<?php
namespace cloudgrayau\pitch\console;

use cloudgrayau\pitch\Pitch;

use CraftCms\Cms\Console\CraftCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pitch:clear')]
#[Description('Clears the Pitch cache')]
class ClearCommand extends Command {

  use CraftCommand;

  public function handle(): int {
    Pitch::getInstance()->clearCache();
    $this->info('Pitch cache cleared.');
    return self::SUCCESS;
  }

}