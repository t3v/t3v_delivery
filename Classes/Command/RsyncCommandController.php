<?php
namespace T3v\T3vDelivery\Command;

use AFM\Rsync\Rsync;

use PHLAK\Config;

use T3v\T3vCore\Command\AbstractCommandController;

/**
 * The rsync command controller class.
 *
 * @package T3v\T3vDelivery\Command
 */
class RsyncCommandController extends AbstractCommandController {
  /**
   * The configuration.
   *
   * @var \PHLAK\Config
   */
  protected $configuration = null;

  /**
   * The constructor function.
   */
  public function __construct() {
    $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['t3v_delivery'];

    if (is_string($extensionConfiguration)) {
      $extensionConfiguration = @unserialize($extensionConfiguration);
    }

    if (is_array($extensionConfiguration)) {
      $this->extensionConfiguration = $extensionConfiguration;

      $enabled = (boolean) $extensionConfiguration['enabled'];

      if ($enabled) {
        $this->enabled = $enabled;

        $this->loadConfiguration($extensionConfiguration['configurationFile']);
      }
    }
  }

  /**
   * The sync command.
   *
   * @param boolean $verbose The optional verbosity, defaults to `false`
   */
  public function syncCommand($verbose = false) {
    $verbose = (boolean) $verbose;

    if ($this->enabled) {
      $this->log('Syncing...', 'info', $verbose);

      $buckets = $this->configuration->get('buckets');

      if (is_array($buckets)) {
        foreach ($buckets as $bucket) {
          $this->syncBucket($bucket, $verbose);
        }
      }

      $this->log('Syncing complete.', 'ok', $verbose);
    }
  }

  /**
   * Syncs a bucket.
   *
   * @param array $bucket The bucket
   * @param boolean $verbose The optional verbosity, defaults to `false`
   */
  protected function syncBucket($bucket, $verbose = false) {
    $sources = $bucket['sources'];
    $verbose = (boolean) $verbose;

    foreach ($sources as $source) {
      $path   = (string) $source['path'];
      $target = (string) $bucket['target'];

      if (!empty($path) && !empty($target)) {
        $origin = PATH_site . $path;

        if (is_dir($origin)) {
          $exclude          = $source['exclude']                      ?: [];
          $recursive        = (boolean) $source['recursive']          ?: true;
          $followSymlinks   = (boolean) $source['follow_symlinks']    ?: true;
          $deleteFromTarget = (boolean) $source['delete_from_target'] ?: false;
          $dryRun           = (boolean) $source['dry_run']            ?: false;

          $rsync = new Rsync;
          $rsync->setExclude($exclude);
          $rsync->setRecursive($recursive);
          $rsync->setFollowSymlinks($followSymlinks);
          $rsync->setDeleteFromTarget($deleteFromTarget);
          $rsync->setDryRun($dryRun);
          $rsync->setShowOutput($verbose);
          $rsync->setVerbose($verbose);

          if (is_array($bucket['ssh'])) {
            $rsync->setSshOptions($bucket['ssh']);
          }

          // $rsync->sync($origin, $target);
        } else {
          $this->log("Directory: `${origin}` not found.", 'error', $verbose);
        }
      }
    }
  }

  /**
   * Loads the configuration.
   *
   * @param string $configurationFile The configuration file, related to the web site root directory
  */
  protected function loadConfiguration($configurationFile) {
    $configurationFile = (string) $configurationFile;

    if ($configurationFile) {
      $configurationFile = PATH_site . $configurationFile;

      if (@file_exists($configurationFile)) {
        $this->configuration = new Config\Config($configurationFile);
      } else {
        throw new \Exception("Configuration file: `${configurationFile}` not found.");
      }
    }
  }
}