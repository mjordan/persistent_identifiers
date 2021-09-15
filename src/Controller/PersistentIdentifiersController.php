<?php

namespace Drupal\persistent_identifiers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;



class PersistentIdentifiersController extends ControllerBase {

	/**
	 * The function calling the batch process.
	 */
	function persistent_identifiers_mint_and_persist(Request $request) {
		$operations[] = ['\Drupal\persistent_identifiers\Controller\PersistentIdentifiersController::persistent_identifiers_batch_process', []];
		$batch = [
			'title' => t('Persistent Identifiers Mint and Persist'),
			'operations' => $operations,
			'finished' => '\Drupal\persistent_identifiers\Controller\PersistentIdentifiersController::persistent_identifiers_finished_callback',
			'init_message' => t('Batch is starting.'),
			'progress_message' => t('Processed @current out of @total.'),
		];

		// Adds the batch sets
		batch_set($batch);
		// Process the batch and after redirect to the frontpage
		return batch_process('admin/config/persistent_identifiers/settings');


	}
	/**
	 * A Batch process function.
	 */
	function persistent_identifiers_batch_process(&$context) {
		$config = \Drupal::config('persistent_identifiers.settings');
		$types = $config->get('persistent_identifiers_bundles');

		// QUICK HACK TODO: don't save zeroes if type not selected
		foreach ($types as $key => $type) {
			if ($types[$key] === 0) {
				unset($types[$key]);
			}
		}
		// END QUICK HACK

		if (empty($context['sandbox'])) {
			$query = \Drupal::entityQuery('node');

			$count = $query->condition('type', $types)
									->count()
									->execute();

			$context['sandbox']['progress'] = 0;
			$context['sandbox']['current_id'] = 0;
			$context['sandbox']['max'] = $count;
		}

		$limit = 25;
		$query = \Drupal::entityQuery('node');

		$result = $query->condition('type', $types)
								 ->condition('nid', $context['sandbox']['current_id'], '>')
								 ->sort('nid', 'ASC')
								 ->range(0, $limit)
								 ->execute();
		$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

		foreach ($nodes as $node) {
			// Persist and Mint
			$minter = \Drupal::service($config->get('persistent_identifiers_minter'));
			$pid = $minter->mint($node);
			if (is_null($pid)) {
				\Drupal::logger('persistent_identifiers')->warning(t("Persistent identifier not created for node @nid.", ['@nid' => $node->id()]));
				\Drupal::messenger()->addWarning(t("Problem creating persistent identifier for this node. Details are available in the Drupal system log."));
				return;
			}
			$persister = \Drupal::service($config->get('persistent_identifiers_persister'));
			if ($persister->persist($node, $pid)) {
				\Drupal::logger('persistent_identifiers')->info(t("Persistent identifier %pid created for node @nid.", ['%pid' => $pid, '@nid' => $node->id()]));
				\Drupal::messenger()->addStatus(t("Persistent identifier %pid created for this node.", ['%pid' => $pid]));
			}

			// Modify context for next batch run
			$context['results'][] = $node->id . ' : ' . $node->title->value;
			$context['sandbox']['progress']++;
			$context['sandbox']['current_id'] = $node->id;
			$context['message'] = $node->title->value;
		}

		if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
			$context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
		}
	}

  /**
   * Function that signals that the mint and persist is finished.
   */
  public static function persistent_identifiers_finished_callback($success, $results, $operations) {

  	if ($success) {
  		$message = t('Persistent Identifers: Persisting complete');
  	}
  	else {
  		$message = t('There were some errors.');
  	}
  	drupal_set_message($message);
  }

}
