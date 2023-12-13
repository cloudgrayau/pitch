<?php
namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\base\Model;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;

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
      ['cacheDuration', 'integer', 'min' => 0],
      ['cacheDir', 'validateDir']
    ];
  }
  
  public function validateDir($attribute): bool {
    $value = $this->$attribute;
    $cacheFolderPath = FileHelper::normalizePath(
      App::parseEnv($value)
    );
    $root = FileHelper::normalizePath(App::parseEnv('@root'));
    switch($cacheFolderPath){
      case $root:
      case FileHelper::normalizePath(App::parseEnv('@app')):
      case FileHelper::normalizePath(App::parseEnv('@config')):
      case FileHelper::normalizePath(App::parseEnv('@contentMigrations')):
      case FileHelper::normalizePath(App::parseEnv('@craft')):
      case FileHelper::normalizePath(App::parseEnv('@lib')):
      case FileHelper::normalizePath(App::parseEnv('@runtime')):
      case FileHelper::normalizePath(App::parseEnv('@storage')):
      case FileHelper::normalizePath(App::parseEnv('@templates')):
      case FileHelper::normalizePath(App::parseEnv('@translations')):
      case FileHelper::normalizePath(App::parseEnv('@vendor')):
      case FileHelper::normalizePath(App::parseEnv('@webroot')):
        $this->addError($attribute, 'Storage path is invalid');
        return false;
        break;
      default:
        if (UrlHelper::isRootRelativeUrl($cacheFolderPath)){
          if (mb_stripos($cacheFolderPath, $root) === false){
            $this->addError($attribute, 'Storage path needs to be relative of the craft installation');
            return false;
          }
        } else {
          $this->addError($attribute, 'Storage path needs to be a relative path');
          return false;
        }
        break;
    }
    return true;
  }
  
}
