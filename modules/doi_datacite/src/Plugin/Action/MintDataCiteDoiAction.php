<?php

namespace Drupal\doi_datacite\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Mints DataCite DOIs.
 *
 * @Action(
 *   id = "doi_datacite_mint_doi",
 *   label = @Translation("Mint DataCite DOIs"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class MintDataCiteDoiAction extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface, PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $minter = \Drupal::service('doi_datacite.minter.datacitedois');
    $datacite_metadata_values = $minter->getDataCiteElementValues($entity);

    // @todo: merge values from $datacite_metadata_values with those from this reaction's
    // config by looking for empty members of $datacite_metadata_values and populate them
    // with values from the config. If some are still missing, skip and log. The minter will
    // check for completeness and skip if values are missing.

    $persister_id = \Drupal::config('persistent_identifiers.settings')->get('persistent_identifiers_persister');
    $persister = \Drupal::service($persister_id);
    // The values saved in this action's configuration form are in $this->configuration.
    $doi_identifier = $minter->mint($entity, $this->configuration);
    if (strlen($doi_identifier)) {
      $persister->persist($entity, $doi_identifier);
      $this->messenger()->addMessage('"' . $entity->label() . '" assigned the DOI ' . $doi_identifier);
    }
    else {
      $this->messenger()->addMessage('"' . $entity->label() . '" (node ' . $entity->id() . ') not assigned a DOI. See system log for details.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Configuration form builder.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $minter = \Drupal::service('doi_datacite.minter.datacitedois');
    $resource_type_values = $minter->getResourceTypes();
    $form['doi_datacite_resource_type'] = [
      '#type' => 'radios',
      '#options' => $resource_type_values,
      '#title' => t("DataCite resource type"),
      '#required' => TRUE,
      '#description' => t("Metadata submitted to DataCite requires one of these " .
      "resource types."),
    ];
    $form['doi_datacite_creator'] = [
      '#title' => t('Creator'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t("Separate repeated values with semicolons."),
    ];
    $form['doi_datacite_publication_year'] = [
      '#title' => t('Publication year'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t("Must be in YYYY format. Note that this value is not validated " .
        "so double check it."),
    ];
    $form['doi_datacite_publisher'] = [
      '#title' => t('Publisher'),
      '#type' => 'textfield',
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * Submit handler for the action configuration form.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['doi_datacite_creator'] = $form_state->getValue('doi_datacite_creator');
    $this->configuration['doi_datacite_publication_year'] = $form_state->getValue('doi_datacite_publication_year');
    $this->configuration['doi_datacite_publisher'] = $form_state->getValue('doi_datacite_publisher');
    $this->configuration['doi_datacite_resource_type'] = $form_state->getValue('doi_datacite_resource_type');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('mint persistent identifiers', $account, $return_as_object);
  }
}
