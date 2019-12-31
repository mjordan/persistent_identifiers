<?php

namespace Drupal\persistent_identifiers\Plugin\ContextReaction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Mints and persists a persistent identifier to an entity.
 *
 * @ContextReaction(
 *   id = "persistent_identifiers_add_pid",
 *   label = @Translation("Add persistent identifier")
 * )
 */
class PersistentIdentifiersAddPid extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'add_persistent_identifier' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Add persistent identifier to an entity.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(EntityInterface $entity = NULL) {
    $config = $this->getConfiguration();
    $module_config = \Drupal::config('persistent_identifiers.settings');

    $minter = \Drupal::service($module_config->get('persistent_identifiers_minter'));
    $persister = \Drupal::service($module_config->get('persistent_identifiers_persister'));
    $identifier = $minter->mint($entity);
    $persister->persist($entity, $identifier, FALSE);
    \Drupal::logger('persistent_identifiers')->info(t("Persistent identifier %pid minted for entity @id via Context Reaction.", ['%pid' => $pid, '@id' => $entity->id()]));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['add_persistent_identifier'] = [
      '#title' => $this->t('Add persistent identifier to a node or other entity'),
      '#type' => 'checkbox',
      '#description' => $this->t("Checking this box will add a persistent identifier to the entity specified in the condition."),
      '#default_value' => isset($config['add_persistent_identifier']) ? $config['add_persistent_identifier'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'add_persistent_identifier' => trim($form_state->getValue('add_persistent_identifier')),
    ]);
  }

}
