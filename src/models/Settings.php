<?php
namespace cloudgrayau\pitch\models;

use Closure;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Support\Env;
use CraftCms\Cms\Validation\Rules\EnvValueRule;

class Settings extends PluginSettings {

  public string $cssFormat = 'Compressed';
  public bool $minifyFiles = true;
  public bool $useCache = true;
  public bool $regenerateCache = true;
  public bool $advancedCache = false;
  public string $cacheDir = '';
  public int $cacheDuration = 31536000;
  public array $fileHeaders = [];

  public function getRules(): array {
    return array_merge(parent::getRules(), [
      'cssFormat' => ['string'],
      'minifyFiles' => ['boolean'],
      'useCache' => ['boolean'],
      'regenerateCache' => ['boolean'],
      'advancedCache' => ['boolean'],
      'cacheDir' => new EnvValueRule(['string', $this->validateDir(...)]),
      'cacheDuration' => ['integer', 'min:0'],
      'fileHeaders' => ['array'],
    ]);
  }

  private function validateDir(string $attribute, mixed $value, Closure $fail): void {
    $path = $this->normalise((string) $value);
    $protected = ['@root', '@app', '@config', '@contentMigrations', '@craft', '@lib',
      '@runtime', '@storage', '@templates', '@translations', '@vendor', '@webroot'];
    foreach ($protected as $alias) {
      if ($path === $this->normalise(Env::parse($alias))) {
        $fail('Storage path is invalid');
        return;
      }
    }
    if (!str_starts_with($path, '/')) {
      $fail('Storage path needs to be a relative path');
      return;
    }
    if (!str_starts_with($path, $this->normalise(Env::parse('@root')) . '/')) {
      $fail('Storage path needs to be relative of the craft installation');
    }
  }

  private function normalise(string $path): string {
    return rtrim(str_replace('\\', '/', $path), '/');
  }

}