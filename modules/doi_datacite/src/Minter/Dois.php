<?php

namespace Drupal\doi_datacite\Minter;

use Drupal\persistent_identifiers\MinterInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

/**
 * DataCite DOI minter.
 */
class Dois implements MinterInterface {

  /**
   * Constructor.
   */
  public function __construct() {
    $config = \Drupal::config('doi_datacite.settings');
    $this->api_endpoint = $config->get('doi_datacite_api_endpoint');
    $this->doi_prefix = $config->get('doi_datacite_prefix');
    $this->doi_suffix_source = $config->get('doi_datacite_suffix_source');
    $this->api_username = $config->get('doi_datacite_username');
    $this->api_password = $config->get('doi_datacite_password');
  }

  /**
   *
   */
  public function getResourceTypes() {
    return [
      'Audiovisual' => 'Audiovisual',
      'Collection' => 'Collection',
      'Dataset' => 'Dataset',
      'Event' => 'Event',
      'Image' => 'Image',
      'InteractiveResource' => 'InteractiveResource',
      'Model' => 'Model',
      'PhysicalObject' => 'PhysicalObject',
      'Service' => 'Service',
      'Software' => 'Software',
      'Sound' => 'Sound',
      'Text' => 'Text',
      'Workflow' => 'Workflow',
      'Other' => 'Other',
    ];
  }

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('DataCite DOI');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType() {
    return t('DataCite DOI');
  }

  /**
   * Mints the identifier.
   *
   * @param object $entity
   *   The node, etc.
   * @param mixed $extra
   *   The node edit form state or data from the Views Bulk Operations action.
   *
   * @return string|NULL;
   *   The DOI that will be saved in the persister's designated field.
   *   NULL if there was an error POSTing to the API.
   */
  public function mint($entity, $extra = NULL) {
    global $base_url;
    // This minter needs $extra.
    if (is_null($extra)) {
      return NULL;
    }

    if ($this->doi_suffix_source == 'id') {
      $suffix = $entity->id();
    }
    if ($this->doi_suffix_source == 'uuid') {
      $suffix = $entity->Uuid();
    }
    $doi = $this->doi_prefix . '/' . $suffix;

    // If $extra is from the Views Bulk Operations Action
    // (i.e., it's an array).
    if (is_array($extra)) {
      $datacite_array = [];
      $creators = explode(';', $extra['doi_datacite_creator']);
      $datacite_creators = [];
      foreach ($creators as $creator) {
        $datacite_creators[] = ['name' => trim($creator)]; 
      }
      $datacite_titles = [];
      $datacite_titles[] = ['title' => $entity->title->value];
      $datacite_array['data']['type'] = 'dois';
      $attributes = [
            'event' => 'publish',
            'creators' => $datacite_creators,
            'titles' => $datacite_titles,
            'publisher' => $extra['doi_datacite_publisher'],
            'publicationYear' => $extra['doi_datacite_publication_year'],
            'types' => ['resourceTypeGeneral' => $extra['doi_datacite_resource_type']],
            'url' => $base_url . '/node/' . $entity->id(),
            'schemaVersion' => 'http://datacite.org/schema/kernel-4',
      ];
      $datacite_array['data']['attributes'] = $attributes;
    }

    // If $extra is from the node edit form (i.e., it's an instance of
    // Drupal\Core\Form\FormState).
    if (is_object($extra) && method_exists($extra, 'getValue')) {

      //
      // @todo: get bundle IDs from config, append them to field names.
      //
      $datacite_array = [];
      $creators = explode(';', $extra->getValue('doi_datacite_creator'));
      $datacite_creators = [];
      foreach ($creators as $creator) {
        $datacite_creators[] = ['name' => trim($creator)]; 
      }
      $datacite_titles = [];
      $datacite_titles[] = ['title' => $entity->title->value];
      $datacite_array['data']['type'] = 'dois';
      $attributes = [
            'event' => 'publish',
            'creators' => $datacite_creators,
            'titles' => $datacite_titles,
            'publisher' => $extra->getValue('doi_datacite_publisher'),
            'publicationYear' => $extra->getValue('doi_datacite_publication_year'),
            'types' => ['resourceTypeGeneral' => $extra->getValue('doi_datacite_resource_type')],
            'url' => $base_url . '/node/' . $entity->id(),
            'schemaVersion' => 'http://datacite.org/schema/kernel-4',
      ];
      $datacite_array['data']['attributes'] = $attributes;
    }

    if ($this->doi_suffix_source == 'auto') {
      $datacite_array['data']['attributes']['prefix'] = $this->doi_prefix;
    }
    else {
      $datacite_array['data']['id'] = $doi;
      $datacite_array['data']['attributes']['doi'] = $doi;
    }

    $datacite_json = json_encode($datacite_array);

    // @todo: Define a hook here so people can write modules to alter the JSON.

    $minted_doi = $this->postToApi($entity->id(), $datacite_json);
    return $minted_doi;
  }


  /**
   * POSTs to DataCite REST API to create and publish the DOI.
   *
   * @param int $nid
   *   The node ID.
   * @param string $datacite_json
   *   The DataCite JSON. See https://support.datacite.org/docs/api-create-dois
   *   for more information.
   *
   * @return string|NULL
   *   The DOI string if successful, NULL if not.
   */
  public function postToApi($nid, $datacite_json) {
    $response = \Drupal::httpClient()->post($this->api_endpoint, [
        'auth' => [$this->api_username, $this->api_password],
        'body' => $datacite_json,
        'http_errors' => FALSE,
        'headers' => [
           'Content-Type' => 'application/vnd.api+json',
        ],
    ]);

    $http_code = $response->getStatusCode();
    // DataCite's API returns a 201 if the request was successful.
    if ($http_code == 201) {
      $response_body_array = json_decode($response->getBody()->getContents(), TRUE);
      $doi = $response_body_array['data']['attributes']['doi'];
      return $doi;
    }
    else {
      switch ($http_code) {
        case 404:
	  $hint = ' Hint: Check your repository ID, DOI prefix, and/or password.';
	  break;
        case 400:
	  $hint = ' Hint: The JSON being sent to DataCite may be invalid (' . $datacite_json . ').';
	  break;
        case 422:
	  $hint = '';
	  break;
        default:
	  $hint = '';
      }

      \Drupal::logger('doi_datacite')->warning(t("While minting DOI for node @nid, the DataCite API returned @code status with the message '@message' @hint", [
        '@nid' => $nid,
        '@code' => $http_code,
        '@message' => $response->getBody()->getContents(),
        '@hint' => $hint,
      ]));

      return NULL;
    }
  }

  /**
   * Attempts to prepopulate DataCite-specific metadata fields.
   *
   * @param int $nid
   *   The node ID.
   *
   * @return array
   *   An associative array of metadata values that can be gotten from
   *   existing node fields, to provide default values for the required DataCite metadata.
   */
  public function getDataCiteElementValues($nid) {
    $config = \Drupal::config('doi_datacite.settings');
    $mappings = preg_split("/\\r\\n|\\r|\\n/", $config->get('doi_datacite_field_mappings'));
    $datacite_values = [
      'creators' => '',
      'publicationYear' => '',
      'publisher' => '',
    ];

    if (empty($nid)) {
      return $datacite_values;
    }

    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    // Creators.
    $creators_field_name = $config->get('doi_datacite_creators_mapping');
    if ($node->hasField($creators_field_name)) {
      $node_creators_field_values = $node->get($creators_field_name)->getValue();
      if (array_key_exists('value', $node_creators_field_values[0])) {
        $datacite_values['creators'] = $node_creators_field_values[0]['value'];
      }
    }
    // Publisher.
    $publisher_field_name = $config->get('doi_datacite_publisher_mapping');
    if ($node->hasField($publisher_field_name)) {
      $node_publisher_field_values = $node->get($publisher_field_name)->getValue();
      if (array_key_exists('value', $node_publisher_field_values[0])) {
        $datacite_values['publisher'] = $node_publisher_field_values[0]['value'];
      }
    }
    // Publication year.
    $publication_year_field_name = $config->get('doi_datacite_publicationyear_mapping');
    if ($node->hasField($publication_year_field_name)) {
      $node_publication_year_field_values = $node->get($publication_year_field_name)->getValue();
      if (array_key_exists('value', $node_publication_year_field_values[0])) {
        $datacite_values['publicationYear'] = $node_publication_year_field_values[0]['value'];
      }
    }

    return $datacite_values;
  }

}
