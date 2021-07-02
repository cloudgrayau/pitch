<?php
/**
 * Pitch plugin for Craft CMS 3.x
 *
 * Advanced SCSS, minimized JS and asset cache loading for CraftCMS
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\models\Paths;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     1.1.0
 */
class ScssController extends Controller {

  // Protected Properties
  // =========================================================================

  /**
   * @var    bool|array Allows anonymous access to this controller's actions.
   *         The actions must be in 'kebab-case'
   * @access protected
   */
  protected $allowAnonymous = ['index' => self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE];

  // Public Methods
  // =========================================================================

  /**
   * @return mixed
   */
  public function actionIndex(){
    Paths::doInit();
    $output = Paths::$output;
    array_pop($output);
    $string = implode('/', $output);
    if (($val = stripos($string, ',')) && ($val !== false)){
      $url = implode('/', Paths::$output);
      $dir = explode('/', substr($url, 0, $val));
      $original = array_pop($dir);
      $dir = implode('/', $dir).'/';
      $files = $original.','.substr($url, $val+1);
    } else {
      $files = array_pop(Paths::$output);
      $dir = implode('/', Paths::$output).'/';
    }
    if ($result = CssController::initSCSS($dir, $files)){
      exit();
    }
    throw new NotFoundHttpException('Page not found.');
  }

}
