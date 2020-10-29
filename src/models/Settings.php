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
 * @since     1.0.0
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
      ['label' => 'Nested', 'value' => 'Nested'],
      ['label' => 'Compressed', 'value' => 'Compressed'],
      ['label' => 'Compact', 'value' => 'Compact'],
      ['label' => 'Crunched', 'value' => 'Crunched']
    ];
    
    public $useCache = false;
    public $cacheDir = '';
    public $cssFormat = 'Crunched';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cacheDir','cssFormat'], 'string'],
            ['useCache', 'boolean'],
            ['cssFormat', 'required'],
            ['cssFormat', 'default', 'value' => 'Crunched'],
        ];
    }
}
