<?php
namespace cloudgrayau\pitch;

use cloudgrayau\pitch\models\Settings;
use cloudgrayau\pitch\controllers\CacheController;
use cloudgrayau\pitch\variables\PitchVariable;
use cloudgrayau\pitch\twigextensions\PitchTwigExtension;
use cloudgrayau\pitch\widgets\CacheWidget;
use cloudgrayau\utils\UtilityHelper;

use Craft;
use craft\base\Plugin;
use craft\services\Dashboard;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\RegisterComponentTypesEvent;
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
  private ?PitchTwigExtension $twigExtension = null;
  private string $cacheDir = '';

  // Public Methods
  // =========================================================================

  public function init(): void {
    parent::init();
    self::$plugin = $this;
    $this->cacheDir = $this->getSettings()->cacheDir;
    $this->_registerEvents();
    $this->_registerComponents();  
    $this->_registerConsole();
    $this->_registerCache();
    $this->_registerVariables();
    $this->_registerTwigExtensions();
    $this->_registerUrlRules();
    if (Craft::$app->getRequest()->getIsCpRequest()) {
      $this->_registerCpUrlRules();
      $this->_registerWidgets();
    }
  }

  public function clearCache($util=false): void {
    $cacheDir = (!empty($this->settings->cacheDir)) ? $this->settings->cacheDir : '@storage/pitch';
    $cacheFolderPath = FileHelper::normalizePath(
      App::parseEnv($cacheDir)
    ).'/';    
    if (is_dir($cacheFolderPath)){
      FileHelper::clearDirectory($cacheFolderPath);
    }
    if (!$this->settings->advancedCache){
      $directoryPath = CRAFT_BASE_PATH.'/templates/';
      $pattern = '/\bpitch\s*\(\s*(?!\/|\\\\[\'"])((?:\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|[^\'",\)]+)*)\s*(?:,|\))/';
      $output = [];
      if (function_exists('exec')) {
        exec('grep -rl "pitch(" '.$directoryPath, $output);
      } else {
        try {
          $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),\RecursiveIteratorIterator::LEAVES_ONLY);
          foreach ($iterator as $file){
            if ($file->isFile()){
              $output[] = $file;
            }
          }
        } catch (\UnexpectedValueException $e){
        }
      }
      $pitch = [];
      foreach($output as $file){
        $data = file_get_contents($file);
        preg_match_all($pattern, $data, $matches, PREG_SET_ORDER);
        foreach($matches as $match){
          $pitchUrl = $this->twigExtension->generatePitch(str_replace(['\'','~'],'',$match[1]), false);
          if ($pitchUrl){
            $url = UrlHelper::siteUrl($pitchUrl);
            if (!in_array($url, $pitch)){
              $pitch[] = $url;
            }
          }
        }
      }
      if (!empty($pitch)){
        print_r($pitch);
        die();
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
  
  private function _registerEvents(): void {
    Event::on(Pitch::class, Pitch::EVENT_AFTER_SAVE_SETTINGS, function(Event $event){
      $entry = $event->sender;
      if ($entry::class == 'cloudgrayau\pitch\Pitch'){
        $cacheDir = (!empty($this->settings->cacheDir)) ? $this->settings->cacheDir : '@storage/pitch';
        if (($cacheDir !== $this->cacheDir) && (!empty($this->cacheDir))){
          $cacheFolderPath = FileHelper::normalizePath(
            App::parseEnv($this->cacheDir)
          ).'/';
          if (is_dir($cacheFolderPath)){
            FileHelper::removeDirectory($cacheFolderPath);
          }
        }
        $this->clearCache();
      }
    });
  }
  
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
  
  private function _registerTwigExtensions(): void {
    $this->twigExtension = new PitchTwigExtension();
    Craft::$app->getView()->registerTwigExtension($this->twigExtension);
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
  
  private function _registerWidgets(): void {
    Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES,
      function(RegisterComponentTypesEvent $event) {
        $event->types[] = CacheWidget::class;
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
