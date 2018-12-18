<?php

namespace Drupal\advanced_pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdvancedpwaSubscriptionForm.
 */
class AdvancedpwaSubscriptionForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'advanced_pwa.advanced_pwa.subscription',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advanced_pwa_subscription_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('advanced_pwa.advanced_pwa.subscription');
    $form = parent::buildForm($form, $form_state);

    $contentTypes = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $form['activate_feature'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('activate_feature'),
      '#title' => $this->t('activate this feature'),
    ];
    $form['enabled_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content'),
      '#description' => $this->t('notifications will be pushed only for checked content types'),
      '#options' => $contentTypesList,
      '#default_value' => $config->get('enabled_content_types'),
      '#states' => [
        'enabled' => [
          ':input[name="activate_feature"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->configFactory->getEditable('advanced_pwa.advanced_pwa.subscription')
      ->set('enabled_content_types', $form_state->getValue('enabled_content_types'))
      ->set('activate_feature', $form_state->getValue('activate_feature'))
      ->save();
  }

}
