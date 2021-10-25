<?php

namespace Drupal\file_logger;

/**
 * FileLoggerService
 *
 * Usage:
 * $our_service = \Drupal::service('file_logger.service');
 * $our_service->log("ERROR", $message);
 */
class FileLoggerService {

  /**
   * Returns a log string
   * @return string $status
   */
  public function log ($status, $ref, $string, $strict = false) {
    // Not strict doesn't log any warning or error.
    if ($strict || $status == "WARNING" || $status == "ERROR") {
        // Locates the file.
        $file = getcwd().'/log_'.date("dmY").'.csv';
        // Adds the csv headers.
        if (!file_exists($file)) {
            $file = getcwd().'/log_'.date("dmY").'.csv';
            $current = "TIME/HOUR, LEVEL, OPERATION, DATA\n";
            file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
        }
        // Print the last log.
        $file = getcwd().'/log_'.date("dmY").'.csv';
        $current = date('d-m-Y H:i:s'). ',' . $status.','.$ref.','. $string."\n";
        file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
    }
  }

}
