<?php
namespace cloudgrayau\pitch\twigextensions;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\models\Paths;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class PitchTwigExtension extends AbstractExtension {
  
    private array $pitch = [];
    
    // Public Methods
    // =========================================================================
    
    public function getName(): string {
        return 'Pitch';
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('pitch', [$this, 'generatePitch']),
        ];
    }

    public function generatePitch(string $pitch = '', bool $base = true): string {
      if (isset($this->pitch[$pitch])){
        return ($base) ? UrlHelper::url($this->pitch[$pitch]) : ('/'.$this->pitch[$pitch]);
      } else {
        $path = parse_url(FileHelper::normalizePath($pitch));
        $paths = pathinfo($path['path']);
        if (isset($paths['extension'])){
          switch($paths['extension']){
            case 'css':
            case 'scss':
            case 'js':
              $output = explode('/', $paths['dirname'].'/'.$paths['filename'].'.'.$paths['extension']);
              if (empty($output[0])){
                array_shift($output);
              }
              Paths::$output = $output;
              extract(Paths::getPaths());
              $webroot = Craft::getAlias('@webroot').'/';
              $settings = Pitch::getInstance()->settings;
              $ext = strrchr($files,'.');
              $files = explode(',', substr($files, 0, -strlen($ext)));
              $realfiles = [];
              $filemtime = 0;
              foreach($files as $file){
                $pos = strpos($file,':');
                if (($pos !== false) && (ctype_digit(substr($file, $pos+1)))){
                  $file = substr($file, 0, $pos);
                }
                $asset = FileHelper::normalizePath($webroot.$dir.$file.'.'.$paths['extension']);
                if (file_exists($asset)){
                  $realfiles[] = $file;
                  $mtime = FileHelper::lastModifiedTime($asset);
                  if ($mtime > $filemtime){
                    $filemtime = $mtime;
                  }
                }
              }
              if ($filemtime > 0){
                $url = $paths['extension'].'/'.$dir.implode(',',$realfiles).':'.$filemtime.'.'.$paths['extension'];
                $this->pitch[$pitch] = $url;
                return ($base) ? UrlHelper::url($url) : ('/'.$url);
              }
              break;
          }
        }
      }
      return '';
    }
    
}
