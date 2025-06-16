<?php
namespace cloudgrayau\pitch\twigextensions;

use cloudgrayau\pitch\Pitch;

use craft\helpers\ArrayHelper;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class PitchTwigExtension extends AbstractExtension {
    
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

    public function generatePitch(string $url = ''): string {
        if (!empty($url)){
          return Craft::$app->getCurrentSite()->getBaseUrl();
        }
    }
    
}
