<?php
namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;
use CraftCms\Cms\Http\RespondsWithFlash;
use Symfony\Component\HttpFoundation\Response;

use function CraftCms\Cms\{cp_url, t};

class CacheController {

  use RespondsWithFlash;
  
  /** From the settings page's action menu. */
  public function clear(): Response {
    Pitch::getInstance()->clearCache();
    return $this->asSuccess(t('Cache successfully cleared.', category: 'pitch'), redirect: cp_url('settings/plugins/pitch'));
  }

  public function refresh(): Response {
    Pitch::getInstance()->clearCache();
    return $this->asSuccess(t('Cache successfully cleared.', category: 'pitch'), redirect: cp_url('dashboard'));
  }

}