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

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a ThemeSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler instance to use.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);

    $this->moduleHandler = $module_handler;
  }

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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($this->moduleHandler->moduleExists('file')) {
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

    $this->configFactory->getEditable('pwa.pwa.subscription')
      ->set('enabled_content_types', $form_state->getValue('enabled_content_types'))
      ->set('activate_feature', $form_state->getValue('activate_feature'))
      ->set('icon_path', $form_state->getValue('icon_path'))
      ->save();
  }
}
