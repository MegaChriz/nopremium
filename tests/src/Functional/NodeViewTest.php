<?php

namespace Drupal\Tests\nopremium\Functional;

use Drupal\Tests\nopremium\Functional\NopremiumBrowserTestBase;

/**
 * Tests displaying nodes.
 *
 * @group nopremium
 */
class NodeViewTest extends NopremiumBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a content type and enable premium for this type.
    $this->drupalCreateContentType(['type' => 'foo']);

    // Don't show body on teaser.
    $this->container->get('entity_type.manager')
      ->getStorage('entity_view_display')
      ->load('node.foo.teaser')
      ->removeComponent('body')
      ->save();
  }

  /**
   * Tests that the premium message is displayed for a premium node.
   */
  public function testViewPremiumNode() {
    $node = $this->drupalCreateNode([
      'type' => 'foo',
      'body'      => [
        [
          'value' => 'Lorem ipsum',
          'format' => filter_default_format(),
        ],
      ],
      'premium' => TRUE,
    ]);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextNotContains('Lorem ipsum');
    $this->assertSession()->pageTextContains('The full content of this page is available to premium users only.');
  }

  /**
   * Tests that the full content is displayed for a non-premium node.
   */
  public function testViewNonPremiumNode() {
    $node = $this->drupalCreateNode([
      'type' => 'foo',
      'body'      => [
        [
          'value' => 'Lorem ipsum',
          'format' => filter_default_format(),
        ],
      ],
    ]);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextContains('Lorem ipsum');
    $this->assertSession()->pageTextNotContains('The full content of this page is available to premium users only.');
  }

}
