<?php

namespace Drupal\file_logger;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FileLoggerService
 *
 * Usage:
 * $our_service = \Drupal::service('file_logger.service');
 * $our_service->log("ERROR", $module, $object);
 */
class FileLoggerService {

  /**
   * The state.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Configuration constructor.
   *
   * @param \Drupal\Core\State\State $state
   *   The state.
   */
  public function __construct(State $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('state'),
      $container->get('messenger'),
    );
  }

  /**
   * Returns a log string
   * @return string $status
   */
  public function log ($status, $ref, $object, $strict = false) {
      // Not strict log any warning or error.
      if ($this->state->get('file_logger_enabled') && (!$strict || $status == "WARNING" || $status == "ERROR")) {
        $realpath = getcwd();
        // If is an array encode it.
        if (is_array($object) || is_object($object)) {
          $object = json_encode($object);
        }
        if ($wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri('public://')) {
          $realpath = $wrapper->realpath();
        }
        $file = $realpath.'/log_'.date("dmY").'.csv';
        // Adds the csv headers.
        if (!file_exists($file)) {
          $current = "TIME/HOUR, LEVEL, MODULE, DATA\n";
          file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
        }
        // Print the last log.
        $file = $realpath.'/log_'.date("dmY").'.csv';
        $current = date('d-m-Y H:i:s'). ',' . $status.','.$ref.",".$object."\n";
        file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
      }
  }
}
