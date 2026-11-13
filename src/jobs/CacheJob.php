<?php
namespace cloudgrayau\pitch\jobs;

use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Queue\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\{File, Http, Log};

use function CraftCms\Cms\site_url;

/** Warms the cache by requesting every pitch() URL found in the site's templates. */
class CacheJob extends Job implements ShouldQueue {

  use InteractsWithQueue;

  private const PATTERN = '/\bpitch\s*\(\s*(?!\/|\\\\[\'"])((?:\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|[^\'",\)]+)*)\s*(?:,|\))/';

  /**
   * The contents of each template that calls pitch(). Checks Craft 6's resources/views and Craft 5's templates/,
   * which upgraded sites can keep using through the yii2-adapter.
   *
   * @return iterable<string>
   */
  private function templates(): iterable {
    $seen = [];
    foreach ([resource_path('views'), base_path('templates')] as $dir) {
      if (!is_dir($dir)) {
        continue;
      }
      foreach (File::allFiles($dir) as $file) {
        $path = $file->getRealPath() ?: $file->getPathname();
        if (isset($seen[$path])) {
          continue; // e.g. one folder is a symlink to the other
        }
        $seen[$path] = true;
        $contents = file_get_contents($path);
        if ($contents !== false && str_contains($contents, 'pitch(')) {
          yield $contents;
        }
      }
    }
  }

  public function handle(): void {
    $twig = Pitch::getInstance()->twigExtension;
    $urls = [];
    foreach ($this->templates() as $contents) {
      preg_match_all(self::PATTERN, $contents, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $pitchUrl = $twig->generatePitch(str_replace(['\'', '"', '~'], '', $match[1]), false);
        if ($pitchUrl !== '') {
          $urls[site_url($pitchUrl)] = true;
        }
      }
    }
    foreach (array_keys($urls) as $url) {
      try {
        // A HEAD request is enough to build and cache the file; the query string skips any HTTP cache in front
        Http::timeout(30)->head($url, ['t' => time()]);
      } catch (ConnectionException $e) {
        Log::info("Pitch couldn't warm $url: " . $e->getMessage());
      }
    }
  }

}
