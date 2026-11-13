<?php
namespace cloudgrayau\pitch\widgets;

use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Dashboard\Widgets\Widget;

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

  public function props(): array {
    return [
      'html' => '<form method="post" action="' . e(cp_url('pitch/refresh')) . '">'
        . csrf_field()
        . '<button type="submit" style="width:100%;padding:8px 14px;cursor:pointer;">' . e(t('Refresh Entire Cache', category: 'pitch')) . '</button>'
        . '</form>',
    ];
  }

}