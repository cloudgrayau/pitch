<?php
namespace cloudgrayau\pitch;

use cloudgrayau\pitch\models\Settings;
use cloudgrayau\pitch\controllers\CacheController;
use cloudgrayau\pitch\variables\PitchVariable;
use cloudgrayau\utils\UtilityHelper;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\twig\variables\CraftVariable;

use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use craft\utilities\ClearCaches;

use yii\base\Event;

class Pitch extends Plugin {

  public static $plugin;
  public string $schemaVersion = '1.0.0';
  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;

  // Public Methods
  // =========================================================================

  public function init(): void {
    parent::init();
    self::$plugin = $this;
    $this->_registerComponents();  
    $this->_registerConsole();
    $this->_registerCache();
    $this->_registerVariables();
    $this->_registerUrlRules();
    $this->_registerCpUrlRules();
  }

  public function afterSaveSettings(): void {
    parent::afterSaveSettings();
    $this->clearCache();
  }

  public function clearCache($util=false): void {
    $cacheDir = (!empty($this->settings->cacheDir)) ? $this->settings->cacheDir : '@storage/pitch';
    $cacheFolderPath = FileHelper::normalizePath(
      App::parseEnv($cacheDir)
    ).'/';    
    if (is_dir($cacheFolderPath)){
      FileHelper::clearDirectory($cacheFolderPath);
    }
    if (!$util){
      Craft::$app->response
      ->redirect(UrlHelper::url('settings/plugins/pitch'))
      ->send();
    }
  }
  
  // Private Methods
  // =========================================================================
  
  private function _registerComponents(): void {
    UtilityHelper::registerModule();
  }
  
  private function _registerConsole(): void {
    if (Craft::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'cloudgrayau\pitch\console';
    }
  }
  
  private function _registerCache(): void {
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
  }
  
  private function _registerVariables(): void {
    Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
      $event->sender->set('pitch', PitchVariable::class);
    });
  }
  
  private function _registerUrlRules(): void {
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
  }
  
  private function _registerCpUrlRules(): void {
    Event::on(
      UrlManager::class,
      UrlManager::EVENT_REGISTER_CP_URL_RULES,
      function (RegisterUrlRulesEvent $event){
        $event->rules['pitch/clear'] = 'pitch/cache/clear-cache';
      }
    );
  }

  // Protected Methods
  // =========================================================================

  protected function createSettingsModel(): ?\craft\base\Model {
    return new Settings();
  }

  protected function settingsHtml(): ?string {
    return Craft::$app->view->renderTemplate(
      'pitch/settings',
      [
      'settings' => $this->getSettings()
      ]
    );
  }

}
