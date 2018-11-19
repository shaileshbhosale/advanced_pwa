<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\pwa\Model\SubscriptionsDatastorage;

/**
 * Class PwaForm.
 *
 * @package Drupal\pwa\Form
 */
class PwaBroadcastForm extends FormBase {

  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_broadcast_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title of the Message'),
      '#default_value' => '',
      '#required' => true
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message to broadcast'),
      '#default_value' => '',
      '#required' => true
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Notification'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $pwa_config = \Drupal::config('pwa.pwa');
    $pwa_subscription = \Drupal::config('pwa.pwa.subscription');
    $host = \Drupal::request()->getHost();

    $entry = [
      'title' => $form_state->getValue('title'),
      'body' => $form_state->getValue('message'),
      'icon' => $pwa_subscription->get('icon_path'),
    ];
    $notification_data = implode('<br>', array_filter($entry));
    $subscriptions = SubscriptionsDatastorage::loadAll();

    $pwa_public_key = $pwa_config->get('public_key');
    $pwa_private_key = $pwa_config->get('private_key');

    if (empty($pwa_public_key) && empty($pwa_private_key)) {
      drupal_set_message($this->t('Please set public & private key.'), 'error');
    }
    if (!empty($subscriptions) && !empty($pwa_public_key) && !empty($pwa_private_key)) {
      $batch = [
        'title' => $this->t('Sending Push Notification...'),
        'operations' => [
          [
            '\Drupal\pwa\Model\SubscriptionsDatastorage::sendNotificationStart',
            [$subscriptions, $notification_data],
          ],
        ],
        'finished' => '\Drupal\pwa\Model\SubscriptionsDatastorage::notificationFinished',
      ];
      batch_set($batch);
      drupal_set_message($this->t('Push notification sent successfully to  @entry users', ['@entry' => print_r(count($subscriptions), TRUE)]));
    }
    else {
      drupal_set_message($this->t('Subscription list is empty.'), 'error');
    }
  }
}
