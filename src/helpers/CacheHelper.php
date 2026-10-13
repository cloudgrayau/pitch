<?php
namespace cloudgrayau\pitch\helpers;

use CraftCms\Cms\Support\Env;
use CraftCms\Cms\Support\File as CraftFile;
use Illuminate\Support\Facades\File;

class CacheHelper {

  public function __construct(
    private string $dir,
    private bool $enabled = false,
    private bool $advanced = false,
  ) {}

  /** The cache filename for a request path (also used for SRI hashes). */
  public function filename(string $file): string {
    $parts = pathinfo($file);
    if ($this->advanced) {
      return $parts['dirname'] . '/' . $parts['basename'];
    }
    return md5($file) . '.' . $parts['extension'];
  }

  /** Cached contents, or null if caching is off or the file is missing or stale. */
  public function read(string $file, int $expiry = 0, int $mtime = 0): ?string {
    if (!$this->enabled || str_contains($file, '..')) {
      return null;
    }
    $path = CraftFile::normalizePath($this->dir . $this->filename($file));
    if (!is_file($path)) {
      return null;
    }
    $expiry = ($expiry > 0) ? $expiry : 2592000;
    $modified = filemtime($path);
    if (($mtime <= $modified) && (($modified + $expiry) > time())) {
      return file_get_contents($path);
    }
    return null;
  }

  public function write(string $file, string $content): void {
    if (!$this->enabled || str_contains($file, '..')) {
      return;
    }
    $path = CraftFile::normalizePath($this->dir . $this->filename($file));
    File::ensureDirectoryExists(dirname($path));
    file_put_contents($path, $content, LOCK_EX);
  }
  
  public static function resolveDir(string $dir): string {
    $dir = !empty($dir) ? $dir : '@storage/pitch';
    return rtrim(Env::parse($dir), '/\\') . '/';
  }

}