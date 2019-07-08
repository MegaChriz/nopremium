<?php

namespace Drupal\Tests\nopremium\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Provides a base class for Node Option Premium functional tests.
 */
abstract class NopremiumBrowserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'nopremium',
  ];

  /**
   * The account that may configure Node Option Premium.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $admin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->admin = $this->drupalCreateUser([
      'administer content types',
      'administer nodes',
    ]);
  }

}
