<?php

namespace Drupal\ezid\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Mints ARKs via EZID.
 *
 * @Action(
 *   id = "ezid_mint_ark",
 *   label = @Translation("Mint EZID Ark"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class MintEzidAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $minter = \Drupal::service('ezid.minter.ezid');
    $persister_id = \Drupal::config('persistent_identifiers.settings')->get('persistent_identifiers_persister');
    $persister = \Drupal::service($persister_id);
    // The values saved in this action's configuration form
    // are in $this->configuration.
    $ark = $minter->mint($entity, $this->configuration);

    if (strlen($ark)) {
      $persister->persist($entity, $ark);
      $this->messenger()->addMessage('"' . $entity->label() . '" assigned ' . $ark);
    }
    else {
      $this->messenger()->addMessage('"' . $entity->label() . '" (node ' . $entity->id() . ') not assigned an ARK. See system log for details.');
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
