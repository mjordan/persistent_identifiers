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
      '#weight' => 100,
      '#attributes' => [
        'id' => 'persistent_identifiers_persister',
      ],
    ];
    $form['persistent_identifiers_target_field'] = [
      '#type' => 'textfield',
      '#maxlength' => 256,
      '#title' => $this->t('Target field'),
      '#weight' => 100,
      '#description' => $this->t('Machine name of field where persistent ID should be ' .
      'stored. You should make sure this field is mapped to "owl:sameAs" in your ' .
      'Islandora site\'s RDF mappings. See the <a href="@url">docs</a> for more information.',
      ['@url' => 'https://islandora.github.io/documentation/islandora/rdf-mapping/']),
      '#default_value' => $config->get('persistent_identifiers_target_field'),
    ];

    // For now, we're only interested in nodes.
    $bundle_info = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($bundle_info as $name => $details) {
      $options[$name] = $details['label'];
    }
    $form['persistent_identifiers_bundles'] = [
      '#weight' => 100,
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $config->get('persistent_identifiers_bundles'),
      '#description' => $this->t('Allow persistent identifier minting for the checked content types.'),
      '#title' => $this->t('Content types'),
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
