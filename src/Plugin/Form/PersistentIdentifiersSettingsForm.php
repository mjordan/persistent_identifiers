<?php

namespace Drupal\persistent_identifiers\Plugin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin settings form.
 */
class PersistentIdentifiersSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'persistent_identifiers_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'persistent_identifiers.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('persistent_identifiers.settings');

    // For now, we're only interested in nodes.
    $bundle_info = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($bundle_info as $name => $details) {
      $options[$name] = $details['label'];
    }
    $form['persistent_identifiers_bundles'] = [
      '#weight' => -10,
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $config->get('persistent_identifiers_bundles'),
      '#description' => $this->t('Allow persistent identifier minting for the checked content types. You must save this form before DataCite field mappings for the selected content types appear below.'),
      '#title' => $this->t('Content types'),
      '#attributes' => [
        'id' => 'persistent_identifiers_bundles',
      ],
    ];

    $minters = $this->getServices('minter');
    $form['persistent_identifiers_minter'] = [
      '#type' => 'radios',
      '#title' => $this->t('Minter'),
      '#default_value' => $config->get('persistent_identifiers_minter'),
      '#options' => $minters,
      '#attributes' => [
        'id' => 'persistent_identifiers_minter',
      ],
    ];
    $persisters = $this->getServices('persister');
    $form['persistent_identifiers_persister'] = [
      '#type' => 'radios',
      '#title' => $this->t('Persister'),
      '#default_value' => $config->get('persistent_identifiers_persister'),
      '#options' => $persisters,
      '#weight' => 99,
      '#attributes' => [
        'id' => 'persistent_identifiers_persister',
      ],
    ];
    $form['persistent_identifiers_target_field'] = [
      '#type' => 'textfield',
      '#maxlength' => 256,
      '#title' => $this->t('Target field'),
      '#weight' => 99,
      '#description' => $this->t('Machine name of field where persistent ID should be ' .
      'stored. All node content types that will be getting persistent identifiers must have this field. ' .
      'See the <a href="@url">docs</a> for more information.',
      ['@url' => 'https://islandora.github.io/documentation/islandora/rdf-mapping/']),
      '#default_value' => $config->get('persistent_identifiers_target_field'),
    ];
    $form['persistent_identifiers_map_to_schema_sameas'] = [
      '#weight' => 100,
      '#type' => 'checkbox',
      '#default_value' => $config->get('persistent_identifiers_map_to_schema_sameas'),
      '#description' => $this->t("Add the persistent identifier to the node's JSON-LD as schema:sameAs, prepending the URL configured below. If the field is multivalued, uses the first value."),
      '#title' => $this->t('Map to schema:sameAs in JSON-LD'),
      '#attributes' => [
        'id' => 'persistent_identifiers_map_to_schema_sameas',
      ],
    ];
    $form['persistent_identifiers_resolver_base_url'] = [
      '#type' => 'textfield',
      '#maxlength' => 256,
      '#title' => $this->t('Resolver base URL'),
      '#weight' => 100,
      '#description' => $this->t('URL to prepend to the persistent identifier. Leave empty to not prepend anything'),
      '#default_value' => $config->get('persistent_identifiers_resolver_base_url'),
      '#states' => [
        'visible' => [
          ':input[id="persistent_identifiers_map_to_schema_sameas"]' => ['checked' => TRUE],
        ],
      ],
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('persistent_identifiers.settings')
      ->set('persistent_identifiers_minter', $form_state->getValue('persistent_identifiers_minter'))
      ->set('persistent_identifiers_persister', $form_state->getValue('persistent_identifiers_persister'))
      ->set('persistent_identifiers_target_field', trim($form_state->getValue('persistent_identifiers_target_field')))
      ->set('persistent_identifiers_bundles', array_values($form_state->getValue('persistent_identifiers_bundles')))
      ->set('persistent_identifiers_map_to_schema_sameas', $form_state->getValue('persistent_identifiers_map_to_schema_sameas'))
      ->set('persistent_identifiers_resolver_base_url', $form_state->getValue('persistent_identifiers_resolver_base_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Gets a list of services that can be used in the admin settings form.
   *
   * @param string $type
   *   Either 'minter' or 'persister'.
   *
   * @return array
   *   Associative array of services filtered by $type.
   */
  public function getServices($type) {
    $container = \Drupal::getContainer();
    $services = $container->getServiceIds();
    $services = preg_grep("/\.$type\./", $services);
    $options = [];
    foreach ($services as $service_id) {
      $service = \Drupal::service($service_id);
      $options[$service_id] = $service->getName();
    }
    return $options;
  }

}
