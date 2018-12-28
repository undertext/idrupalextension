<?php

namespace undertext\idrupalextension;

use Behat\Mink\Mink;

/**
 * Mink session manager that is aware of Drupal test site.
 */
class DrupalAwareMink extends Mink {

  /**
   * Test site installer.
   *
   * @var \undertext\idrupalextension\DrupalTestSiteInstaller
   */
  private $testSiteInstaller;

  /**
   * Drupal test site Url.
   *
   * @var string
   */
  private $siteUrl;

  /**
   * DrupalAwareMink constructor.
   *
   * @param \undertext\idrupalextension\DrupalTestSiteInstaller $testSiteInstaller
   *   Test site installer.
   * @param $siteUrl
   *   Site Url.
   * @param  \Behat\Mink\Session[] $sessions
   *   Sessions to register.
   */
  public function __construct(DrupalTestSiteInstaller $testSiteInstaller, $siteUrl, array $sessions = []) {
    parent::__construct($sessions);
    $this->testSiteInstaller = $testSiteInstaller;
    $this->siteUrl = $siteUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getSession($name = NULL) {
    $session = $this->locateSession($name);
    if (!$session->isStarted()) {
      $session->start();
      // Selenium needs to visit the domain to set the cookie.
      $session->visit($this->siteUrl);
    }
    if (empty($session->getCookie('SIMPLETEST_USER_AGENT'))) {
      $ua = drupal_generate_test_ua($this->testSiteInstaller->getTestPrefix());
      $session->setCookie('SIMPLETEST_USER_AGENT', $ua);
    }
    return $session;
  }

}
