<?php
namespace cloudgrayau\pitch\widgets;

use cloudgrayau\pitch\helpers\CacheHelper;
use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Dashboard\Widgets\Widget;
use CraftCms\Cms\Support\Html;
use CraftCms\Cms\View\Enums\Position;
use CraftCms\Cms\View\HtmlStack;

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

  /** The line above the button; `pitch/refresh` also returns it, so the widget can update in place. */
  public static function status(): string {
    $settings = Pitch::getInstance()->getSettings();
    if (!$settings->useCache) {
      return t('Caching is turned off.', category: 'pitch');
    }
    $count = CacheHelper::count(CacheHelper::resolveDir($settings->cacheDir));
    return t('{num, plural, =0{No asset files are cached.} =1{1 asset file is cached.} other{# asset files are cached.}}', ['num' => $count], 'pitch');
  }

  public function component(): ?string {
    return 'craft:html-widget';
  }

  /**
   * Laid out like Craft's Updates widget: a status line, then a button. Without JS the form
   * posts normally; with JS it's sent with fetch, and a spinner shows until it finishes.
   */
  public function props(): array {
    $button = Html::tag('craft-button', Html::encode(t('Refresh Cache', category: 'pitch')), ['type' => 'submit', 'variant' => 'primary', 'style' => 'width:100%']);
    $form = Html::tag('form', csrf_field() . Html::tag('p', Html::encode(self::status()), ['data-status' => true]) . $button, [
      'method' => 'post',
      'action' => cp_url('pitch/refresh'),
      'class' => 'space-y-3',
    ]);
    // Wrapped in plain divs, since a web component's own display style can beat `hidden`
    $spinner = Html::tag('div', Html::tag('craft-spinner', Html::encode(t('Refreshing cache…', category: 'pitch')), ['visible' => true]), ['data-spinner' => true, 'hidden' => true]);
    $error = Html::tag('div', Html::tag('craft-callout', '', ['variant' => 'danger', 'role' => 'alert']), ['data-error' => true, 'hidden' => true]);
    app(HtmlStack::class)->jsWithVars(fn (string $failed): string => <<<JS
      (() => {
        const root = document.querySelector('[data-pitch-cache]:not([data-ready])');
        if (!root) return;
        root.dataset.ready = '';
        const form = root.querySelector('form');
        const spinner = root.querySelector('[data-spinner]');
        const error = root.querySelector('[data-error]');
        const busy = (on) => {
          form.hidden = on;
          spinner.hidden = !on;
        };
        form.addEventListener('submit', async (e) => {
          e.preventDefault();
          if (form.hidden) return;
          error.hidden = true;
          busy(true);
          try {
            const res = await fetch(form.action, {
              method: 'POST',
              body: new FormData(form),
              headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
              credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || $failed);
            if (data.status) form.querySelector('[data-status]').textContent = data.status;
            if (data.message) window.Craft?.cp?.displaySuccess?.(data.message, data.notificationSettings);
          } catch (err) {
            error.firstChild.textContent = err.message || $failed;
            error.hidden = false;
          } finally {
            busy(false);
          }
        });
      })();
      JS, [t('A server error occurred.', category: 'pitch')], Position::BodyEnd);
    return [
      'html' => Html::tag('div', $error . $spinner . $form, [
        'data-pitch-cache' => true,
        'class' => 'text-center space-y-3',
        'aria-live' => 'polite',
      ]),
    ];
  }

}
