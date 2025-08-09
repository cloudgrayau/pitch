<?php
namespace cloudgrayau\pitch\widgets;

use Craft;
use craft\base\Widget;
use craft\helpers\Html;
use cloudgrayau\pitch\Pitch;

class CacheWidget extends Widget {

  public static function displayName(): string {
    return Craft::t('pitch', 'Pitch Cache');
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
  
  public static function getActions(): array {
    $iconPath = '@cloudgrayau/pitch/resources/';
    return [
      'id' => 'refresh',
      'label' => Craft::t('pitch', 'Refresh Entire Cache'),
      'instructions' => Craft::t('pitch', 'Refresh the entire cache'),
      'icon' => Html::svg($iconPath . 'refresh.svg'),
    ];
  }
  
  public function getBodyHtml(): ?string {
    return Craft::$app->getView()->renderTemplate('pitch/_widget', [
      'actions' => static::getActions(),
    ]);
  }

}
