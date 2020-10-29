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

use Craft;
use craft\web\Controller;

/**
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     1.0.1
 */
class CacheController extends Controller {

  // Protected Properties
  // =========================================================================

  /**
   * @var    bool|array Allows anonymous access to this controller's actions.
   *         The actions must be in 'kebab-case'
   * @access protected
   */
  protected $allowAnonymous = [];

  // Public Methods
  // =========================================================================

  /**
   * @return mixed
   */
  public function actionClearCache(){
    $this->setSuccessFlash(Craft::t('app', 'Cache successfully cleared.'));
    Pitch::getInstance()->clearCache();
  }

}
