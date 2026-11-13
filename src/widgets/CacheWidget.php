<?php
namespace cloudgrayau\pitch\widgets;

use cloudgrayau\pitch\helpers\CacheHelper;
use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Dashboard\Widgets\Widget;
use CraftCms\Cms\Support\Html;

use function CraftCms\Cms\{cp_url, t};

class CacheWidget extends Widget {

  protected static function allowMultipleInstances(): bool {
    return false;
  }

  public static function displayName(): string {
    return t('Pitch', category: 'pitch');
  }

  public static function icon(): string {
    return Pitch::getInstance()->getBasePath() . '/icon-mask.svg'; // the plugin icon, as in Craft 5
  }

  public function component(): ?string {
    return 'craft:html-widget';
  }

  /** Laid out like Craft's Updates widget: a status line, then a button. */
  public function props(): array {
    $settings = Pitch::getInstance()->getSettings();
    if ($settings->useCache) {
      $count = CacheHelper::count(CacheHelper::resolveDir($settings->cacheDir));
      $status = t('{num, plural, =0{No asset files are cached.} =1{1 asset file is cached.} other{# asset files are cached.}}', ['num' => $count], 'pitch');
    } else {
      $status = t('Caching is turned off.', category: 'pitch');
    }
    $button = Html::tag('craft-button', Html::encode(t('Refresh Cache', category: 'pitch')), ['type' => 'submit', 'variant' => 'primary', 'style' => 'width:100%']);
    return [
      'html' => Html::tag('form', csrf_field() . Html::tag('p', Html::encode($status)) . $button, [
        'method' => 'post',
        'action' => cp_url('pitch/refresh'),
        'class' => 'text-center space-y-3',
      ]),
    ];
  }

}