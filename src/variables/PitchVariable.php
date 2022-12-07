<?php
/**
 * Pitch plugin for Craft CMS 4.x
 *
 * On the go SCSS compiling, CSS/JS minifying, merging and caching.
 *
 * @link      https://cloudgray.com.au/
 * @copyright Copyright (c) 2020 Cloud Gray Pty Ltd
 */

namespace cloudgrayau\pitch\variables;

use cloudgrayau\pitch\Pitch;

use Craft;
use ScssPhp\ScssPhp\Compiler;
use MatthiasMullie\Minify;

/**
 * Class PitchVariable
 *
 * @author    Cloud Gray Pty Ltd
 * @package   Pitch
 * @since     2.2.0
 *
 */
class PitchVariable {
  
    public function renderSCSS(String $output='') {
      $scss = new Compiler();
      $scss->setImportPaths(Craft::getAlias('@webroot').'/');
      $settings = Pitch::getInstance()->settings;
      $format = $settings->cssFormat;
      switch($format){ /* Depreciated scssphp 1.4 */
        case 'Compact':
        case 'Crunched':
        case 'Compressed':
          $format = \ScssPhp\ScssPhp\OutputStyle::COMPRESSED;
          break;
        case 'Nested':
        case 'Expanded':
          $format = \ScssPhp\ScssPhp\OutputStyle::EXPANDED;
          break;
      }
      $scss->setOutputStyle($format);
      return $scss->compileString($output)->getCss();
    }
    public function renderCSS(String $output='') {
      $minifier = new Minify\CSS();
      $minifier->add($output);
      return $minifier->minify();
    }
    public function renderJS(String $output='') {
      $minifier = new Minify\JS();
      $minifier->add($output);
      return $minifier->minify();
    }
    
}
