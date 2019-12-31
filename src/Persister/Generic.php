<?php

namespace Drupal\persistent_identifiers\Persister;

use Drupal\Core\Form\FormStateInterface;

class Generic {
  public function __construct() {
    $config = \Drupal::config('persistent_identifiers.settings');
    $this->config = $config;
  }

  /**
   * Returns the persister's name.
   *
   * @return string
   */
  public function getName() {
    return t('Generic persister for text fields');
  }

  /**
   * Persists the identifier to a field in the entity.
   *
   * @param $entity object
   *    The node, etc.
   * @param $pid string
   *    The persistent identifier.
   * @param $save bool
   *    Whether or not to save the entity. If called from a context
   *    where the entity is saved automatically, such as from within
   *    hook_entity_presave(), this should be FALSE.
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
