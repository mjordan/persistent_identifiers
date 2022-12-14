<?php

namespace Drupal\hdl\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Mints Handle.
 *
 * @Action(
 *   id = "hdl_mint_handle",
 *   label = @Translation("Create Handle"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
//class MintHdlAction extends ActionBase {
class MintHdlAction extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $minter = \Drupal::service('hdl.minter.hdl');
    $persister_id = \Drupal::config('persistent_identifiers.settings')->get('persistent_identifiers_persister');
    $persister = \Drupal::service($persister_id);
    // The values saved in this action's configuration form
    // are in $this->configuration.
    $hdl_identifier = $minter->mint($entity, $this->configuration);

    if (strlen($hdl_identifier)) {
      $persister->persist($entity, $hdl_identifier);
      $this->messenger()->addMessage('"' . $entity->label() . '" assigned ' . $hdl_identifier);
    }
    else {
      $this->messenger()->addMessage('"' . $entity->label() . '" (node ' . $entity->id() . ') not assigned a Handle. See system log for details.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $result = $object->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
