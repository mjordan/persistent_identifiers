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
   * @command persistent_identifiers:add_pid
   * @usage persistent_identifiers:add_pid 25
   */
    public function add_pid($id) {
      $entity = \Drupal::entityTypeManager()->getStorage('node')->load($id);
      $minter = \Drupal::service($this->module_config->get('persistent_identifiers_minter'));
      $persister = \Drupal::service($this->module_config->get('persistent_identifiers_persister'));
      $identifier = $minter->mint($entity);
      $persister->persist($entity, $identifier);

      $this->logger()->notice(
          dt(
              'Node @nid now has the persistent identifier @pid',
              ['@nid' => $id, '@pid' => $identifier]
            )
      );
  }

  /**
   * Testing twig templates in Drush cause I'm lazy.
   *
   * Using this technique, we can have minters build XML for posting to
   * DataCite, etc. Templates can be strings (and hence stored in config)
   * instead of files. Would be useful to allow admins to use tokens in
   * their templates.
   *
   * @command persistent_identifiers:twig_test
   * @usage persistent_identifiers:twig_test
   */
   public function twig_test() {
     $template = 'Hello {{ name }}';
     $output = \Drupal::service('twig')
            ->createTemplate($template)
            ->render(['name' => 'Joe']);

      $this->logger()->notice(
          dt(
              '@output',
              ['@output' => $output]
            )
      );
   }

}
