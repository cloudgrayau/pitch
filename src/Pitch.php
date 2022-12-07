<?php
/**
 * Pitch plugin for Craft CMS 4.x
 *
 * On the go SCSS compiling, CSS/JS minifying, merging and caching.
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch;

use cloudgrayau\pitch\models\Settings;
use cloudgrayau\pitch\controllers\CacheController;
use cloudgrayau\pitch\variables\PitchVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\twig\variables\CraftVariable;

use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use craft\utilities\ClearCaches;

use yii\base\Event;

/**
 * Class Pitch
 *
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     2.2.0
 *
 */
class Pitch extends Plugin {
  // Static Properties
  // =========================================================================

  /**
   * @var Pitch
   */
  public static $plugin;

  // Public Properties
  // =========================================================================

  /**
   * @var string
   */
  public string $schemaVersion = '1.0.0';

  /**
   * @var bool
   */
  public bool $hasCpSettings = true;

  /**
   * @var bool
   */
  public bool $hasCpSection = false;

  // Public Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  public function init(){
    parent::init();
    self::$plugin = $this;
    
    if (Craft::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'cloudgrayau\pitch\console';
    }
    
    Event::on(ClearCaches::class, ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
      function(RegisterCacheOptionsEvent $event) {
        $event->options[] = [
          'key' => 'pitch',
          'label' => Craft::t('pitch', 'Pitch cache'),
          'action' => function(){
            self::$plugin->clearCache(true);
          }
        ];
      }
    );
    
    Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
      $event->sender->set('pitch', PitchVariable::class);
    });

    Event::on(
      UrlManager::class,
      UrlManager::EVENT_REGISTER_SITE_URL_RULES,
      function (RegisterUrlRulesEvent $event){
        $event->rules['css/<action:.+>.css'] = 'pitch/css';
        $event->rules['scss/<action:.+>.css'] = 'pitch/scss';
        $event->rules['scss/<action:.+>.scss'] = 'pitch/scss';
        $event->rules['js/<action:.+>.js'] = 'pitch/js';
      }
    );

    Event::on(
      UrlManager::class,
      UrlManager::EVENT_REGISTER_CP_URL_RULES,
      function (RegisterUrlRulesEvent $event){
        $event->rules['pitch/clear'] = 'pitch/cache/clear-cache';
      }
    );
    
  }

  public function afterSaveSettings(): void{
    parent::afterSaveSettings();
    $this->clearCache();
  }

  public function clearCache($util=false){
    $cacheDir = (!empty($this->settings->cacheDir)) ? $this->settings->cacheDir : '@storage/pitch';
    $cacheFolderPath = FileHelper::normalizePath(
      Craft::parseEnv($cacheDir)
    ).'/';
    $files = glob($cacheFolderPath.'*');
    foreach($files as $file){
      if(is_dir($file)){
        $this->deleteSub($file);
      } else {
        unlink($file);
      }
    }
    if (!$util){
      Craft::$app->response
      ->redirect(UrlHelper::url('settings/plugins/pitch'))
      ->send();
    }
  }
  
  // Private Methods
  // =========================================================================
  private function deleteSub($dir){
    foreach(glob($dir . '/*') as $file) {
      if(is_dir($file)){
        $this->deleteSub($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dir);
  }

  // Protected Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  protected function createSettingsModel(): ?\craft\base\Model{
    return new Settings();
  }

  /**
   * @inheritdoc
   */
  protected function settingsHtml(): ?string {
    return Craft::$app->view->renderTemplate(
      'pitch/settings',
      [
      'settings' => $this->getSettings()
      ]
    );
  }

}
