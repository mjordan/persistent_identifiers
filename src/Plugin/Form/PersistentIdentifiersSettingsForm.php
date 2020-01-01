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
    $form['persistent_identifiers_minter'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Minter'),
      '#default_value' => $config->get('persistent_identifiers_minter'),
      '#options' => $minters,
      '#attributes' => [
        'id' => 'persistent_identifiers_minter',
      ],
    );
    $persisters = $this->getServices('persister');
    $form['persistent_identifiers_persister'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Persister'),
      '#default_value' => $config->get('persistent_identifiers_persister'),
      '#options' => $persisters,
      '#weight' => 100,
      '#attributes' => [
        'id' => 'persistent_identifiers_persister',
      ],
    );
    $form['persistent_identifiers_target_field'] = array(
      '#type' => 'textfield',
      '#maxlength' => 256,
      '#title' => $this->t('Target field'),
      '#weight' => 100,
      '#description' => $this->t('Machine name of field where persistent ID should be stored.'),
      '#default_value' => $config->get('persistent_identifiers_target_field'),
    );

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
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Gets a list of services that can be used in the admin settings form.
   *
   * @param string $type
   *    Either 'minter' or 'persister'.
   *
   * @return array
   *    Associative array of services filtered by $type.
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

