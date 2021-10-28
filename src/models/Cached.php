<?php
/**
 * Pitch plugin for Craft CMS 3.x
 *
 * On the go SCSS compiling, CSS/JS minifying, merging and caching.
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\base\Model;

/**
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     1.2.0
 */
class Cached extends Model {

  private static $dir = '';
  private $filename = null;
  private $tmp_file = null;
  private $cache = false;
  private $advanced = false;

  function __construct($dir, $cache=false, $advanced=false){
    self::$dir = $dir;
    $this->cache = $cache;
    $this->advanced = $advanced;
  }

  final public function generateURL(){
    $parts = pathinfo($this->filename);
    if ($this->advanced){
      if (!file_exists(self::$dir.$parts['dirname'].'/')){
        mkdir(self::$dir.$parts['dirname'].'/', 0777, true);
      }
      return $parts['dirname'].'/'.$parts['basename'];
    } else {
      return md5($this->filename).'.'.$parts['extension'];
    }
  }

  final public function cache($file='', $time=0, $mtime=0){
    if ($this->cache){
      $this->filename = $file;
      ob_start();
      if ($this->tmp_file = $this->generateURL()) {
        if (file_exists(self::$dir.$this->tmp_file)) {
          $expiry = ($time > 0) ? $time : 3600;
          $mod_time = filemtime(self::$dir.$this->tmp_file);
          if (($mtime <= $mod_time) && (($mod_time+$expiry) > $_SERVER['REQUEST_TIME'])){
            readfile(self::$dir.$this->tmp_file);
            return true;
          }
        }
      }
    }
    return false;
  }

  final public function write(){
    if ($this->cache){
      $data = ob_get_contents();
      if (!empty($this->tmp_file)) {
        try {
          if ($fp = fopen(self::$dir.$this->tmp_file, 'wb')) {
            flock($fp, LOCK_EX);
            fwrite($fp, $data);
            flock($fp, LOCK_UN);
            fclose($fp);
          }
        } catch (Exception $e) {
        }
      }
    }
  }

  final public function dump(){
    if ($this->cache){
      ob_end_flush();
    }
    return true;
  }

}