<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\pwa\Model\SubscriptionsDatastorage;
use Drupal\Component\Serialization\Json;

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
    $icon = $pwa_config->get('icon_path');
    $icon_path = file_create_url($icon);

    $entry = [
      'title' => $form_state->getValue('title'),
      'message' => $form_state->getValue('message'),
      'icon' => $icon_path,
      'url' => "",
      'content-details' => [
        'nodeid' => "",
        'nodetype' => ""
      ]
    ];
    $notification_data = Json::encode($entry);
    $subscriptions = SubscriptionsDatastorage::loadAll();

    $pwa_public_key = $pwa_config->get('public_key');
    $pwa_private_key = $pwa_config->get('private_key');

    if (empty($pwa_public_key) && empty($pwa_private_key)) {
      drupal_set_message($this->t('Please set public & private key.'), 'error');
    }
    if (!empty($subscriptions) && !empty($pwa_public_key) && !empty($pwa_private_key)) {
      /** @var QueueFactory $queue_factory */
      $queue_factory = \Drupal::service('queue');
      /** @var QueueInterface $queue */
      $queue = $queue_factory->get('cron_send_notification');
      $item = new \stdClass();
      $item->subscriptions = $subscriptions;
      $item->notification_data = $notification_data;
      $queue->createItem($item);
    }
  }
}
