<?php
/**
 * Pitch plugin for Craft CMS 3.x
 *
 * On the go SCSS compiling, CSS/JS minifying, merging and caching.
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\models\Paths;
use cloudgrayau\pitch\models\Cached;
use cloudgrayau\pitch\models\Minify;

use Craft;
use craft\web\Controller;
use craft\helpers\FileHelper;
use yii\web\NotFoundHttpException;

use ScssPhp\ScssPhp\Compiler;

/**
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     1.0.1
 */
class CssController extends Controller {

  // Protected Properties
  // =========================================================================

  /**
   * @var    bool|array Allows anonymous access to this controller's actions.
   *         The actions must be in 'kebab-case'
   * @access protected
   */
  protected $allowAnonymous = ['index'];

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
    if ($result = self::initCSS($dir, $files)){
      exit();
    }
    throw new NotFoundHttpException('Page not found.');
  }

  public static function initSCSS($dir, $files){
    return self::initCSS($dir, $files, true);
  }

  private static function initCSS($dir, $files, $scss=false){
    $webroot = Craft::getAlias('@webroot').'/';
    $settings = Pitch::getInstance()->settings;
    $ext = strrchr($files,'.');
    $files = explode(',', substr($files, 0, -strlen($ext)));
    ob_start();
    $filemtime = 0;
    $count = 0;
    foreach($files as $file){
      $pos = strpos($file,':');
      if (($pos !== false) && (ctype_digit(substr($file, $pos+1)))){
        $file = substr($file, 0, $pos);
      }
      $file = $dir.$file.'.css';
      if (file_exists($webroot.$file)){
        ++$count;
        $mtime = filemtime($webroot.$file);
        if ($mtime > $filemtime){
          $filemtime = $mtime;
        }
        readfile($webroot.$file);
        echo "\n";
      }
    }
    $css = ob_get_contents();
    ob_end_clean();
    if ($count == 0){
      return false;
    }
    $offset = 60*60*24*30;
    ob_start('ob_gzhandler');
    header('Content-Type: text/css; charset=utf-8');
    if (Craft::$app->getConfig()->general->devMode){
      header('Cache-control: no-cache');
      $offset = 0;
    } else {
      header('Cache-control: max-age='.$offset.', public, must-revalidate');
    }
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $filemtime).' GMT');
    header('Expires: ' .gmdate('D, d M Y H:i:s',$filemtime + $offset) . ' GMT');
    header('Link: <'.$_SERVER['REQUEST_URI'].'>; rel=preload; as=style;');
    header('Connection: keep-alive');
    $cacheDir = (isset($settings->cacheDir{0})) ? $settings->cacheDir : '@storage/pitch';
    $cacheFolderPath = FileHelper::normalizePath(
      Craft::parseEnv($cacheDir)
    ).'/';
    if (!is_dir($cacheFolderPath)){
      FileHelper::createDirectory($cacheFolderPath);
    }
    $c = new Cached($cacheFolderPath, $settings->useCache);
    if (!$c->cache(Craft::$app->getRequest()-> fullPath, $offset, $filemtime)){
      if ($scss){
        $scss = new Compiler();
        $scss->setImportPaths($webroot.$dir);
        $scss->setVariables(array(
          'baseUrl' => Craft::$app->getRequest()->baseUrl,
        ));
        $scss->setFormatter('ScssPhp\ScssPhp\Formatter\\'.$settings->cssFormat);
        echo $scss->compile($css);
      } else {
        echo Minify::minifyCSS($css);
      }
      $c->write();
    }
    header('Etag: '.sprintf('"%s-%s"',$filemtime,md5(ob_get_contents())));
    return $c->dump();
  }
}
