<?php

namespace Drupal\persistent_identifiers\Persister;

use Drupal\persistent_identifiers\PersisterInterface;

/**
 * Persists identifier string to a text field.
 */
class Generic implements PersisterInterface {

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
   *
   * @return bool
   *   TRUE if persisted, FALSE if not. @todo: Check for failure to assign
   *   field value and if fails, return FALSE.
   */
  public function persist(&$entity, $pid, $save = TRUE) {
    $target_field = trim($this->config->get('persistent_identifiers_target_field'));
    if (method_exists($entity, 'hasField') && $entity->hasField($target_field)) {
      // TODO: Don't add same values
      $entity->{$target_field}[] = $pid;
    }
    else {
        \Drupal::messenger()->addMessage(t('This node does not have the required field (@field)', ['@field' => $target_field]));
    }
    if ($save) {
      $entity->save();
    }
    return TRUE;
  }

}
