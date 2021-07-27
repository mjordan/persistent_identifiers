<?php

namespace Drupal\localarks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller.
 */
class LocalArksRedirectController extends ControllerBase {

  /**
   * Redirect the user to the appropriate node.
   *
   * @param string $identifier
   *    The identifier to look up.
   *
   * @return object|string
   *    If an entity is found, redirect to it. If nothing found,
   *    throw a 404 response.
   */
  public function main() {
    $naan = \Drupal::routeMatch()->getRawParameter('naan');
    $idstring = \Drupal::routeMatch()->getRawParameter('idstring');
    // Minter prepends the redirector base URL when it mints the ARK, so we need to also prepend it here for the query.
    $config = \Drupal::config('localarks.settings');
    $localarks_redirector_host = $config->get('localarks_redirector_host');
    $ark = rtrim($localarks_redirector_host, '/') . '/ark:/' . $naan . '/' . $idstring;

    $node_host = \Drupal::request()->getSchemeAndHttpHost();

    $localarks_shoulder_mappings = trim($config->get('localarks_shoulders'));
    $shoulder_mappings_list = preg_split('/\n/', $localarks_shoulder_mappings);
    $shoulder_mappings = []; 
    if (count($shoulder_mappings_list) > 0) {
      $shoulder_mappings = []; 
      foreach ($shoulder_mappings_list as $entry) {
        $parts = explode(',', $entry);
        $shoulder_mappings[$parts[0]] = $parts[1];
      }
    }

    $persister_target_field = \Drupal::config('persistent_identifiers.settings')->get('persistent_identifiers_target_field');

    $node_query = \Drupal::entityQuery('node');
    $node_query->condition(trim($persister_target_field), trim($ark), '=');
    $results = $node_query->execute();
    if (count($results) == 0) {
      throw new NotFoundHttpException();
    }
    else {
      // For now, take the first node found (@todo: account for multiple results, maybe like Redirect From Identifier does it).
      $first_result = array_shift($results);
      // Check to see if the incoming ARK contains a registered shoulder.
      if (count($shoulder_mappings) > 0) {
        // Need to remove the configured redirect host from the ARK.
	$ark_without_redirector_host = preg_replace('#^.*ark:#', '', $ark);
	foreach ($shoulder_mappings as $shoulder => $host) {
	  $id_string = preg_replace('#^./' . $naan . '#', '', $ark_without_redirector_host);
          // To prevent infinite redirects.
          if ($host != $node_host) {
            if (str_starts_with($id_string, trim($shoulder))) {
              $node_url_at_shouldered_host = rtrim($host, '/') . '/ark:' . $ark_without_redirector_host;
              $response = new RedirectResponse($node_url_at_shouldered_host);
              $response->send();
              if ($config->get('localarks_log_redirects')) {
                \Drupal::logger('persistent_identifiers')->info(t("ARK @ark redirected to shouldered target @node_url.", ['@ark' => $ark, '@node_url' => $node_url_at_shouldered_host]));
              }
              return $response;
            }
          }
        }
      }

      // $node_host = \Drupal::request()->getSchemeAndHttpHost();
      $node_url = $node_host . '/node/' . $first_result;
      $response = new RedirectResponse($node_url);
      $response->send();
      if ($config->get('localarks_log_redirects')) {
        \Drupal::logger('persistent_identifiers')->info(t("ARK @ark redirected to @node_url.", ['@ark' => $ark, '@node_url' => $node_url]));
      }
      return $response;
    }
  }

}
