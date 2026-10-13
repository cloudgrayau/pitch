<?php
namespace cloudgrayau\pitch\twigextensions;

use cloudgrayau\pitch\helpers\CacheHelper;
use cloudgrayau\pitch\helpers\PathHelper;
use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Support\Env;
use CraftCms\Cms\Support\File;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function CraftCms\Cms\site_url;

class PitchTwigExtension extends AbstractExtension {

  private array $pitch = [];

  // Public Methods
  // =========================================================================

  public function getName(): string {
    return 'Pitch';
  }

  public function getFunctions(): array {
    return [
      new TwigFunction('pitch', [$this, 'generatePitch']),
      new TwigFunction('pitch_sri', [$this, 'generateSRI']),
    ];
  }

  public function generatePitch(string $pitch = '', bool $base = true): string {
    if (isset($this->pitch[$pitch])) {
      return $this->format($this->pitch[$pitch], $base);
    }
    $path = parse_url(File::normalizePath($pitch));
    $paths = pathinfo($path['path'] ?? '');
    $extension = $paths['extension'] ?? null;
    if (!in_array($extension, ['css', 'scss', 'js'], true)) {
      return '';
    }
    $segments = PathHelper::fromString($paths['dirname'] . '/' . $paths['filename'] . '.' . $extension);
    ['dir' => $dir, 'files' => $files] = PathHelper::parse($segments);
    $webroot = rtrim(Env::parse('@webroot'), '/') . '/';
    $ext = strrchr($files, '.');
    $names = explode(',', substr($files, 0, -strlen($ext)));
    $realfiles = [];
    $filemtime = 0;
    foreach ($names as $file) {
      $pos = strpos($file, ':');
      if (($pos !== false) && (ctype_digit(substr($file, $pos + 1)))) {
        $file = substr($file, 0, $pos);
      }
      $asset = File::normalizePath($webroot . $dir . $file . '.' . $extension);
      if (file_exists($asset)) {
        $realfiles[] = $file;
        $filemtime = max($filemtime, filemtime($asset));
        /*$tempmtime = max($filemtime, filemtime($asset));
        if ($tempmtime > $filemtime){
          $filemtime = $tempmtime;
        }*/
      }
    }
    if ($filemtime > 0) {
      $url = $extension . '/' . $dir . implode(',', $realfiles) . ':' . $filemtime . '.' . $extension;
      $this->pitch[$pitch] = $url;
      return $this->format($url, $base);
    }
    return '';
  }

  public function generateSRI(string $pitch = '', string $hash = 'sha384'): mixed {
    if (Pitch::devMode() || !isset($this->pitch[$pitch])){
      return false;
    }
    $settings = Pitch::getInstance()->getSettings();
    $dir = CacheHelper::resolveDir($settings->cacheDir);
    $cache = new CacheHelper($dir, false, $settings->advancedCache);
    $cacheFile = File::normalizePath($dir . $cache->filename($this->pitch[$pitch]));
    if (file_exists($cacheFile)) {
      return $hash . '-' . base64_encode(hash_file($hash, $cacheFile, true));
    }
    return false;
  }

  // Private Methods
  // =========================================================================

  private function format(string $url, bool $base): string {
    return $base ? site_url($url) : '/' . $url;
  }

}