<?php

namespace undertext\idrupalextension\Listener;

use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use undertext\idrupalextension\DrupalTestSiteInstaller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for start/end of the behat test process.
 *
 * Run test site installation right before test suite is executed.
 * And then run a cleanup at the end of a test process.
 */
final class InstallSiteListener implements EventSubscriberInterface {

  /**
   * Test site installer.
   *
   * @var \undertext\idrupalextension\DrupalTestSiteInstaller
   */
  private $testSiteInstaller;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SuiteTested::BEFORE => ['onBeforeSuiteTested', 255],
      SuiteTested::AFTER => ['onAfterSuiteTested', 255],
    ];
  }

  /**
   * InstallSiteListener constructor.
   *
   * @param \undertext\idrupalextension\DrupalTestSiteInstaller $testSiteInstaller
   *   Test site installer.
   */
  public function __construct(DrupalTestSiteInstaller $testSiteInstaller) {
    $this->testSiteInstaller = $testSiteInstaller;
  }

  /**
   * Install test site before test suite is executed.
   */
  public function onBeforeSuiteTested() {
    $this->testSiteInstaller->install();
  }

  /**
   * Cleanup at the end of the test process.
   */
  public function onAfterSuiteTested() {
    $this->testSiteInstaller->cleanup();
  }
}
