<?php

namespace Drupal\file_logger;

use Drupal\Core\State\State;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FileLoggerService.
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
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */

  protected $streamWrapper;

  /**
   * Spor service configuration constructor.
   *
   * @param \Drupal\Core\State\State $state
   *   The state.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $streamWrapper
   *   The stream to get the current URI.
   */
  public function __construct(State $state, StreamWrapperManager $streamWrapper) {
    $this->state = $state;
    $this->streamWrapper = $streamWrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('state'),
      $container->get('stream_wrapper_manager'),
    );
  }

  /**
   * Returns a log string.
   */
  public function log($status, $ref, $object, $suffix = "", $strict = FALSE) {
    // Not strict log any warning or error.
    if ($this->state->get('file_logger_enabled') && (!$strict || $status == "WARNING" || $status == "ERROR")) {
      $realpath = getcwd();
      // If is an array encode it.
      if (is_array($object) || is_object($object)) {
        $object = json_encode($object);
      }
      if ($wrapper = $this->streamWrapperManager->getViaUri('public://')) {
        $realpath = $wrapper->realpath();
      }
      $file = $realpath . '/log_' . date("dmY") . '_' . $suffix . '.csv';
      // Adds the csv headers the very first time.
      if (!file_exists($file)) {
        $current = "TIME/HOUR, LEVEL, MODULE, DATA\n";
        file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
        return;
      }
      // Print the last log.
      $current = date('d-m-Y H:i:s') . ',' . $status . ',' . $ref . "," . $object . "\n";
      file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
    }
  }

}
