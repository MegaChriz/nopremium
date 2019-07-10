<?php

namespace Drupal\Tests\nopremium\Functional;

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
   * Creates a node with body.
   *
   * @param string $body
   *   The body text.
   * @param array $values
   *   (optional) An associative array of values for the node.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node entity.
   */
  protected function createNodeWithBodyValue($body, array $values = []) {
    $values += [
      'type' => 'foo',
      'body'      => [
        [
          'value' => $body,
          'format' => filter_default_format(),
        ],
      ],
    ];
    return $this->drupalCreateNode($values);
  }

  /**
   * Tests that the premium message is displayed for a premium node.
   */
  public function testViewPremiumNode() {
    // Create a premium node.
    $node = $this->createNodeWithBodyValue('Lorem ipsum', [
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
    // Create a public node.
    $node = $this->createNodeWithBodyValue('Lorem ipsum');

    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextContains('Lorem ipsum');
    $this->assertSession()->pageTextNotContains('The full content of this page is available to premium users only.');
  }

  /**
   * Tests that the premium message is *not* displayed when hidden.
   */
  public function testWithHiddenPremiumMessage() {
    // Create a premium node.
    $node = $this->createNodeWithBodyValue('Lorem ipsum', [
      'premium' => TRUE,
    ]);

    // Don't show premium message on teaser.
    $this->container->get('entity_type.manager')
      ->getStorage('entity_view_display')
      ->load('node.foo.teaser')
      ->removeComponent('premium_message')
      ->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextNotContains('Lorem ipsum');
    $this->assertSession()->pageTextNotContains('The full content of this page is available to premium users only.');
  }

}
