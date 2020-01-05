<?php

namespace Drupal\persistent_identifiers;

/**
 * Persists identifier string to a text field.
 */
interface PersisterInterface {

  /**
   * Returns the persister's name.
   *
   * @return string
   *   The name of the persister as it appears in the Persistent
   *   Identifiers config form.
   */
  public function getName();

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
   *
   * @return bool
   *   TRUE if persisted, FALSE if not.
   */
  public function persist(&$entity, $pid, $save = TRUE);

}
