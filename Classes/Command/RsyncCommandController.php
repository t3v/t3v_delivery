<?php
namespace T3v\T3vDelivery\Command;

use AFM\Rsync\Rsync;

use T3v\T3vCore\Command\AbstractCommandController;

/**
 * The rsync command controller class.
 *
 * @package T3v\T3vDelivery\Command
 */
class RsyncCommandController extends AbstractCommandController {
  /**
   * The sync command.
   *
   * @return void
   */
  public function syncCommand() {
    $this->beforeCommand();

    $this->log('Syncing...');

    // $origin = __DIR__;
    // $target = '/target/dir/';
    //
    // $rsync = new Rsync;
    // $rsync->sync($origin, $target);

    $this->log('Syncing complete.');
  }

  /**
   * Helper function which gets executed before a command.
   *
   * @return void
   */
  protected function beforeCommand() {
    // ...
  }
}