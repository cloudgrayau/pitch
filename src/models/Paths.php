<?php
namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\base\Model;

class Paths extends Model {
  
  public static array $output = array();
  
  // Public Methods
  // =========================================================================
  
  public static function doInit(): void {
    $output = explode('/', Craft::$app->getRequest()->pathInfo);
    array_shift($output);
    self::$output = $output;
  }
  
  public static function getPaths(): array {
    if (empty(self::$output)){
      self::doInit();
    }
    $output = self::$output;
    array_pop($output);
    $string = implode('/', $output);
    if (($val = stripos($string, ',')) && ($val !== false)){
      $url = implode('/', self::$output);
      $dir = explode('/', substr($url, 0, $val));
      $original = array_pop($dir);
      $dir = implode('/', $dir).'/';
      $files = $original.','.substr($url, $val+1);
    } else {
      $files = array_pop(self::$output);
      $dir = implode('/', self::$output).'/';
    }
    return [
      'dir' => $dir,
      'files' => $files
    ];
  }
  
}
