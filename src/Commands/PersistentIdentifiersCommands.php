<?php

namespace Drupal\persistent_identifiers\Commands;

use Drush\Commands\DrushCommands;

/**
 * Drush commandfile.
 */
class PersistentIdentifiersCommands extends DrushCommands {

  public function __construct() {
    $this->module_config = \Drupal::config('persistent_identifiers.settings');
  }

  /**
   * @param int $id
   *   Node ID.
   *
   * @command persistent_identifiers:add-pid
   * @usage persistent_identifiers:add-pid 25
   */
    // public function addPid($id) {
    public function addPid($id = NULL, $options = ['extra' => '']) {
      $entity = \Drupal::entityTypeManager()->getStorage('node')->load($id);
      $minter = \Drupal::service($this->module_config->get('persistent_identifiers_minter'));
      $persister = \Drupal::service($this->module_config->get('persistent_identifiers_persister'));
      $identifier = $minter->mint($entity, $options['extra']);
      $persister->persist($entity, $identifier);
      $this->logger()->notice(
          dt(
              'Node @nid now has the persistent identifier @pid',
              ['@nid' => $id, '@pid' => $identifier]
            )
      );
  }

}
