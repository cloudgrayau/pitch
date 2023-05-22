<?php
namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\models\Paths;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

class ScssController extends Controller {

  protected array|bool|int $allowAnonymous = ['index' => self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE];

  // Public Methods
  // =========================================================================

  public function actionIndex(): void {
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
