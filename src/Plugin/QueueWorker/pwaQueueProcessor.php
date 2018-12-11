<?php

namespace Drupal\pwa\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

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
    $str = json_decode($response->notification_data, true);
    $nid = $str['content-details']['nodeid'];
    $node_type = $str['content-details']['nodetype'];
    $message = 'push notification for node:' . $nid . ' of type '. $node_type . ' is sent to ';
    foreach($response->subscriptions as $sub)
    {
     $message .= ' subscriber_id:'.$sub->id;
    } 
    \Drupal::logger('pwa')->info($message);
    $sendNotificationService = \Drupal::service('pwa.push_notifications');
    return $sendNotificationService::sendNotificationStart($response->subscriptions, $response->notification_data);
  }
}
