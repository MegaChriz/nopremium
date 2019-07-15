<?php

namespace Drupal\nopremium\Form;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nopremium_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nopremium.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $current_url = Url::createFromRequest($request);
    $nopremium_config = $this->config('nopremium.settings');
    $form['message'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Premium messages'),
      '#description' => $this->t('You may customize the messages displayed to unprivileged users trying to view full premium contents.'),
    ];
    $form['message']['nopremium_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Default message'),
      '#description' => $this->t('This message will apply to all content types with blank messages below.'),
      '#default_value' => $nopremium_config->get('default_message'),
      '#rows' => 3,
      '#required' => TRUE,
    ];
    foreach ($this->entityTypeManager->getStorage('node_type')->loadMultiple() as $content_type) {
      $form['message']['nopremium_message_' . $content_type->id()] = [
        '#type' => 'textarea',
        '#title' => $this->t('Message for %type content type', ['%type' => $content_type->label()]),
        '#default_value' => $nopremium_config->get('default_message' . $content_type->id()),
        '#rows' => 3,
      ];
    }
    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $form['message']['token_tree'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['user', 'node'],
        '#weight' => 90,
      ];
    }
    else {
      $form['message']['token_tree'] = [
        '#markup' => '<p>' . $this->t('Enable the <a href="@drupal-token">Token module</a> to view the available token browser.', ['@drupal-token' => 'http://drupal.org/project/token']) . '</p>',
      ];
    }
    $options = [];
    foreach ($this->entityDisplayRepository->getViewModes('node') as $id => $view_mode) {
      $options[$id] = $view_mode['label'];
    }
    $form['view_modes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Premium display modes'),
      '#description' => $this->t('Select which for view modes access is restricted. When none is selected, all are restricted.'),
      '#default_value' => $nopremium_config->get('view_modes'),
      '#options' => $options,
    ];
    $form['nopremium_teaser_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Teaser display mode'),
      '#description' => $this->t('Teaser display view mode to render for premium contents.'),
      '#default_value' => $nopremium_config->get('teaser_view_mode'),
      '#options' => $options,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('nopremium.settings')
      ->set('default_message', $values['nopremium_message'])
      ->set('view_modes', $values['view_modes'])
      ->set('teaser_view_mode', $values['nopremium_teaser_view_mode'])
      ->save();
    foreach ($this->entityTypeManager->getStorage('node_type')->loadMultiple() as $content_type) {
      $this->config('nopremium.settings')
        ->set('default_message' . $content_type->id(), $values['nopremium_message_' . $content_type->id()])
        ->save();
    }
    parent::submitForm($form, $form_state);
  }

}
