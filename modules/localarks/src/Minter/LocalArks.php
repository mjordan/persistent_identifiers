<?php

namespace Drupal\localarks\Minter;

use Drupal\persistent_identifiers\MinterInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * A Minter class.
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
    $localarks_redirector_host = $config->get('localarks_redirector_host');
    $localarks_naan = $config->get('localarks_naan');

    // Should provide 183,579,396 unique combinations.
    $identifier_length = 10;
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $ark_identifier = '';
    for ($i = 0; $i < $identifier_length; $i++) {
        $index = rand(0, strlen($chars) - 1);
        $ark_identifier .= $chars[$index];
    }
    $local_shoulder = $config->get('localarks_local_shoulder');
    if (strlen($local_shoulder)) {
      $ark_identifier = ltrim($local_shoulder, '/') . $ark_identifier;
    }

    // ARK should contain the redirector base URL, so we  prepend it here.
    $ark = trim(rtrim($localarks_redirector_host, '/')) . '/ark:/' . trim($localarks_naan) . '/' . $ark_identifier;
    return $ark;
  }

}
