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
    $form['icon'] = [
        '#type' => 'details',
        '#title' => t('PWA notification icon'),
        '#open' => TRUE,
      ];
      $form['icon']['settings'] = [
        '#type' => 'container',
      ];
      $form['icon']['settings']['icon_path'] = [
        '#type' => 'textfield',
        '#title' => t('Icon image'),
        '#default_value' => $config->get('icon_path'),
        '#disabled' => 'disabled',
      ];
      $form['icon']['settings']['icon_upload'] = [
        '#type' => 'file',
        '#title' => t('Upload icon image'),
        '#maxlength' => 40,
        '#description' => t("Upload PWA notification icon. Maximum allowed image diamention is 144 x 144."),
        '#upload_validators' => [
          'file_validate_is_image' => [],
          'file_validate_extensions' => array('png gif jpg jpeg'),
        ],
        '#states' => array(
          'enabled' => array(
              ':input[name="activate_feature"]' => array('checked' => TRUE),
          ),
        )
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
    $moduleHandler = \Drupal::service('module_handler');

  if ($moduleHandler->moduleExists('file')){
        // Check for a new uploaded logo.
        if (isset($form['icon'])) {
          $file = _file_save_upload_from_form($form['icon']['settings']['icon_upload'], $form_state, 0);
   
          if ($file) {
            $error = file_validate_image_resolution($file, 144, 144);
            if ($error) {
              $form_state->setErrorByName('icon_upload', $this->t('Image diamention is greater than 144 x 144.'));
            }
            // Put the temporary file in form_values so we can save it on submit.
            $form_state->setValue('icon_upload', $file);
          }
        }
      }
    }
  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    if (!empty($form_state->getValue('icon_upload'))) {
      $filename = file_unmanaged_copy($form_state->getValue('icon_upload')->getFileUri());
      $form_state->setValue('icon_path', $filename);

    }

    $this->config('pwa.pwa')
      ->set('gcm_key', trim($form_state->getValue('gcm_key')))
      ->set('public_key', trim($form_state->getValue('public_key')))
      ->set('private_key', trim($form_state->getValue('private_key')))
      ->set('icon_path', $form_state->getValue('icon_path'))
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
