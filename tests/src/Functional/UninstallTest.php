<?php

namespace Drupal\Tests\nopremium\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests module uninstallation.
 *
 * @group nopremium
 */
class UninstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['nopremium'];

  /**
   * Module handler to ensure installed modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public $moduleHandler;

  /**
   * Module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  public $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Reloads services used by this test.
   */
  protected function reloadServices() {
    $this->rebuildContainer();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Tests module uninstallation.
   */
  public function testUninstall() {
    // Confirm that the Node Option Premium module has been installed.
    $this->assertTrue($this->moduleHandler->moduleExists('nopremium'));

    // Uninstall Node Option Premium.
    $this->moduleInstaller->uninstall(['nopremium']);
    $this->assertFalse($this->moduleHandler->moduleExists('nopremium'));
  }

  /**
   * Tests that the module can be reinstalled.
   */
  public function testReinstall() {
    $this->moduleInstaller->uninstall(['nopremium']);
    $this->assertTrue($this->moduleInstaller->install(['nopremium']));
    $this->reloadServices();
    $this->assertTrue($this->moduleHandler->moduleExists('nopremium'));
  }

}
