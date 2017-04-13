<?php
/**
 * Contains \PreventHivOk\Scaffold\FilesInterface.
 *
 * @filesource
 */
declare(strict_types=1);

namespace PreventHivOk\Scaffold;

use Composer\Script\Event;

/**
 * Interface defining the functions needed to scaffold a project.
 */
interface FilesInterface {

  /**
   * Generates the files required for a Drupal 8 project.
   *
   * @param  Event $event
   * @return void
   */
  public static function create(Event $event);

  /**
   * Removes the generated files of a Drupal 8 project.
   *
   * @param  Event $event
   * @return void
   */
  //public function remove(Event $event);

  /**
   * Validates the generated files of a Drupal 8 project.
   *
   * @param  Event $event
   * @return void
   */
  //public function validate(Event $event);
}
