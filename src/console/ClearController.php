<?php
namespace cloudgrayau\pitch\console;

use cloudgrayau\pitch\Pitch;
use yii\console\Controller;

class ClearController extends Controller {
    
    // Public Methods
    // =========================================================================

    public function actionIndex(): void {
      Pitch::$plugin->clearCache(true);
    }
    
}