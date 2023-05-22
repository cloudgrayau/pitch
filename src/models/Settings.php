<?php
namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\base\Model;

class Settings extends Model {
  
  // Static Variables
  // =========================================================================

  public array $formatOptions = [
    ['label' => 'Expanded', 'value' => 'Expanded'],
    ['label' => 'Compressed', 'value' => 'Compressed'],
  ];
  
  // Editable Variables
  // =========================================================================

  public string $cssFormat = 'Compressed';
  public bool $minifyFiles = true;
  public bool $useCache = true;
  public bool $advancedCache = false;
  public string $cacheDir = '';
  public int $cacheDuration = 2592000;
  
  // Public Methods
  // =========================================================================

  public function rules(): array {
    return [
      [['cacheDir','cssFormat'], 'string'],
      [['minifyFiles', 'useCache', 'advancedCache'], 'boolean'],
      ['cacheDuration', 'integer', 'min' => 0]
    ];
  }
}
