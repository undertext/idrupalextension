<?php

namespace undertext\idrupalextension;

use Drupal\Core\Database\Database;
use undertext\idrupalextension\Utility\FileSystemUtility;

/**
 * Configurable Drupal test site installer.
 */
class DrupalTestSiteInstaller {

  /**
   * Installation directory for test site.
   *
   * We use the same path as Drupal use for tests because 'TestDatabase' class
   * and 'drupal_generate_test_ua' relies on this path.
   *
   * @see \Drupal\Core\Test\TestDatabase
   * @see \drupal_generate_test_ua()
   */
  const TEST_SITE_DIRECTORY = 'sites/simpletest';

  /**
   * Path to Drupal root.
   *
   * @var string
   */
  private $drupalRoot;

  /**
   * Unique test site id.
   *
   * Used to generate a test site directory name.
   *
   * @var string
   */
  private $siteId;

  /**
   * Path to directory where Drupal site will be installed.
   *
   * @var string
   */
  private $siteDirectory;

  /**
   * Database connection information for our test site.
   *
   * @var array
   */
  private $connection;

  /**
   * Name of the profile to install.
   *
   * @var string
   */
  private $profileName;

  /**
   * Reuse test site installation if set to TRUE.
   *
   * @var bool
   */
  private $reuseInstallation;

  /**
   * Indicate that test site installation is needed.
   *
   * @var bool
   */
  private $isInstallationNeeded = TRUE;

  /**
   * DrupalTestSiteInstaller constructor.
   *
   * @param string $drupalRoot
   *   Path to Drupal root.
   * @param string $profileName
   *   Name of profile to install.
   * @param bool $reuseInstallation
   *   Reuse or not to reuse existing test site.
   */
  public function __construct($drupalRoot, $profileName, $reuseInstallation) {
    $this->drupalRoot = $drupalRoot;
    $this->profileName = $profileName;
    $this->reuseInstallation = $reuseInstallation;
    $this->siteId = time();
    $this->siteDirectory = self::TEST_SITE_DIRECTORY . '/' . $this->siteId;
    // @todo Connection is hardcoded to sqlite for now.
    $this->connection = [
      'driver' => 'sqlite',
      'database' => $this->drupalRoot . '/test_database.sqlite',
    ];

    $sitesDir = $this->getDrupalRoot() . '/' . self::TEST_SITE_DIRECTORY;
    if (!file_exists($sitesDir)) {
      mkdir($sitesDir);
    }

    if ($this->isReuseInstallation()) {
      if (FileSystemUtility::getFilesCount($sitesDir) > 0) {
        $this->isInstallationNeeded = FALSE;
        $this->siteId = FileSystemUtility::getLastUpdatedDirectory($sitesDir);
        define('DRUPAL_TEST_IN_CHILD_SITE', FALSE);
        // We need to fix REQUEST_TIME because it is set very early on drupal-behat-extension bootstrap phase.
        $_SERVER['REQUEST_TIME'] = time();
        require_once $this->drupalRoot . '/core/includes/bootstrap.inc';
        $_COOKIE['SIMPLETEST_USER_AGENT'] = drupal_generate_test_ua('test' . $this->siteId);
      }
    }
  }

  /**
   * Install test Drupal site.
   */
  public function install() {
    if ($this->isInstallationNeeded) {
      $last_updated_directory = FileSystemUtility::getLastUpdatedDirectory($this->drupalRoot . '/' . self::TEST_SITE_DIRECTORY);
      if (!empty($last_updated_directory)) {
        FileSystemUtility::cleanDirectory($this->drupalRoot . '/' . self::TEST_SITE_DIRECTORY);
      }
      $class_loader = require $this->drupalRoot . '/autoload.php';
      chdir($this->drupalRoot);
      mkdir($this->siteDirectory, 0777, TRUE);
      require_once $this->drupalRoot . '/core/includes/install.core.inc';
      install_drupal($class_loader, $this->getInstallParameters());
      chmod($this->siteDirectory, 0777);
      require_once $this->drupalRoot . '/core/includes/bootstrap.inc';
      drupal_valid_test_ua('test' . $this->siteId);
      $_COOKIE['SIMPLETEST_USER_AGENT'] =  drupal_generate_test_ua('test' . $this->siteId);
    }
  }

  /**
   * Remove all files generated by site install.
   */
  public function cleanup() {
    if (!$this->reuseInstallation) {
      drupal_register_shutdown_function(function () {
        Database::closeConnection();
        unlink($this->drupalRoot . '/test_database.sqlite');
        FileSystemUtility::cleanDirectory($this->drupalRoot . '/' . self::TEST_SITE_DIRECTORY);
      });
    }
  }

  /**
   * Get site parameters that will be passed to Drupal site install function.
   *
   * @return array
   *   Test site install parameters.
   */
  private function getInstallParameters() {
    $parameters = [
      'interactive' => FALSE,
      'site_path' => $this->siteDirectory,
      'parameters' => [
        'profile' => $this->profileName,
        'langcode' => 'en',
      ],
      'forms' => [
        'install_settings_form' => [
          'driver' => $this->connection['driver'],
          $this->connection['driver'] => $this->connection,
        ],
        'install_configure_form' => [
          'site_name' => 'Drupal',
          'site_mail' => 'test@example.com',
          'account' => [
            'name' => 'admin',
            'mail' => 'test@example.com',
            'pass' => [
              'pass1' => 'admin',
              'pass2' => 'admin',
            ],
          ],
          'enable_update_status_module' => NULL,
          'enable_update_status_emails' => NULL,
        ],
      ],
    ];
    return $parameters;
  }

  /**
   * Get Drupal root.
   *
   * @return string
   *   Drupal root.
   */
  public function getDrupalRoot() {
    return $this->drupalRoot;
  }

  /**
   * Get site directory.
   *
   * @return string
   *   Site directory.
   */
  public function getSiteDirectory() {
    return $this->siteDirectory;
  }

  /**
   * Get connection.
   *
   * @return array
   *   Connection information.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * Get profile name.
   *
   * @return string
   *   Profile name.
   */
  public function getProfileName() {
    return $this->profileName;
  }

  /**
   * Get site id.
   *
   * @return string
   *   Site id.
   */
  public function getSiteId() {
    return $this->siteId;
  }

  /**
   * Get test prefix.
   *
   * @return string
   *   Test prefix.
   */
  public function getTestPrefix() {
    return 'test' . $this->siteId;
  }

  /**
   * Do we need to reuse installation.
   *
   * @return bool
   *   TRUE if we want to reuse an installation.
   */
  public function isReuseInstallation() {
    return $this->reuseInstallation;
  }

}
