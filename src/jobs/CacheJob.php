<?php
namespace cloudgrayau\pitch\jobs;

use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Queue\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;

use function CraftCms\Cms\site_url;

class CacheJob extends Job implements ShouldQueue {

  use InteractsWithQueue;

  private const PATTERN = '/\bpitch\s*\(\s*(?!\/|\\\\[\'"])((?:\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|[^\'",\)]+)*)\s*(?:,|\))/';

  public function handle(): void {
    $urls = [];
    foreach ($this->templateFiles() as $file) {
      preg_match_all(self::PATTERN, file_get_contents($file), $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $pitchUrl = Pitch::getInstance()->twigExtension->generatePitch(
          str_replace(['\'', '"', '~'], '', $match[1]),
          false
        );
        if ($pitchUrl) {
          $url = site_url($pitchUrl);
          if (!in_array($url, $urls)) {
            $urls[] = $url;
          }
        }
      }
    }
    if (empty($urls)) {
      return;
    }
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_NOBODY => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_FRESH_CONNECT => true,
      CURLOPT_TIMEOUT => 30,
    ]);
    foreach ($urls as $url) {
      curl_setopt($curl, CURLOPT_URL, $url . '?' . time());
      curl_exec($curl);
    }
  }

  /** @return list<string> */
  private function templateFiles(): array {
    $found = [];
    foreach ([resource_path('views')] as $dir) {
      if (!is_dir($dir)) {
        continue;
      }
      foreach (File::allFiles($dir) as $file) {
        if (str_contains(file_get_contents($file->getPathname()), 'pitch(')) {
          $found[] = $file->getPathname();
        }
      }
    }
    return array_values(array_unique($found));
  }

}