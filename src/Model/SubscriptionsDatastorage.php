<?php

namespace Drupal\advanced_pwa\Model;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

/**
 * Class SubscriptionsDatastorage.
 */
class SubscriptionsDatastorage {
  public static $subscriptionTable = 'advanced_pwa_subscriptions';

  /**
   * Save an entry in the database.
   *
   * @param array $entry
   *   An array containing all the fields of the database record.
   *
   * @return int
   *   The number of updated rows.
   *
   * @throws \Exception
   *   When the database insert fails.
   */
  public static function insert(array $entry) {
    $return_value = NULL;
    $arguments = [];
    $arguments[':endpoint'] = $entry['subscription_endpoint'];

    $subscription_exist = db_select(self::$subscriptionTable)
      ->fields('advanced_pwa_subscriptions')
      ->where('subscription_endpoint=:endpoint', $arguments)
      ->execute()
      ->fetchAll();
    if ($subscription_exist) {
      return $subscription_exist;
    }

    try {
      $return_value = db_insert('advanced_pwa_subscriptions')
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      drupal_set_message(t('db_insert failed. Message = %message, query= %query',
        ['%message' => $e->getMessage(), '%query' => $e->query_string]), 'error');
    }

    return $return_value;
  }

  /**
   * Delete an entry in the database.
   *
   * @param array $entry
   *   An array containing endpoint field of the database record.
   *
   * @return int
   *   The number of deleted rows.
   *
   * @throws \Exception
   *   When the database insert fails.
   */
  public static function delete(array $entry) {
    $return_value = NULL;
    $arguments = [];
    $arguments[':endpoint'] = $entry['subscription_endpoint'];

    $subscription_exist = db_select(self::$subscriptionTable)
      ->fields('advanced_pwa_subscriptions')
      ->where('subscription_endpoint=:endpoint', $arguments)
      ->execute()
      ->fetchAll();
    if (!$subscription_exist) {
      return NULL;
    }

    try {
      $return_value = db_delete('advanced_pwa_subscriptions')
        ->where('subscription_endpoint=:endpoint', $arguments)
        ->execute();
    }
    catch (\Exception $e) {
      drupal_set_message(t('db_delete failed. Message = %message, query= %query',
        ['%message' => $e->getMessage(), '%query' => $e->query_string]), 'error');
    }

    return $return_value;
  }

  /**
   * Load all client subscription details to send notification.
   */
  public static function loadAll() {
    // Read all fields from the browser_subscriptions table.
    $select = db_select(self::$subscriptionTable, 'advanced_pwa_subscriptions');
    $select->fields('advanced_pwa_subscriptions');
    return $select->execute()->fetchAll();
  }

  /**
   * Batch process to start subscription.
   *
   * @param array $subscriptionData
   *   Array of subscription data.
   * @param string $notification_data
   *   String of subscription data.
   */
  public static function sendNotificationStart(array $subscriptionData, $notification_data) {
    if (!empty($subscriptionData) && !empty($notification_data)) {
      foreach ($subscriptionData as $subscription) {
        $subscription_data = unserialize($subscription->subscription_data);
        $subscription_endpoint = $subscription->subscription_endpoint;
        $key = $subscription_data['key'];
        $token = $subscription_data['token'];
        $public_key = \Drupal::config('advanced_pwa.advanced_pwa')->get('public_key');
        $private_key = \Drupal::config('advanced_pwa.advanced_pwa')->get('private_key');

        if (!empty($key) && !empty($token) && !empty($subscription_endpoint)) {
          $host = \Drupal::request()->getHost();
          $auth = [
            'VAPID' => [
              'subject' => $host,
              'publicKey' => $public_key,
              'privateKey' => $private_key,
            ],
          ];
          $sub = new Subscription(
            $subscription_endpoint,
            $key,
            $token
          );
          $webPush = new WebPush($auth);
          $webPush->sendNotification(
            $sub,
            $notification_data,
            TRUE
          );
        }

      }
    }
  }

  /**
   * Batch End process.
   */
  public static function notificationFinished() {
    return TRUE;
  }

}
