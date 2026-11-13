<?php
namespace cloudgrayau\pitch\helpers;

use CraftCms\Cms\Support\Env;

class PathGuard {

  private static function within(string $child, string $parent): bool {
    $child = mb_strtolower(rtrim($child, '/'));
    $parent = mb_strtolower(rtrim($parent, '/'));
    return $child === $parent || str_starts_with($child, $parent . '/');
  }

  private static function relative(string $path, string $root): string {
    return str_starts_with($path, $root . '/') ? substr($path, strlen($root) + 1) : $path;
  }

  public static function problem(string $raw): ?string {
    $raw = trim($raw);
    if ($raw === '') {
      return null; // empty falls back to the default, @storage/runtime/pitch
    }
    $path = self::resolve($raw);
    $root = self::resolve(base_path());

    if ($path === $root || !self::within($path, $root)) {
      return 'Storage path needs to be a folder inside the project, not the project root';
    }
    foreach (self::protectedPaths() as $protected) {
      if (self::within($path, $protected)) {
        return 'Storage path cannot be inside ' . self::relative($protected, $root);
      }
      // Also refuse a folder that contains a protected one, e.g. the web root or @storage itself
      if (self::within($protected, $path)) {
        return 'Storage path cannot contain ' . self::relative($protected, $root);
      }
    }
    if (is_file($path)) {
      return 'Storage path is a file, not a folder';
    }
    return null;
  }

  public static function protectedPaths(): array {
    return array_values(array_unique(array_map(self::resolve(...), [
      base_path('vendor'),
      base_path('bootstrap'),
      base_path('routes'),
      base_path('database'),
      base_path('templates'),
      app_path(),
      config_path(),
      resource_path(),
      public_path('vendor'),
      storage_path('app'),
      storage_path('framework'),
      storage_path('logs'),
    ])));
  }

  public static function resolve(string $raw): string {
    $path = self::normalise((string) Env::parse($raw));
    $existing = $path;
    $tail = '';
    while ($existing !== '/' && !file_exists($existing)) {
      $tail = '/' . basename($existing) . $tail;
      $existing = dirname($existing);
    }
    $real = realpath($existing);
    return rtrim(($real !== false ? $real : $existing) . $tail, '/') ?: '/';
  }

  public static function normalise(string $path): string {
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