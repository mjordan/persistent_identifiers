<?php

namespace Drupal\hdl\Minter;

use Drupal\persistent_identifiers\MinterInterface;

/**
 * A Handle class.
 *
 * Mints a persistent identifier using a configurable
 * namespace and a random string.
 */
class Hdl implements MinterInterface {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('Handle Minter');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType() {
    return t('Handle');
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
    $config = \Drupal::config('hdl.settings');
    $handle_prefix = $config->get('hdl_prefix');

    if (array_key_exists('hdl_qualifier', $extra)) {
      $handle_type_qualifier = $extra['hdl_qualifier'];
    }
    else {
      $handle_type_qualifier = $config->get('hdl_qualifier');
    }
    $handle = $handle_prefix . '/' . $handle_type_qualifier . '.' . $entity->id();
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $url = $host . $entity->toUrl()->toString();
    $admin_handle = $config->get('hdl_admin_handle');
    $handle_admin_index = $config->get('hdl_admin_index');
    $endpoint_url = $config->get('hdl_handle_api_endpoint');
    $permissions = $config->get('hdl_handle_permissions');
    $password = $config->get('hdl_handle_basic_auth_password');
    $handle_json = [
      [
        'index' => 1,
        'type' => "URL",
        'data' => [
          'format' => "string",
          'value' => $url,
        ],
      ],
      [
        'index' => 100,
        'type' => 'HS_ADMIN',
        'data' => [
          'format' => 'admin',
          'value' => [
            'handle' => $admin_handle,
            'index' => $handle_admin_index,
            'permissions' => $permissions,
          ],
        ],
      ],
    ];

    $client = \Drupal::httpClient();
    try {
      $request = $client->request('PUT', $endpoint_url . $handle . "?overwrite=true", ['json' => $handle_json, 'auth' => [$handle_admin_index . '%3A' . $admin_handle, $password], 'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']]);
      \Drupal::logger('persistent identifiers')->info(print_r($request, TRUE));
    } catch (ClientException $e) {
      \Drupal::logger('persistent identifiers')->error(print_r($e, TRUE));
      return FALSE;
    }
    $full_handle = "https://hdl.handle.net/" . $handle;
    return $full_handle;
  }

}
