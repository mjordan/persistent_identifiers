<?php

namespace Drupal\localarks\Minter;

use Drupal\persistent_identifiers\MinterInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * A Handle class.
 *
 * Mints a persistent identifier using a configurable
 * namespace and a random string.
 */
class LocalArks implements MinterInterface {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('Local Ark Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType() {
    return t('ARK');
  }

  /**
   * Mints the identifier.
   *
   * @param object $entity
   *   The entity.
   * @param mixed $extra
   *   Extra data the minter needs, for example from the node edit form.
   *
   * @return string|NULL
   *   The identifier, or NULL on failure.
   */
  public function mint($entity, $extra = NULL) {
    $config = \Drupal::config('localarks.settings');

    $base_url = $host = \Drupal::request()->getSchemeAndHttpHost();
    return $base_url . '/id/' . $entity->uuid();

  }

}
