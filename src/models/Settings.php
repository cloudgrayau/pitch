<?php
namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\helpers\PathGuard;

use Closure;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Validation\Rules\EnvValueRule;
use Illuminate\Validation\Rule;

class Settings extends PluginSettings {

  public string $cssFormat = 'Compressed';
  public bool $minifyFiles = true;
  public bool $useCache = true;
  public bool $regenerateCache = true;
  public bool $advancedCache = false;
  public string $cacheDir = '';
  public int $cacheDuration = 31536000;
  public array $fileHeaders = [];

  private function validateDir(string $attribute, mixed $value, Closure $fail): void {
    if (($error = PathGuard::problem((string) $value)) !== null) {
      $fail($error);
    }
  }

  public function getRules(): array {
    return array_merge(parent::getRules(), [
      'cssFormat' => ['string', Rule::in(['Expanded', 'Compressed', 'Nested', 'Compact', 'Crunched'])], // the last three are scssphp 1.x names
      'minifyFiles' => ['boolean'],
      'useCache' => ['boolean'],
      'regenerateCache' => ['boolean'],
      'advancedCache' => ['boolean'],
      'cacheDir' => new EnvValueRule(['string', $this->validateDir(...)]),
      'cacheDuration' => ['integer', 'min:0'],
      'fileHeaders' => ['array'],
    ]);
  }

}