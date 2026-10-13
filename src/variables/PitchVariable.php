<?php
namespace cloudgrayau\pitch\variables;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\services\AssetService;

use CraftCms\Cms\Support\Env;
use MatthiasMullie\Minify;
use ScssPhp\ScssPhp\Compiler;

class PitchVariable {

  // Public Methods
  // =========================================================================

  public function renderSCSS(string $output = ''): string {
    $scss = new Compiler();
    $scss->setImportPaths(rtrim(Env::parse('@webroot'), '/') . '/');
    $settings = Pitch::getInstance()->getSettings();
    $scss->setOutputStyle(AssetService::scssOutputStyle($settings->cssFormat));
    return $scss->compileString($output)->getCss();
  }

  public function renderCSS(string $output = ''): string {
    $minifier = new Minify\CSS();
    $minifier->add($output);
    return $minifier->minify();
  }

  public function renderJS(string $output = ''): string {
    $minifier = new Minify\JS();
    $minifier->add($output);
    return $minifier->minify();
  }

}