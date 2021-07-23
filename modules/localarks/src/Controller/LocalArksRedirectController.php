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

    $response = new RedirectResponse('https://www.lib.sfu.ca');
    $response->send();
    return $response;


    $config = \Drupal::config('redirect_from_identifier.settings');
    $fields = preg_split("/\\r\\n|\\r|\\n/", $config->get('redirect_from_identifier_target_fields'));

    // An array of associative arrays, each with version ID => node ID.
    $ids = [];
    foreach ($fields as $field_name) {
      $query = \Drupal::entityQuery('node');
      $query->condition(trim($field_name), trim($identifier), '=');
      $results = $query->execute();
      if (count($results) == 0) {
        continue;
      }
      $ids = array_merge($ids, array_values($results));
    }

    return $ids;

  }

}
