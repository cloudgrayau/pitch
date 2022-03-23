<?php
/**
 * Pitch plugin for Craft CMS 4.x
 *
 * Advanced SCSS, minimized JS and asset cache loading for CraftCMS
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch\console;

use cloudgrayau\pitch\Pitch;
use yii\console\Controller;

/**
 * Class ClearController
 *
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     2.0.0
 *
 */
class ClearController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex() {
      Pitch::$plugin->clearCache(true);
    }
    
}