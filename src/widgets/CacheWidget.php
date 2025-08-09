<?php
namespace cloudgrayau\pitch\widgets;

use Craft;
use craft\base\Widget;
use craft\helpers\Html;
use cloudgrayau\pitch\Pitch;

class CacheWidget extends Widget {

  public static function displayName(): string {
    return Craft::t('pitch', 'Pitch');
  }
  
  protected static function allowMultipleInstances(): bool {
    return false;
  }
  
  public static function icon(): string {
    return Craft::getAlias('@cloudgrayau/pitch/icon-mask.svg');
  }
  
  public static function maxColspan(): ?int {
    return 1;
  }
  
  public function getBodyHtml(): ?string {
    return Craft::$app->getView()->renderTemplate('pitch/_widget');
  }

}
