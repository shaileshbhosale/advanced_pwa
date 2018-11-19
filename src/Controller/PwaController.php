<?php

namespace Drupal\pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\pwa\Model\SubscriptionsDatastorage;
use Drupal\pwa\manifest;
use Symfony\Component\HttpFoundation\Response;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
/**
 * Class PwaController.
 */
class PwaController extends ControllerBase {

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
   * Subscribe.
   *
   * @return string
   *   Return Hello string.
   */
  public function subscribe(Request $request) {
    if ($request) {
      $message = 'Subscribe: ' . $request->getContent();
      \Drupal::logger('pwa')->info($message);

      $data = json_decode($request->getContent(), TRUE);
      $entry['subscription_endpoint'] = $data['endpoint'];
      $entry['subscription_data'] = serialize(['key' => $data['key'], 'token' => $data['token']]);
      $entry['registered_on'] = strtotime(date('Y-m-d H:i:s'));
      $success = SubscriptionsDatastorage::insert($entry);
      return new JsonResponse([$success]);
    }
  }

  /**
   * Un-subscribe.
   *
   * @return string
   *   Return Hello string.
   */
  public function unsubscribe(Request $request) {
    if ($request) {
      $message = 'Un-subscribe : ' . $request->getContent();
      \Drupal::logger('pwa')->info($message);

      $data = json_decode($request->getContent(), TRUE);
      $entry['subscription_endpoint'] = $data['endpoint'];
      $success = SubscriptionsDatastorage::delete($entry);
      return new JsonResponse([$success]);
    }
  }

  /**
   * List of all subscribed users.
   */
  public function subscriptionList() {
    // The table description.
    $header = [
      ['data' => $this->t('Id'),],
      ['data' => $this->t('Subscription Endpoint')],
      ['data' => $this->t('Registeration Date'),],
    ];
    $getFields = [
      'id',
      'subscription_endpoint',
      'registered_on',
    ];
    $query = $this->database->select(SubscriptionsDatastorage::$subscriptionTable);
    $query->fields(SubscriptionsDatastorage::$subscriptionTable, $getFields);
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $result = $pager->execute();

    // Populate the rows.
    $rows = [];
    foreach ($result as $row) {
      $rows[] = [
        'data' => [
          'id' => $row->id,
          'register_id' => $row->subscription_endpoint,
          'date' => date('d/m/Y', $row->registered_on),
        ],
      ];
    }
    if (empty($rows)) {
      $markup = $this->t('No record found.');
    }
    else {
      $markup = $this->t('List of All Subscribed Users.');
    }
    $build = [
      '#markup' => $markup,
    ];
    // Generate the table.
    $build['config_table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];
    return $build;
  }

  /**
   * Subscribe.
   *
   * @return string
   *   Return Hello string.
   */
  public function send_notifications(Request $request) {
    if ($request) {
      $data = json_decode($request->getContent(), TRUE);
      $entry = [
        'title' => $data['title'],
        'body' => $data['body'],
        'icon' => $data['icon'],
        'url' => $data['url'],
      ];

      $notification_data = implode('<br>', array_filter($entry));
      $subscriptions = SubscriptionsDatastorage::loadAll();
      $public_key = $this->config('pwa.pwa')->get('public_key');
      $private_key = $this->config('pwa.pwa')->get('private_key');

      if (empty($public_key) && empty($private_key)) {
        drupal_set_message($this->t('Please set public & private key.'), 'error');
      }

      if (!empty($subscriptions) && !empty($public_key) && !empty($private_key)) {
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

  /**
   * route generates the manifest file for the browser
   *
   * @return Response
   */
  public function pwa_get_manifest() {
    $pwa_enabled = \Drupal::config('pwa.settings')->get('status.all');
    print_r($pwa_enabled);
    if (!$pwa_enabled) {
      echo "OK!!";
      return new JsonResponse([]);
    }

    // Get all the current settings stored in pwa.settings.
    $config = \Drupal::config('pwa.settings')->get();

    // Array filter used to filter the "_core:" key from the output.
    $allowed = [
      'name',
      'short_name',
      'icons',
      'start_url',
      'background_color',
      'theme_color',
      'display',
      'orientation',
    ];

    $filtered = [];

    foreach ($config as $config_key => $config_value) {
      if (!in_array($config_key, $allowed)) {
        continue;
      }

      if ($config_key == 'icons') {
        // Get the specific icons. Needed to get the correct path of the file.
        $icon = \Drupal::config('pwa.settings')->get('icons.icon');

        // Get the file id and path.
        $fid = $icon[0];
        /** @var \Drupal\file\Entity\File $file */
        $file = File::load($fid);
        $path = $file->getFileUri();

        $all_image_styles = ImageStyle::loadMultiple();
          foreach ($all_image_styles as $img) {
            $name_of_img = $img->getName();
            if(substr( $name_of_img, 0, 4 ) === "pwa_"){
              $image_style_config = (ImageStyle::load($name_of_img)->getEffects()->getConfiguration());
               foreach($image_style_config as $config) { 
                $image_style_height = $config['data']['height'];
                $image_style_width = $config['data']['width'];
                $imgdimensions = $image_style_height."x".$image_style_width;
                }
              $image_styles[$name_of_img] = $imgdimensions;
            }
          }

        $config_value = [];

        foreach ($image_styles as $key => $value) {
          $config_value[] = [
            'src' => file_url_transform_relative(ImageStyle::load($key)->buildUrl($path)),
            'sizes' => $value,
            'type' => 'image/png',
          ];
        }
      }
      $filtered[$config_key] = $config_value;
    }

    // Finally, after all the magic went down we return a manipulated and
    // filtered array of our pwa.settings and output it to JSON format.
    return new JsonResponse($filtered);
  
  }
  public function pwa_serviceworker_file_data() {
    $query_string = \Drupal::state()->get('system.css_js_query_string') ?: 0;
    $path = drupal_get_path('module', 'pwa');
    $data = 'importScripts("' . $path . '/js/service_worker.js?' . $query_string . '");';

    return new Response($data, 200, [
      'Content-Type' => 'application/javascript',
      'Service-Worker-Allowed' => '/',
    ]);
  }

}