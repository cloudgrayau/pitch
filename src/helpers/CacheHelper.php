<?php
namespace cloudgrayau\pitch\helpers;

use CraftCms\Cms\Support\Env;
use CraftCms\Cms\Support\File as CraftFile;
use Illuminate\Support\Facades\{File, Log};

/**
 * Reads and writes Pitch's cache files.
 *
 * Every file Pitch writes is recorded in a manifest, and clearing the cache only deletes files listed there.
 * Pitch never deletes or overwrites a file it didn't create, even if the cache folder is pointed somewhere it
 * shouldn't be. The manifest is the file storage/app/pitch, never in the cache folder itself (which may be public).
 * Its first line names the cache folder it belongs to, so entries recorded for one folder are never applied to another.
 */
class CacheHelper {

  /** Non-advanced cache files: an md5 of the request path. Matched so older caches, written before the manifest, still clear. */
  private const HASHED = '/^[0-9a-f]{32}\.(?:css|scss|js)$/';

  /**
   * Advanced cache files for pitch() URLs, which always end in the source's modified time, e.g. css/site:1700000000.css.
   * Real source files don't have a colon in their name, so these are Pitch's even without a manifest entry.
   */
  private const VERSIONED = '#^(?:css|scss|js)/.+:\d+\.(?:css|scss|js)$#';

  /** The manifest, relative to storage/. */
  private const MANIFEST = 'app/pitch';

  /** @var array<string, true>|null Paths in this folder's manifest, loaded on first use. */
  private ?array $known = null;

  public function __construct(
    private string $dir,
    private bool $enabled = false,
    private bool $advanced = false,
  ) {}

  /** The manifest's first line for a cache folder. */
  private static function header(string $dir): string {
    return '# ' . rtrim($dir, '/') . '/';
  }

  /** The entries a manifest holds for a folder, or null if it belongs to a different folder (or is empty). */
  private static function entries(string $dir, string $contents): ?array {
    $lines = preg_split('/\R/', $contents, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (($lines[0] ?? null) !== self::header($dir)) {
      return null;
    }
    return array_values(array_unique(array_slice($lines, 1)));
  }

  /**
   * Updates the manifest under an exclusive lock. $fn gets this folder's entries (null if the manifest belongs to
   * another folder) and returns the entries to save, or null to leave the file as it is.
   */
  private static function update(string $dir, callable $fn): void {
    $manifest = storage_path(self::MANIFEST);
    File::ensureDirectoryExists(dirname($manifest));
    if (($handle = @fopen($manifest, 'c+')) === false) {
      Log::warning("Pitch: couldn't open its cache manifest, $manifest");
      return;
    }
    try {
      flock($handle, LOCK_EX); // clears and writes take turns, so no entry is lost
      $entries = $fn(self::entries($dir, (string) stream_get_contents($handle)));
      if ($entries !== null) {
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, implode("\n", [self::header($dir), ...$entries]) . "\n");
      }
    } finally {
      flock($handle, LOCK_UN);
      fclose($handle);
    }
  }

  /** A manifest entry as a path inside the folder, or null if it's unsafe or no longer points at a file there. */
  private static function locate(string $dir, string $entry): ?string {
    $entry = trim($entry);
    if ($entry === '' || str_starts_with($entry, '/') || str_contains($entry, "\0") || in_array('..', explode('/', str_replace('\\', '/', $entry)), true)) {
      return null;
    }
    $root = realpath($dir);
    $path = realpath($dir . $entry); // also resolves symlinks, so a linked file or folder can't point outside
    if ($root === false || $path === false || !is_file($path) || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
      return null;
    }
    return $path;
  }

  /** Removes folders left empty by a clear, deepest first, stopping at the cache folder. rmdir() can't remove a folder that isn't empty. */
  private static function pruneDirs(string $dir, array $paths): void {
    $root = realpath($dir);
    if ($root === false) {
      return;
    }
    $dirs = [];
    foreach ($paths as $path) {
      for ($d = dirname($path); str_starts_with($d, $root . DIRECTORY_SEPARATOR); $d = dirname($d)) {
        $dirs[$d] = true;
      }
    }
    $dirs = array_keys($dirs);
    usort($dirs, fn ($a, $b) => strlen($b) <=> strlen($a));
    foreach ($dirs as $d) {
      @rmdir($d);
    }
  }

  /** Whether Pitch wrote this file: it's in the manifest, or it matches a cache filename from an older, untracked cache. */
  private function owns(string $entry): bool {
    if ($this->known === null) {
      $contents = @file_get_contents(storage_path(self::MANIFEST));
      $this->known = array_fill_keys(self::entries($this->dir, (string) $contents) ?? [], true);
    }
    return isset($this->known[$entry]) || preg_match(self::HASHED, $entry) === 1 || preg_match(self::VERSIONED, $entry) === 1;
  }

  /**
   * Deletes the files Pitch wrote to a cache folder, then the folders they leave empty.
   * Anything else in the folder is left alone. Returns how many files were deleted.
   */
  public static function clear(string $dir): int {
    $dir = rtrim($dir, '/') . '/';
    if (!is_dir($dir)) {
      return 0;
    }
    $deleted = [];
    if (is_file(storage_path(self::MANIFEST))) {
      self::update($dir, function (?array $entries) use ($dir, &$deleted): ?array {
        if ($entries === null) {
          return null; // the manifest is for another folder: leave it alone
        }
        $kept = [];
        foreach ($entries as $entry) {
          $path = self::locate($dir, $entry);
          if ($path === null) {
            continue; // already gone, or not a path we'd ever delete
          }
          if (@unlink($path)) {
            $deleted[] = $path;
          } else {
            $kept[] = $entry; // still there, so keep tracking it
          }
        }
        return $kept;
      });
    }
    // Cache files from before the manifest existed, matched only by their exact name patterns
    foreach (File::files($dir) as $file) {
      if (preg_match(self::HASHED, $file->getFilename()) === 1 && @unlink($file->getPathname())) {
        $deleted[] = $file->getPathname();
      }
    }
    foreach (['css', 'scss', 'js'] as $top) {
      if (!is_dir($dir . $top) || is_link($dir . $top)) {
        continue;
      }
      foreach (File::allFiles($dir . $top) as $file) { // doesn't follow symlinked folders
        $entry = $top . '/' . str_replace('\\', '/', $file->getRelativePathname());
        if (preg_match(self::VERSIONED, $entry) === 1 && ($path = self::locate($dir, $entry)) !== null && @unlink($path)) {
          $deleted[] = $path;
        }
      }
    }
    self::pruneDirs($dir, $deleted);
    return count($deleted);
  }

  public static function resolveDir(string $dir): string {
    $dir = !empty($dir) ? $dir : '@storage/runtime/pitch';
    return rtrim(Env::parse($dir), '/\\') . '/';
  }

  /** The cache filename for a request path (also used for SRI hashes). */
  public function filename(string $file): string {
    $parts = pathinfo($file);
    if ($this->advanced) {
      return $parts['dirname'] . '/' . $parts['basename'];
    }
    return md5($file) . '.' . $parts['extension'];
  }

  /** Cached contents, or null if caching is off or the file is missing or stale. An expiry of 0 (dev mode) is always stale. */
  public function read(string $file, int $expiry = 2592000, int $mtime = 0): ?string {
    if (!$this->enabled || $expiry <= 0 || str_contains($file, '..')) {
      return null;
    }
    $path = CraftFile::normalizePath($this->dir . $this->filename($file));
    if (!is_file($path)) {
      return null;
    }
    $modified = filemtime($path);
    if (($mtime <= $modified) && (($modified + $expiry) > time())) {
      return file_get_contents($path);
    }
    return null;
  }

  public function write(string $file, string $content): void {
    if (!$this->enabled || str_contains($file, '..') || PathGuard::problem(rtrim($this->dir, '/')) !== null) {
      return;
    }
    $entry = $this->filename($file);
    $path = CraftFile::normalizePath($this->dir . $entry);
    $owned = $this->owns($entry); // also loads the manifest
    if (file_exists($path) && !$owned) {
      // e.g. advanced caching pointed at the web root, where /css/site.css would replace the real file
      Log::warning("Pitch: not overwriting $path, which it didn't create");
      return;
    }
    File::ensureDirectoryExists(dirname($path));
    if (file_put_contents($path, $content, LOCK_EX) === false) {
      return;
    }
    if (!isset($this->known[$entry])) { // new, or a hashed file from an older cache: start tracking it
      // A manifest left for another folder is replaced: its files become untracked, never wrongly deleted
      self::update($this->dir, fn (?array $entries): array => array_values(array_unique([...($entries ?? []), $entry])));
      $this->known[$entry] = true;
    }
  }

}
