<?php
namespace cloudgrayau\pitch\variables;

use cloudgrayau\pitch\Pitch;

use Craft;
use ScssPhp\ScssPhp\Compiler;
use MatthiasMullie\Minify;

class PitchVariable {
    
    // Public Methods
    // =========================================================================
  
    public function renderSCSS(String $output=''): string {
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
    public function renderCSS(String $output=''): string {
      $minifier = new Minify\CSS();
      $minifier->add($output);
      return $minifier->minify();
    }
    public function renderJS(String $output=''): string {
      $minifier = new Minify\JS();
      $minifier->add($output);
      return $minifier->minify();
    }
    
}
