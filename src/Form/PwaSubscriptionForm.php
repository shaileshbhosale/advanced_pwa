<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class PwaForm.
 */
class PwaSubscriptionForm extends ConfigFormBase {
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
      'pwa.pwa.subscription',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_subscription_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pwa.pwa.subscription');
    $form = parent::buildForm($form, $form_state);

    $contentTypes = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $form['activate_feature'] = array(
      '#type' => 'checkbox',
      '#default_value' => $config->get('activate_feature'),
      '#title' => $this->t('activate this feature'),
    );
    $form['enabled_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content'),
      '#description' => $this->t('List of content types'),
      '#options' => $contentTypesList,
      '#default_value' => $config->get('enabled_content_types'),
      '#states' => array(
        'enabled' => array(
            ':input[name="activate_feature"]' => array('checked' => TRUE),
        ),
      )
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

    $this->configFactory->getEditable('pwa.pwa.subscription')
      ->set('enabled_content_types', $form_state->getValue('enabled_content_types'))
      ->set('activate_feature', $form_state->getValue('activate_feature'))
      ->save();
  }
}
