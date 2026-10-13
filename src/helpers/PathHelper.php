<?php
namespace cloudgrayau\pitch\helpers;

class PathHelper {

  /** Segments of the current request path, minus the leading css/scss/js segment. */
  public static function fromRequest(): array {
    $segments = explode('/', request()->path());
    array_shift($segments);
    return $segments;
  }

  /** Segments of a path as passed to the pitch() Twig function. */
  public static function fromString(string $path): array {
    $segments = explode('/', $path);
    if (empty($segments[0])) {
      array_shift($segments);
    }
    return $segments;
  }

  /** @return array{dir: string, files: string} */
  public static function parse(array $segments): array {
    $head = $segments;
    array_pop($head);
    $string = implode('/', $head);
    if (($val = stripos($string, ',')) && ($val !== false)) {
      $url = implode('/', $segments);
      $dir = explode('/', substr($url, 0, $val));
      $original = array_pop($dir);
      $dir = implode('/', $dir) . '/';
      $files = $original . ',' . substr($url, $val + 1);
    } else {
      $files = array_pop($segments);
      $dir = implode('/', $segments) . '/';
    }
    return ['dir' => $dir, 'files' => $files];
  }

}