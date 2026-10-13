<?php
namespace cloudgrayau\pitch\widgets;

use CraftCms\Cms\Dashboard\Widgets\Widget;

use function CraftCms\Cms\{cp_url, t};

class CacheWidget extends Widget {

  public static function displayName(): string {
    return t('Pitch', category: 'pitch');
  }

  protected static function allowMultipleInstances(): bool {
    return false;
  }

  public static function icon(): string {
    return 'bolt'; // placeholder: confirm the icon name below
  }

  public function component(): string {
    return 'craft:html-widget';
  }

  public function props(): array {
    return [
      'html' => '<form method="post" action="' . e(cp_url('pitch/refresh')) . '">'
        . csrf_field()
        . '<button type="submit" style="width:100%;padding:8px 14px;cursor:pointer;">Refresh Entire Cache</button>'
        . '</form>',
    ];
  }

}