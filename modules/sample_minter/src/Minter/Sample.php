<?php

namespace Drupal\sample_minter\Minter;

class Sample {

  /**
   * Returns the minter's name.
   *
   * @return string
   */
  public function getName() {
    return t('Sample Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   */
  public function getPidType() {
    return 'Sample';
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
   * @return string
   */
  public function mint($entity) {
    $config = \Drupal::config('sample_minter.settings');
    $namespace = $config->get('sample_minter_namespace');
    return $namespace . rand(100, 10000);
  }

}
