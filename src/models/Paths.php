<?php
/**
 * Pitch plugin for Craft CMS 4.x
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
 * @since     2.0.0
 */
class Paths extends Model {
  public static array $output = array();
  public static function doInit(){
    $output = explode('/', Craft::$app->getRequest()->pathInfo);
    array_shift($output);
    self::$output = $output;
  }
}
