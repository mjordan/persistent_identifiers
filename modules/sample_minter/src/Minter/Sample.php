<?php

namespace Drupal\sample_minter\Minter;

use Drupal\persistent_identifiers\MinterInterface;

/**
 * Demonstration/sample class.
 *
 * Mints a persistent identifier using a configurable
 * namespace and a random string.
 */
class Sample implements MinterInterface {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('Sample Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType() {
    return t('Sample Identifier');
  }

  /**
   * Mints the identifier.
   *
   * This sample minter simply returns a random string prepended by
   * a namespace, but this method is where you would request a new
   * DOI, ARK, etc.
   *
   * @param object $entity
   *   The entity.
   * @param mixed $extra
   *   Extra data the minter needs, for example from the node edit form.
   *
   * @return string
   *   The identifier.
   */
  public function mint($entity, $extra = NULL) {
    $config = \Drupal::config('sample_minter.settings');
    $namespace = $config->get('sample_minter_namespace');
    return $namespace . rand(100, 10000);
  }

}
