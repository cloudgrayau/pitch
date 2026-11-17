<?php
namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\widgets\CacheWidget;
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

  /** From the dashboard widget, which posts with fetch and shows the returned status line. */
  public function refresh(): Response {
    Pitch::getInstance()->refreshCache();
    $data = request()->expectsJson() ? ['status' => CacheWidget::status()] : []; // otherwise it'd be flashed to the session
    return $this->asSuccess(t('Cache successfully refreshed.', category: 'pitch'), $data, cp_url('dashboard'));
  }

}