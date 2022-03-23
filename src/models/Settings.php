<?php
/**
 * Pitch plugin for Craft CMS 4.x
 *
 * On the go SCSS compiling, CSS/JS minifying, merging and caching.
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch\models;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\base\Model;

/**
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     2.0.0
 */
class Settings extends Model
{
  // Public Properties
  // =========================================================================

  /**
   * @var string
   */
  public $formatOptions = [
    ['label' => 'Expanded', 'value' => 'Expanded'],
    ['label' => 'Compressed', 'value' => 'Compressed'],
  ];

  public string $cssFormat = 'Compressed';
  public bool $minifyFiles = true;
  public bool $useCache = true;
  public bool $advancedCache = false;
  public string $cacheDir = '';
  public int $cacheDuration = 2592000;
  

  // Public Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  public function rules(): array
  {
    return [
      [['cacheDir','cssFormat'], 'string'],
      [['minifyFiles', 'useCache', 'advancedCache'], 'boolean'],
      ['cacheDuration', 'integer', 'min' => 0],
      ['cssFormat', 'required'],
      ['cssFormat', 'default', 'value' => 'Compressed'],
    ];
  }
}
