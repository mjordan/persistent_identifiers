<?php

namespace Drupal\doi_datacite\Plugin\ContextReaction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Mints and persists a DOI for a node.
 *
 * @ContextReaction(
 *   id = "datacite_doi_mint_doi",
 *   label = @Translation("Mint DataCite DOI")
 * )
 */
class DoiDataciteMintDoi extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'mint_datacite_doi' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Mint a DataCite DOI for a node.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(EntityInterface $entity = NULL) {
    $config = $this->getConfiguration();
    $pid_config = \Drupal::config('persistent_identifiers.settings');
    $config = \Drupal::config('doi_datacite.settings');

    //  Check for the existence of a DOI. If present, log and return.
    $doi_prefix = $config->get('doi_datacite_prefix');
    $pid_config = \Drupal::config('persistent_identifiers.settings');
    $doi_field_name = $pid_config->get('persistent_identifiers_target_field');
    if ($entity->hasField($doi_field_name)) {
      $doi_field_values = $entity->get($doi_field_name)->getValue();
      if (array_key_exists('value', $doi_field_values[0])) {
        if (preg_match('/' . $doi_prefix . '\//', $doi_field_values[0]['value'])) {
          \Drupal::logger('doi_datacite')->info(t("Node \"@title\" (UUID @uuid) already has a DOI (@doi), Context Reaction not adding one.", ['@title' => $entity->get('title')->value, '@uuid' => $entity->uuid(), '@doi' => $doi_field_values[0]['value']]));
          return;
        }
      }
    }
    
    $minter = \Drupal::service('doi_datacite.minter.datacitedois');

    // If any of the required metadata fields are empty, log and return.
    $datacite_metadata_values = $minter->getDataCiteElementValues($node);
    $missing_properties = [];
    foreach ($datacite_metadata_values as $key => $value) {
      if (strlen($value) == 0) {
        $missing_properties[] = $key;
      }
    }
    if (count($missing_properties) > 0) {
      $keys = implode(', ', $missing_properties);
      \Drupal::logger('doi_datacite')->info(t("Cannot mint DOI for node \"@title\" (UUID @uuid) due to missing required metadata propert(ies) @keys.", ['@title' => $entity->get('title')->value, '@uuid' => $entity->uuid(), '@keys' => $keys]));
      return;
    }

    // $persister = \Drupal::service($module_config->get('persistent_identifiers_persister'));
    // $identifier = $minter->mint($entity);
    // $persister->persist($entity, $identifier, FALSE);
    // This context is fired in presave, so there is no entity ID to log.
    \Drupal::logger('doi_datacite')->info(t("DOI %pid minted for \"@title\" (UUID @uuid) via Context Reaction.", ['%pid' => $pid, '@title' => $entity->get('title')->value, '@uuid' => $entity->uuid()]));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    // DataCite requires the use of this following controlled list of resource types.
    $minter = \Drupal::service('doi_datacite.minter.datacitedois');
    $resource_type_values = $minter->getResourceTypes();

    $form['required_note'] = [
      '#markup' => t('If a node has a field that maps to the following required DataCite metadata elements, its value will be used. If it doesn\'t the value below will be used.'),
    ];
    $form['doi_datacite_resource_type'] = [
      '#type' => 'radios',
      '#options' => $resource_type_values,
      '#title' => $this->t("DataCite resource type"),
      '#default_value' => isset($config['doi_datacite_resource_type']) ? $config['doi_datacite_resource_type'] : 'Text',
    ];
    $form['doi_datacite_creator'] = [
      '#type' => 'textfield',
      '#title' => t('Creator'),
      '#description' => t("Separate repeated values with semicolons."),
      '#default_value' => isset($config['doi_datacite_creator']) ? $config['doi_datacite_creator'] : '',
    ];
    $form['doi_datacite_publication_year'] = [
      '#title' => t('Publication year'),
      '#type' => 'textfield',
      '#description' => t("Must be in YYYY format."),
      '#default_value' => isset($config['doi_datacite_publication_year']) ? $config['doi_datacite_publication_year'] : '',
    ];
    $form['doi_datacite_publisher'] = [
      '#title' => t('Publisher'),
      '#type' => 'textfield',
      '#default_value' => isset($config['doi_datacite_publisher']) ? $config['doi_datacite_publisher'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'doi_datacite_resource_type' => $form_state->getValue('doi_datacite_resource_type'),
      'doi_datacite_creator' => trim($form_state->getValue('doi_datacite_creator')),
      'doi_datacite_publication_year' => trim($form_state->getValue('doi_datacite_publication_year')),
      'doi_datacite_publisher' => trim($form_state->getValue('doi_datacite_publisher')),
    ]);
  }

}
