<?php
/**
 * Pitch plugin for Craft CMS 3.x
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
 * @since     1.2.0
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

  public $cssFormat = 'Compressed';
  public $minifyFiles = true;
  public $useCache = true;
  public $advancedCache = false;
  public $cacheDir = '';
  public $cacheDuration = 2592000;
  

  // Public Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  public function rules()
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
