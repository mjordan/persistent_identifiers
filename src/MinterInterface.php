<?php

namespace Drupal\persistent_identifiers;

/**
 * Defines an interface for Persistent Identifier minters.
 */
interface MinterInterface {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   The name of the minter as it appears in the Persistent
   *   Identifiers config form.
   */
  public function getName();

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType();

  /**
   * Mints the identifier.
   *
   * @param object $entity
   *   The node, etc.
   * @param mixed $extra
   *   Extra data the minter needs, for example from the node edit form state.
   *
   * @return string|null
   *   The identifier or NULL if it is not available/failed/etc.
   */
  public function mint($entity, $extra = NULL);

}
