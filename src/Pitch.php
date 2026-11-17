<?php
namespace cloudgrayau\pitch;

use cloudgrayau\pitch\console\ClearCommand;
use cloudgrayau\pitch\helpers\CacheHelper;
use cloudgrayau\pitch\helpers\PathGuard;
use cloudgrayau\pitch\jobs\CacheJob;
use cloudgrayau\pitch\models\Settings;
use cloudgrayau\pitch\services\AssetService;
use cloudgrayau\pitch\twigextensions\PitchTwigExtension;
use cloudgrayau\pitch\variables\PitchVariable;
use cloudgrayau\pitch\widgets\CacheWidget;
use cloudgrayau\utils\UtilityHelper;

use CraftCms\Cms\Cms;
use CraftCms\Cms\Cp\SelectOptions;
use CraftCms\Cms\Ui\{Ui, UiContext};
use CraftCms\Cms\Ui\Controls\{Choice, Lightswitch, Text};
use CraftCms\Cms\Ui\Nodes\{ActionMenu, Field};
use CraftCms\Cms\Plugin\{Plugin, PluginSettings};
use CraftCms\Cms\Plugin\Events\PluginSettingsSaved;
use CraftCms\Cms\Support\Facades\Twig;
use CraftCms\Cms\Twig\Variables\CraftVariable;
use CraftCms\Cms\Utility\Utilities\ClearCaches;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

use function CraftCms\Cms\t;

class Pitch extends Plugin {

  protected array $commands = [ClearCommand::class];
  protected array $widgets = [CacheWidget::class];
  private string $cacheDir = '';

  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;
  public ?PitchTwigExtension $twigExtension = null;

  #[\Override]
  protected static function createSettings(): PluginSettings {
    return new Settings();
  }

  public function boot(): void {
    $this->cacheDir = $this->getSettings()->cacheDir;
    $this->twigExtension = new PitchTwigExtension();
    Twig::registerExtension($this->twigExtension);
    CraftVariable::macro('pitch', fn () => new PitchVariable());
    ClearCaches::add('pitch', [
      'label' => t('Pitch cache', category: 'pitch'),
      'action' => fn () => $this->clearCache(),
    ]);
    Event::listen(function (PluginSettingsSaved $event): void {
      if ($event->plugin->handle === $this->handle) {
        $this->onSettingsSaved();
      }
    });
  }

  /** Clears the cache, then queues its regeneration when that's turned on (unless `$queue` is false). Returns whether it cleared. */
  public function clearCache(bool $queue = true): bool {
    $settings = $this->getSettings();
    $path = CacheHelper::resolveDir($settings->cacheDir);
    if (PathGuard::problem($path) !== null) {
      Log::warning('Pitch: refusing to clear an unsafe cache directory: ' . $path);
      return false;
    }
    CacheHelper::clear($path); // only the files Pitch wrote
    if ($queue && !self::devMode() && $settings->regenerateCache) {
      dispatch(new CacheJob());
    }
    return true;
  }

  /**
   * Clears the cache and regenerates it in this request, rather than in a queue job that may wait
   * behind others. Building in-process needs no HTTP requests back to the site, so it can't tie up
   * another PHP worker. For the dashboard widget, whose request has the site's base URL.
   */
  public function refreshCache(): void {
    if (!$this->clearCache(false)) {
      return;
    }
    $settings = $this->getSettings();
    if (self::devMode() || !$settings->regenerateCache || !$settings->useCache) {
      return;
    }
    $assets = app(AssetService::class);
    foreach (CacheJob::paths() as $path) {
      try {
        $assets->generate($path, strstr($path, '/', true));
      } catch (\Throwable $e) {
        Log::info("Pitch couldn't regenerate $path: " . $e->getMessage());
      }
    }
  }
  
  public static function devMode(): bool {
    return app()->hasDebugModeEnabled() || Cms::config()->devMode;
  }

  public function onSettingsSaved(): void {
    $new = $this->getSettings()->cacheDir;
    if (!empty($this->cacheDir) && $new !== $this->cacheDir) {
      $old = CacheHelper::resolveDir($this->cacheDir);
      CacheHelper::clear($old); // only the files Pitch wrote, so even an unsafe old path is safe to clear
      @rmdir($old); // and the folder itself, if that left it empty
    }
    $this->clearCache();
  }

  public function settingsUi(UiContext $context = new UiContext): ?Ui {
    return Ui::make([
      ActionMenu::make('pitch-settings-actions', [[
        'label' => t('Clear Cache', category: 'pitch'),
        'action' => 'pitch/cache/clear',
        'confirm' => t('Are you sure you wish to clear the cache?', category: 'pitch'),
      ]]),
      Field::make(t('SCSS Output Style', category: 'pitch'), Choice::make('cssFormat')->options([
        ['value' => 'Expanded', 'label' => 'Expanded'],
        ['value' => 'Compressed', 'label' => 'Compressed'],
      ]))
        ->instructions(t('The output style for your compiled SCSS files.', category: 'pitch'))
        ->required()
        ->warning(UtilityHelper::configWarning($this->handle, 'cssFormat')),
      Field::make(t('Enable Minification', category: 'pitch'), Lightswitch::make('minifyFiles'))
        ->instructions(t('Whether CSS/JS minification should be enabled.', category: 'pitch'))
        ->warning(UtilityHelper::configWarning($this->handle, 'minifyFiles')),
      Field::make(t('Enable Cache', category: 'pitch'), Lightswitch::make('useCache'))
        ->instructions(t('Whether caching should be enabled.', category: 'pitch'))
        ->warning(UtilityHelper::configWarning($this->handle, 'useCache')),
      Field::make(t('Regenerate Cache', category: 'pitch'), Lightswitch::make('regenerateCache'))
        ->instructions(t('Whether Pitch should try and automatically regenerate the cache when cleared (disabled in development mode).', category: 'pitch'))
        ->warning(UtilityHelper::configWarning($this->handle, 'regenerateCache')),
      Field::make(t('Enable Advanced Caching', category: 'pitch'), Lightswitch::make('advancedCache'))
        ->instructions(t('Offers superior performance but requires [server rewrites](https://github.com/cloudgrayau/pitch/blob/craft6/ADVANCED.md) and a public storage path. Recommended for production. Ignored if Enable Cache is off.', category: 'pitch'))
        ->warning(UtilityHelper::configWarning($this->handle, 'advancedCache')),
      Field::make(
        t('Cache Storage Path', category: 'pitch'),
        Text::make('cacheDir')
          ->placeholder('@storage/runtime/pitch')
          ->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers(true))
      )
        ->instructions(t("The path to your cache folder. Advanced caching requires a public location, for example '@webroot/pitch'.", category: 'pitch'))
        ->warning(UtilityHelper::configWarning($this->handle, 'cacheDir')),
      Field::make(
        t('Cache Duration', category: 'pitch'),
        Text::make('cacheDuration')->inputType('number')->min(0)->placeholder('2592000')
      )
        ->instructions(t('Total seconds to cache files. Ignored when advanced caching is set up correctly.', category: 'pitch'))
        ->warning(UtilityHelper::configWarning($this->handle, 'cacheDuration')),
      UtilityHelper::footer(),
    ]);
  }

}