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
  
}
