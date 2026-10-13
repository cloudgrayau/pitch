<?php
namespace cloudgrayau\pitch\services;

use cloudgrayau\pitch\helpers\CacheHelper;
use cloudgrayau\pitch\helpers\PathHelper;
use cloudgrayau\pitch\models\Settings;
use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Support\Env;
use Illuminate\Http\Request;
use MatthiasMullie\Minify;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\ScssPhp\ValueConverter;
use Symfony\Component\HttpFoundation\Response;

class AssetService {

  // Public Methods
  // =========================================================================

  public function serve(Request $request, string $type): Response {
    /** @var Settings $settings */
    $settings = Pitch::getInstance()->getSettings();
    $webroot = self::normalise(Env::parse('@webroot'));
    $paths = PathHelper::parse(PathHelper::fromRequest());
    $dir = $paths['dir'];
    $ext = (string) strrchr($paths['files'], '.');
    $base = strlen($ext) ? substr($paths['files'], 0, -strlen($ext)) : $paths['files'];

    $source = '';
    $mtime = 0;
    foreach (explode(',', $base) as $name) {
      $pos = strpos($name, ':');
      if (($pos !== false) && ctype_digit(substr($name, $pos + 1))) {
        $name = substr($name, 0, $pos);
      }
      $asset = self::normalise($webroot . '/' . $dir . $name . '.' . $type);
      if (!str_starts_with($asset, $webroot . '/') || !is_file($asset)) {
        continue; // outside the web root, or missing
      }
      $mtime = max($mtime, filemtime($asset));
      $source .= file_get_contents($asset) . "\n";
    }
    abort_if($mtime === 0, 404, 'Page not found.');

    $devMode = Pitch::devMode();
    $offset = ($settings->cacheDuration > 0)
      ? $settings->cacheDuration
      : (($type === 'js') ? 31536000 : 2592000);
    if ($devMode) {
      $offset = 0;
    }

    $cache = new CacheHelper(
      CacheHelper::resolveDir($settings->cacheDir),
      $settings->useCache,
      $settings->advancedCache
    );
    $path = $request->path();
    $body = $cache->read($path, $offset, $mtime);
    if ($body === null) {
      $body = $this->build($type, $source, $webroot . '/' . $dir, $settings);
      $cache->write($path, $body);
    }

    $response = response($body, 200, [
      'Content-Type' => (($type === 'js') ? 'text/javascript' : 'text/css') . '; charset=utf-8',
    ]);
    $response->headers->set('Cache-Control', $devMode ? 'no-cache' : "max-age=$offset, public, must-revalidate");
    $response->setLastModified((new \DateTime())->setTimestamp($mtime));
    $response->setExpires((new \DateTime())->setTimestamp(time() + $offset));
    foreach ($settings->fileHeaders as $header) {
      $header = trim((string) $header);
      if (str_contains($header, ':')) {
        [$name, $value] = array_map('trim', explode(':', $header, 2));
        $response->headers->set($name, $value);
      }
    }
    $response->setEtag($mtime . '-' . md5($body));
    $response->isNotModified($request); // turns it into a 304 when the client's copy is current
    return $response;
  }

  public static function scssOutputStyle(string $format): OutputStyle {
    return match ($format) {
      'Compact', 'Crunched', 'Compressed' => OutputStyle::COMPRESSED,
      'Nested', 'Expanded' => OutputStyle::EXPANDED,
      default => $format,
    };
  }

  // Private Methods
  // =========================================================================

  private function build(string $type, string $source, string $dir, Settings $settings): string {
    $baseUrl = request()->getBasePath();
    switch ($type) {
      case 'scss':
        $scss = new Compiler();
        $scss->setImportPaths(self::normalise($dir) . '/');
        $scss->replaceVariables(['baseUrl' => ValueConverter::fromPhp($baseUrl)]);
        $scss->setOutputStyle(self::scssOutputStyle($settings->cssFormat));
        return $scss->compileString($source)->getCss();
      case 'css':
        $css = str_replace('#{$baseUrl}', $baseUrl, $source);
        if (!$settings->minifyFiles) {
          return $css;
        }
        $minifier = new Minify\CSS();
        $minifier->add($css);
        return $minifier->minify();
      default:
        $js = str_replace('$baseUrl', $baseUrl, $source);
        if (!$settings->minifyFiles) {
          return $js;
        }
        $minifier = new Minify\JS();
        $minifier->add($js);
        return $minifier->minify();
    }
  }

  /** Lexically collapses `.`, `..` and repeated slashes (absolute Unix paths). Doesn't resolve symlinks. */
  private static function normalise(string $path): string {
    $parts = [];
    foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
      if ($segment === '' || $segment === '.') {
        continue;
      }
      if ($segment === '..') {
        array_pop($parts);
        continue;
      }
      $parts[] = $segment;
    }
    return '/' . implode('/', $parts);
  }

}