<?php
namespace T3v\T3vDelivery\Command;

use AFM\Rsync\Rsync;
use PHLAK\Config;
use TH\Lock\Exception;
use TH\Lock\FileLock;

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
        foreach ($buckets as $name => $configuration) {
          $this->syncBucket($name, $configuration, $verbose);
        }
      }

      $this->log('Syncing completed.', 'ok', $verbose);
    }
  }

  /**
   * Syncs a bucket.
   *
   * @param array $name The bucket name
   * @param array $configurtion The bucket configuration
   * @param boolean $verbose The optional verbosity, defaults to `false`
   */
  protected function syncBucket($name, $configuration, $verbose = false) {
    $name    = (string) $name;
    $sources = $configuration['sources'];
    $verbose = (boolean) $verbose;

    $this->log("Syncing `${name}` bucket...", 'info', $verbose);

    try {
      $fileLock = new FileLock($this->getLockFilePath($name));

      $fileLock->acquire();

      foreach ($sources as $source) {
        $path   = (string) $source['path'];
        $target = (string) $configuration['target'];

        if (!empty($path) && !empty($target)) {
          $origin = PATH_site . $path;

          if (is_dir($origin)) {
            $exclude          = $source['exclude']            ?? [];
            $recursive        = $source['recursive']          ?? true;
            $followSymlinks   = $source['follow_symlinks']    ?? true;
            $deleteFromTarget = $source['delete_from_target'] ?? false;
            $archive          = $source['archive']            ?? false;
            $dryRun           = $source['dry_run']            ?? false;

            $rsync = new Rsync;
            $rsync->setExclude($exclude);
            $rsync->setRecursive($recursive);
            $rsync->setFollowSymlinks($followSymlinks);
            $rsync->setDeleteFromTarget($deleteFromTarget);
            $rsync->setArchive($archive);
            $rsync->setDryRun($dryRun);
            $rsync->setVerbose($verbose);

            if (is_array($configuration['ssh'])) {
              $rsync->setSshOptions($configuration['ssh']);
            }

            $relativePath = $this->getRelativePath($path);

            if ($relativePath) {
              $target = $target . $relativePath;
            }

            $rsync->sync($origin, $target);
          } else {
            $this->log("Directory: `${origin}` not found.", 'error', $verbose);
          }
        }
      }

      $fileLock->release();

      $this->log("The bucket called `${name}` has been synced.", 'ok', $verbose);
    } catch (\TH\Lock\Exception $exception) {
      $this->log("The bucket called `${name}` is currently being synced.", 'warning', $verbose);
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

  /**
   * Gets the lock file path for an identifier, typical the name of a bucket.
   *
   * @param string $identifier The identifier
   * @return string The lock file path
  */
  protected function getLockFilePath($identifier) {
    $identifier   = (string) $identifier;
    $tempFolder   = PATH_site . 'typo3temp';
    $lockFilePath = "${tempFolder}/t3v_delivery-${identifier}.lock";

    return $lockFilePath;
  }

  /**
   * Gets the relative path.
   *
   * @param string $path The path
   * @return string|null The relative path or null
  */
  protected function getRelativePath($path) {
    $path         = (string) $path;
    $segments     = explode('/', $path);
    $relativePath = null;

    if (sizeof($segments) >= 2) {
      array_pop($segments);

      foreach ($segments as &$segment) {
        $relativePath = $relativePath . '/' . $segment;
      }
    }

    return $relativePath;
  }
}