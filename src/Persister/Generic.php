<?php

namespace Drupal\persistent_identifiers\Persister;

/**
 * Persists identifier string to a text field.
 */
class Generic {

  /**
   * Constructor.
   */
  public function __construct() {
    $config = \Drupal::config('persistent_identifiers.settings');
    $this->config = $config;
  }

  /**
   * Returns the persister's name.
   *
   * @return string
   *   The name of the persister as it appears in the Persistent
   *   Identifiers config form.
   */
  public function getName() {
    return t('Generic persister for text fields');
  }

  /**
   * Persists the identifier to a field in the entity.
   *
   * @param object $entity
   *   The node, etc.
   * @param string $pid
   *   The persistent identifier.
   * @param bool $save
   *   Whether or not to save the entity. If called from a context
   *   where the entity is saved automatically, such as from within
   *   hook_entity_presave(), this should be FALSE.
   */
  public function persist(&$entity, $pid, $save = TRUE) {
    $target_field = $this->config->get('persistent_identifiers_target_field');
    if ($entity->hasField('field_identifier')) {
      $entity->set('field_identifier', $pid);
    }
    if ($save) {
      $entity->save();
    }
  }

}
