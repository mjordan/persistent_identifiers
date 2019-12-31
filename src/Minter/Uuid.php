<?php

namespace Drupal\persistent_identifiers\Minter;

class Uuid {

  /**
   * Returns the minter's name.
   *
   * @return string
   */
  function getName() {
    return t('UUID Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   */
  function getPidType() {
    return 'UUID';
  }

  /**
   * Mints the identifier.
   *
   * @return string
   */
  function mint($entity) {
    $base_url = $host = \Drupal::request()->getSchemeAndHttpHost();
    // Note: The URL resulting from this minter does not resolve
    // to the entity. It is for demonstration purposes only.
    return $base_url . '/id/' . $entity->uuid();
  }

}

