<?php

namespace Drupal\pwa\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\pwa\Model\SubscriptionsDatastorage;

/**
 * Processes Node Tasks.
 *
 * @QueueWorker(
 *   id = "cron_send_notification",
 *   title = @Translation("Task Worker: Push notification"),
 *   cron = {"time" = 10}
 * )
 */
class pwaQueueProcessor extends QueueWorkerBase {
  /**
   * {@inheritdoc}
   */
  public function processItem($response) {
    \Drupal::logger('pwa')->info("Node publish push notification sent to " . print_r($response->subscriptions));
    return SubscriptionsDatastorage::sendNotificationStart($response->subscriptions, $response->notification_data);
  }
}
