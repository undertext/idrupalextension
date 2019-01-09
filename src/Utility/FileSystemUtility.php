<?php

namespace undertext\idrupalextension\Utility;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Utility class for interaction with a file system.
 */
class FileSystemUtility {

  /**
   * Get number of files/directories inside given directory.
   *
   * @param $directory
   *   Directory to look into.
   *
   * @return int
   *   Number of files/directories inside of given directory.
   */
  public static function getFilesCount($directory) {
    $files = scandir($directory);
    return count($files) - 2;
  }

  /**
   * Remove all files/directories from the given directory.
   *
   * @param string $directory
   *   Path to the directory.
   *
   * @return bool
   *   TRUE on success.
   */
  public static function cleanDirectory($directory) {
    $di = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
      $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }
    return TRUE;
  }

  /**
   * Get last updated directory inside given directory.
   *
   * @param $directory
   *   Given directory.
   *
   * @return string|null
   *   Last updated directory or NULL.
   */
  public static function getLastUpdatedDirectory($directory) {
    $files = scandir($directory, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
      if (is_dir($directory . '/' . $file) && !in_array($file, ['.', '..'])) {
        return $file;
      }
    }
    return NULL;
  }

}
