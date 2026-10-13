<?php
namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;
use CraftCms\Cms\Http\RespondsWithFlash;

use function CraftCms\Cms\{cp_url, t};

class CacheController {

  use RespondsWithFlash;
  
  public function refresh() {
    Pitch::getInstance()->clearCache();
    return $this->asSuccess(t('Cache successfully cleared.'), redirect: cp_url('dashboard'));
  }

}