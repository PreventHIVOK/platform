<?php
/**
 * Contains \PreventHivOk\Scaffold\Files.
 */
declare(strict_types=1);

namespace PreventHivOk\Scaffold;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * Script to manage the configuration of the project.
 */
class Files implements FilesInterface
{
  /**
   * An instance of \Composer\Composer.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * An instance of Composer's input/output helper interface.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $consoleIO;

  /**
   * Directories required for Drupal 8 installation.
   *
   * @var string[]
   */
  protected $directories = [
    'modules',
    'profiles',
    'themes',
    'libraries',
  ];

  /**
   * Dotfiles included with Drupal 8 core.
   *
   * @var string[]
   */
  protected $dotFiles = [
    '.csslintrc',
    '.editorconfig',
    '.eslintingnore',
    '.eslintrc.json',
    '.gitattributes',
  ];

  /**
   * An instance of \Composer\Script\Event.
   *
  * @var \Composer\Script\Event
  */
  protected $event;

  /**
   * An instance of \Symfony\Component\Filesystem\Filesystem.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * Path to the project root.
   *
   * @var string
   */
  protected $projectRoot;

  /**
   * Path to the website root.
   *
   * @var string
   */
  protected $webRoot;

  /**
  * Gets the required directories.
  *
  * @return string[]
  */
  public function getDirectories(): array
  {
    return $this->directories;
  }

  /**
   * Get the Composer input/output interface instance.
   *
   * @return IOInterface Composer input/output interface.
   */
  public function getIO(): IOInterface
  {
    return $this->consoleIO;
  }

  /**
   * Get Filesystem instance.
   *
   * @return Filesystem
   */
  public function getFilesystem(): Filesystem
  {
    return $this->filesystem;
  }

  /**
   * Get the project root path.
   *
   * @return string
   */
  public function getProjectRoot(): string
  {
    return $this->projectRoot;
  }

  /**
   * Get the web root path.
   *
   * @return string
   */
  public function getWebRoot(): string
  {
    return $this->webRoot;
  }

  /**
   * Simple constructor.
   *
   * @param Event $event
   *
   * @todo  Explain exactly what the constructor is doing.
   */
  public function __construct(Event $event)
  {
    $this->composer  = $event->getComposer();
    $this->consoleIO = $event->getIO();
    $this->event     = $event;

    $this->filesystem = new Filesystem();

    $finder = new DrupalFinder();
    $finder->locateRoot(getcwd());

    $this->projectRoot = $finder->getComposerRoot();
    $this->webRoot     = $finder->getDrupalRoot();
  }

  /**
   * @inheritDoc
   */
  public static function create(Event $event)
  {
    $creator      = new self($event);
    $filesystem   = $creator->getFilesystem();
    $consoleIO    = $creator->getIO();
    $projectRoot  = $creator->getprojectRoot();
    $webRoot      = $creator->getWebRoot();

    // Required for unit testing
    foreach ($creator->getDirectories() as $dir) {
      if (false === $filesystem->exists($webRoot . '/'. $dir)) {
        $filesystem->mkdir($webRoot . '/'. $dir);
        $filesystem->touch($webRoot . '/'. $dir . '/.gitkeep');
      }
    }

    // Prepare the settings file for installation
    if (false === $filesystem->exists($webRoot . '/sites/default/settings.php') && true === $filesystem->exists($webRoot . '/sites/default/default.settings.php')) {
      $filesystem->copy($webRoot . '/sites/default/default.settings.php', $webRoot . '/sites/default/settings.php');
      // Include Drupal 8's bootstrap and installation files.
      require_once $webRoot . '/core/includes/bootstrap.inc';
      require_once $webRoot . '/core/includes/install.inc';

      if (true === defined('CONFIG_SYNC_DIRECTORY')) {
        $syncDir = constant("CONFIG_SYNC_DIRECTORY");
        $settings['config_directories'] = [
          $syncDir => (object) [
            'value'    => Path::makeRelative($projectRoot . '/config/sync', $webRoot),
            'required' => true,
          ],
        ];
      }

      if (true === function_exists("drupal_rewrite_settings")) {
        $parameters[] = $settings;
        $parameters[] = $webRoot . '/sites/default/settings.php';
        call_user_func_array("drupal_rewrite_settings", $parameters);
      }

      $filesystem->chmod($webRoot . '/sites/default/settings.php', 0666);
      $consoleIO->write("Created file: 'sites/default/settings.php' (mode 0666)");
    }

    // Prepare the services file for installation
    if (false === $filesystem->exists($webRoot . '/sites/default/services.yml') && true === $filesystem->exists($webRoot . '/sites/default/default.services.yml')) {
      $filesystem->copy($webRoot . '/sites/default/default.services.yml', $webRoot . '/sites/default/services.yml');
      $filesystem->chmod($webRoot . '/sites/default/services.yml', 0666);
      $consoleIO->write("Created file: 'sites/default/services.yml' (mode 0666)");
    }

    $creator->createDefaultFilesDirectory();
    $creator->copyCoreDotFiles();
  }

  /**
   * Creates 'sites/default/files' directory with mode 0777.
   *
   * @return void
   */
  public function createDefaultFilesDirectory()
  {
    $relativePath = "sites/default/files";
    $fullPath     = "$this->webRoot/$relativePath";

    if (false === $this->filesystem->exists($fullPath)) {
      $oldMask = umask(0);
      $this->filesystem->mkdir($fullPath, 0777);
      umask($oldMask);
      $this->consoleIO->write("Created directory: '$relativePath' (mode 0777)");
    }
  }

  /**
   * Copy dotfiles from Drupal 8 root.
   *
   * @return void
   */
  public function copyCoreDotFiles()
  {
    foreach ($this->dotFiles as $file) {

      $originFile = "$this->webRoot/$file";
      $targetFile = "$this->webRoot/../$file";

      if (false === $this->filesystem->exists($targetFile) && true === $this->filesystem->exists($originFile)) {
        $this->filesystem->copy($originFile, $targetFile);
        $this->consoleIO->write("Copied '$file' into your project root directory.");
      }
    }
  }
}
