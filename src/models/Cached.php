<?php
namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\base\Model;
use craft\helpers\FileHelper;

class Cached extends Model {

  private static string $dir = '';
  private string $filename = '';
  private string $tmp_file = '';
  private bool $cache = false;
  private bool $advanced = false;
  
  // Public Methods
  // =========================================================================

  function __construct($dir, $cache=false, $advanced=false) {
    self::$dir = $dir;
    $this->cache = $cache;
    $this->advanced = $advanced;
  }

  final public function generateURL(): string {
    $parts = pathinfo($this->filename);
    if ($this->advanced){
      return $parts['dirname'].'/'.$parts['basename'];
    } else {
      return md5($this->filename).'.'.$parts['extension'];
    }
  }

  final public function cache($file='', $time=0, $mtime=0): bool {
    if ($this->cache){
      $this->filename = $file;
      ob_start();
      if ($this->tmp_file = $this->generateURL()){
        $cachefile = FileHelper::normalizePath(self::$dir.$this->tmp_file);
        if (file_exists($cachefile)) {
          $expiry = ($time > 0) ? $time : 2592000;
          $mod_time = FileHelper::lastModifiedTime($cachefile);
          if (($mtime <= $mod_time) && (($mod_time+$expiry) > $_SERVER['REQUEST_TIME'])){
            readfile($cachefile);
            return true;
          }
        }
      }
    }
    return false;
  }

  final public function write(): void {
    if ($this->cache){
      $data = ob_get_contents();
      if (!empty($this->tmp_file)) {
        FileHelper::writeToFile(
          self::$dir.$this->tmp_file,
          $data,
          array(
            'createDirs' => true,
            'append' => false,
            'lock' => true
          )
        );
      }
    }
  }

  final public function dump(): bool {
    if ($this->cache){
      ob_end_flush();
    }
    return true;
  }

}