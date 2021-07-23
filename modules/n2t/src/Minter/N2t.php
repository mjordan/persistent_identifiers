<?php

namespace Drupal\n2t\Minter;

use Drupal\persistent_identifiers\MinterInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * A Handle class.
 *
 * Mints a persistent identifier using a configurable
 * namespace and a random string.
 */
class N2t implements MinterInterface {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('N2T ARK Minter');
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
   * Issues a mint request to the N2T service.
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
    $config = \Drupal::config('n2t.settings');
    $n2t_user = $config->get('n2t_user');
    $n2t_password = $config->get('n2t_password');
    $n2t_api_endpoint = $config->get('n2t_api_endpoint');
    $n2t_shoulder = $config->get('n2t_shoulder');
    $n2t_local_branding_resolver = $config->get('n2t_local_branding_resolver');
    $n2t_identifier_metadata = $config->get('n2t_identifier_metadata');

    // First we mint the ARK.
    $minting_url = rtrim($n2t_api_endpoint, '/') . '/a/' . $n2t_user . '/m/ark/' . $n2t_shoulder . '?mint%201';
    $client = \Drupal::httpClient();
    try {
      $request = $client->request(
        'GET',
        $minting_url,
	['auth' => [$n2t_user, $n2t_password]]
      );
      $response_message = (string) $request->getBody();
      $parts = preg_split("/\\r\\n|\\r|\\n/", $response_message);
      if (strpos($parts[0], "s: ") === 0) {
        $ark = substr($parts[0], 3);
      }
    }
    catch (RequestException $e) {
      $message = "Minting response: " . (string) $request->getBody() . " Exception message: " . $e->getMessage();
      \Drupal::logger('persistent_identifiers')->error(preg_replace('/Authorization: Basic \w+/', 'Authentication Redacted', $message));
      return NULL;
    }

    // Then we bind it to the current node. Optionally, we POST the basic identifier metadata.
    $node_host = \Drupal::request()->getSchemeAndHttpHost();
    $node_url = $node_host . $entity->toUrl()->toString();
    $client = \Drupal::httpClient();
    if ($n2t_identifier_metadata) {
      // POST basic identifier metadata plus ARK.
      $binding_url = rtrim($n2t_api_endpoint, '/') . '/a/' . $n2t_user . '/b?-';
      $identifier_metadata = $this->get_identifier_metadata($entity->label(), $ark, $node_url);
      try {
        $request = $client->request(
          'POST',
          $binding_url,
	  [
            'auth' => [$n2t_user, $n2t_password],
            'headers' => ['Content-Type' => 'text/plain; charset=UTF-8'],
            'body' => $identifier_metadata
          ]
	);
        $response_message = (string) $request->getBody();
        if ($request->getStatusCode() == 200 & preg_match('/^egg-status: 0\n/', $response_message)) {
          if (strlen($n2t_local_branding_resolver) > 0 && preg_match('/^http/', $n2t_local_branding_resolver)) {
            $ret = rtrim($n2t_local_branding_resolver, '/') . '/ark:/' . $ark;
	  }
	  else {
	    $ret = rtrim($n2t_api_endpoint, '/') . '/ark:/' . $ark;
	  }
          return $ret;
        }
        else {
          \Drupal::logger('persistent_identifiers')->error('Could not bind ARK; ARK resolver response (HTTP response code ' . $request->getStatusCode() . ': ' . $response_message);
          return NULL;
        }
      }
      catch (RequestException $e) {
        $message = "Binding response: " . (string) $request->getBody() . " Exception message: " . $e->getMessage();
        \Drupal::logger('persistent_identifiers')->error(preg_replace('/Authorization: Basic \w+/', 'Authentication Redacted', $message));
        return NULL;
      }
    }
    else {
      // Don't POST basic identifier metadata, just bind the ARK.
      $binding_url = rtrim($n2t_api_endpoint, '/') . '/a/' . $n2t_user . '/b?ark:/' . $ark . '.set%20_t%20' . urlencode($node_url);
      try {
        $request = $client->request(
          'GET',
          $binding_url,
	  ['auth' => [$n2t_user, $n2t_password]]
	);
        $response_message = (string) $request->getBody();
        if ($request->getStatusCode() == 200 & preg_match('/^egg-status: 0\n/', $response_message)) {
          if (strlen($n2t_local_branding_resolver) > 0 && preg_match('/^http/', $n2t_local_branding_resolver)) {
            $ret = rtrim($n2t_local_branding_resolver, '/') . '/ark:/' . $ark;
	  }
	  else {
	    $ret = rtrim($n2t_api_endpoint, '/') . '/ark:/' . $ark;
	  }
          return $ret;
        }
        else {
          \Drupal::logger('persistent_identifiers')->error('Could not bind ARK; ARK resolver response (HTTP response code ' . $response->getStatusCode() . ': ' . $response_message);
          return NULL;
        }
      }
      catch (RequestException $e) {
        $message = "Binding response: " . (string) $request->getBody() . " Exception message: " . $e->getMessage();
        \Drupal::logger('persistent_identifiers')->error(preg_replace('/Authorization: Basic \w+/', 'Authentication Redacted', $message));
        return NULL;
      }
    }
  }

  /**
   * Assembles the basic identifer metadata for posting to the ARK resolver.
   *
   * @param string $title
   *   The node's title.
   * @param string $ark
   *   The node's ARK.
   * @param string $node_url
   *   The node's URL (where the ARK resolves to).
   *
   * @return string
   *   The metadata, suitable for POSTing as the body of the request.
   */
  public function get_identifier_metadata($title, $ark, $node_url) {
    $ark = 'ark:/' . $ark;
    $data = $ark . '.set _t ' . $node_url . "\n";
    $data = $data . $ark . '.set what "' . addslashes($title) . '"' . "\n";
    $data = $data . $ark . ".set when (:tba)\n";
    $data = $data . $ark . ".set who (:tba)\n";
    $data = $data . $ark . '.set how "(:mtype oba)"' . "\n";
    return $data;
  }

}
