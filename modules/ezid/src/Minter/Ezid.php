<?php

namespace Drupal\ezid\Minter;

use Psr7\Message;
use Drupal\persistent_identifiers\MinterInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * A Handle class.
 *
 * Mints a persistent identifier using a configurable
 * namespace and a random string.
 */
class Ezid implements MinterInterface {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('EZID ARK Minter');
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
   * Issues a mint request to the EZID service for an ARK.
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
    $password = \Drupal::state()->get('ezid.password');

    $config = \Drupal::config('ezid.settings');
    $ezid_user = $config->get('ezid_user');
    $ezid_api_endpoint = $config->get('ezid_api_endpoint');
    $ezid_shoulder = $config->get('ezid_shoulder');

    $host = \Drupal::request()->getSchemeAndHttpHost();
    $target = $host . $entity->toUrl()->toString();

    // @TODO Add support for Dublin Core and additional fields based on bundle.
    $data = "_profile: erc\n";
    $data = $data . "what: " . $entity->label() . "\n";
    $data = $data . "_target: $target\n";

    $client = \Drupal::httpClient();
    try {
      $request = $client->request(
        'POST',
        $ezid_api_endpoint . '/shoulder/' . $ezid_shoulder,
        [
          'auth' => [$ezid_user, $password],
          'headers' => ['Content-Type' => 'text/plain; charset=UTF-8'],
          'body' => $data,
        ]);
      \Drupal::logger('persistent identifiers')->info(print_r($request, TRUE));
      $message = $request->getBody();
      if (strpos($message, "success: ") === 0) {
        return substr($message, 9);
      }
      \Drupal::logger('persistent identifiers')->error("Could not mint: $message");
      return FALSE;
    }
    catch (RequestException $e) {
      $message = Message::toString($e->getRequest());
      if ($e->hasResponse()) {
        $message = $message . "\n" . Message::toString($e->getResponse());
      }
      \Drupal::logger('persistent identifiers')->error(preg_replace('/Authorization: Basic \w+/', 'Authentication Redacted', $message));
      return FALSE;
    }
  }

}
