<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Minishlink\WebPush\VAPID;

/**
 * Class PwaForm.
 */
class PwaForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'pwa.pwa',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pwa.pwa');
    $form = parent::buildForm($form, $form_state);

    $form['gcm_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GCM Key'),
      '#description' => $this->t('Google Cloud Messaging (GCM) key'),
      '#maxlength' => 50,
      '#size' => 50,
      '#default_value' => $config->get('gcm_key'),
    ];

    $form['public_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public Key'),
      '#description' => $this->t('VAPID authentication public key.'),
      '#maxlength' => 100,
      '#size' => 100,
      '#default_value' => $config->get('public_key'),
      '#required' => true,
    ];
    $form['private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Private key'),
      '#description' => $this->t('VAPID authentication private key.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('private_key'),
      '#required' => true,
    ];

    $public_key = $config->get('public_key');
    if (empty($public_key)) {
      $form['actions']['generate'] = [
        '#type' => 'submit',
        '#value' => $this->t('Generate keys'),
        '#limit_validation_errors' => array(),
        '#submit' => ['::generateKeys'],
      ];
    }

    return $form;
//    return parent::buildForm($form, $form_state);
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

    $this->config('pwa.pwa')
      ->set('gcm_key', trim($form_state->getValue('gcm_key')))
      ->set('public_key', trim($form_state->getValue('public_key')))
      ->set('private_key', trim($form_state->getValue('private_key')))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function generateKeys(array &$form, FormStateInterface $form_state) {
    $keys = VAPID::createVapidKeys();
    $this->config('pwa.pwa')
      ->set('public_key', $keys['publicKey'])
      ->set('private_key', $keys['privateKey'])
      ->save();
  }
}
