<?php

namespace Drupal\sample_minter\Minter;

class Sample {

  /**
   * Returns the minter's name.
   *
   * @return string
   */
  function getName() {
    return t('Sample Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   */
  function getPidType() {
    return 'Sample';
  }

  /**
   * Mints the identifier.
   *
   * This sample minter simply returns a random string, but this method
   * is where you would request a new DOI, ARK, etc.
   * @return string
   */
  function mint($entity) {
    $config = \Drupal::config('persistent_identifiers.settings');
    $namespace = $config->get('sample_minter_namespace');
    return $namespace . rand(100, 10000);
  }

}
