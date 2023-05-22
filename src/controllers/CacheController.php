<?php
namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\web\Controller;

class CacheController extends Controller {

  protected array|bool|int $allowAnonymous = [];

  // Public Methods
  // =========================================================================

  public function actionClearCache(): void {
    $this->setSuccessFlash(Craft::t('app', 'Cache successfully cleared.'));
    Pitch::getInstance()->clearCache();
  }

}
