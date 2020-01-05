<?php

namespace Drupal\persistent_identifiers\Minter;

/**
 * Demonstration/sample class.
 *
 * Mints a persistent identifier based on the entity's UUID.
 */
class Uuid {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   The name of the minter as it appears in the Persistent
   *   Identifiers config form.
   */
  public function getName() {
    return t('UUID Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType() {
    return 'UUID-based persistent identifier';
  }

  /**
   * Mints the identifier.
   *
   * @param object $entity
   *   The node, etc.
   * @param mixed $extra
   *   Extra data the minter needs, for example from the node edit form.
   *
   * @return string
   *   The identifier.
   */
  public function mint($entity, $extra = NULL) {
    $base_url = $host = \Drupal::request()->getSchemeAndHttpHost();
    // Note: The URL resulting from this minter does not resolve
    // to the entity. It is for demonstration purposes only.
    return $base_url . '/id/' . $entity->uuid();
  }

}
