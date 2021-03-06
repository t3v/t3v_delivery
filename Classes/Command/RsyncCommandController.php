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
   * If the extension is enabled.
   *
   * @var bool
   */
  protected $enabled = false;

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

    if (is_array($extensionConfiguration) && !empty($extensionConfiguration)) {
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
   * @param bool $verbose The optional verbosity, defaults to `false`
   */
  public function syncCommand(bool $verbose = false) {
    if ($this->enabled) {
      $this->log('Syncing...', 'info', $verbose);

      $buckets = $this->configuration->get('buckets');

      if (is_array($buckets) && !empty($buckets)) {
        foreach ($buckets as $name => $configuration) {
          $this->syncBucket($name, $configuration, $verbose);
        }
      } else {
        $this->log('No buckets defined.', 'error', $verbose);
      }

      $this->log('Syncing completed.', 'ok', $verbose);
    }
  }

  /**
   * Syncs a bucket.
   *
   * @param string $name The bucket name
   * @param array $configurtion The bucket configuration
   * @param bool $verbose The optional verbosity, defaults to `false`
   */
  protected function syncBucket(string $name, array $configuration, bool $verbose = false) {
    $this->log("Syncing `${name}` bucket...", 'info', $verbose);

    $sources = $configuration['sources'];

    if (is_array($sources) && !empty($sources)) {
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
              $times            = $source['times']              ?? true;
              $compression      = $source['compression']        ?? true;
              $followSymlinks   = $source['follow_symlinks']    ?? false;
              $skipNewerFiles   = $source['skip_newer_files']   ?? false;
              $deleteFromTarget = $source['delete_from_target'] ?? false;
              $archive          = $source['archive']            ?? false;
              $info             = $source['info']               ?? false;
              $dryRun           = $source['dry_run']            ?? false;

              $rsync = new Rsync;
              $rsync->setExclude($exclude);
              $rsync->setRecursive($recursive);
              $rsync->setTimes($times);
              $rsync->setCompression($compression);
              $rsync->setFollowSymlinks($followSymlinks);
              $rsync->setSkipNewerFiles($skipNewerFiles);
              $rsync->setDeleteFromTarget($deleteFromTarget);
              $rsync->setArchive($archive);
              $rsync->setInfo($info);
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
    } else {
      $this->log("The bucket called `${name}` has no sources defined.", 'error', $verbose);
    }
  }

  /**
   * Loads the configuration.
   *
   * @param string $configurationFile The configuration file, relative to the web site root directory
   * @throws \Exception
  */
  protected function loadConfiguration(string $configurationFile) {
    if ($configurationFile) {
      $configurationFile = PATH_site . $configurationFile;

      if (@file_exists($configurationFile)) {
        $this->configuration = new Config\Config($configurationFile);
      } else {
        throw new \Exception("Configuration file `${configurationFile}` doesn't exist.");
      }
    }
  }

  /**
   * Gets the lock file path for an identifier, typical the name of a bucket.
   *
   * @param string $identifier The identifier
   * @return string The lock file path
  */
  protected function getLockFilePath(string $identifier): string {
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
  protected function getRelativePath(string $path) {
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
