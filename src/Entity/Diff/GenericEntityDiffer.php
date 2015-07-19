<?php

namespace Wikibase\DataModel\Entity\Diff;

use Diff\Differ\MapDiffer;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Statement\StatementListDiffer;
use Wikibase\DataModel\StatementListProvider;
use Wikibase\DataModel\Term\FingerprintProvider;

/**
 * @since 2.6
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class GenericEntityDiffer {

	/**
	 * @var MapDiffer
	 */
	private $recursiveMapDiffer;

	/**
	 * @var StatementListDiffer
	 */
	private $statementListDiffer;

	public function __construct() {
		$this->recursiveMapDiffer = new MapDiffer( true );
		$this->statementListDiffer = new StatementListDiffer();
	}

	/**
	 * @param EntityDocument $from
	 * @param EntityDocument $to
	 *
	 * @return EntityDiff
	 */
	public function diffEntities( EntityDocument $from, EntityDocument $to ) {
		$diffOps = $this->recursiveMapDiffer->doDiff(
			$this->toDiffArray( $from ),
			$this->toDiffArray( $to )
		);

		$diffOps['claim'] = $this->statementListDiffer->getDiff(
			$this->getStatementList( $from ),
			$this->getStatementList( $to )
		);

		return new EntityDiff( $diffOps );
	}

	private function toDiffArray( EntityDocument $entity ) {
		$array = array();

		if ( $entity instanceof FingerprintProvider ) {
			$fingerprint = $entity->getFingerprint();

			$array['aliases'] = $fingerprint->getAliasGroups()->toTextArray();
			$array['label'] = $fingerprint->getLabels()->toTextArray();
			$array['description'] = $fingerprint->getDescriptions()->toTextArray();
		}

		if ( $entity instanceof Item ) {
			$siteLinks = $entity->getSiteLinkList();

			if ( !$siteLinks->isEmpty() ) {
				$array['links'] = $this->getLinksInDiffFormat( $siteLinks );
			}
		}

		return $array;
	}

	private function getLinksInDiffFormat( SiteLinkList $siteLinks ) {
		$links = array();

		/** @var SiteLink $siteLink */
		foreach ( $siteLinks as $siteLink ) {
			$links[$siteLink->getSiteId()] = array(
				'name' => $siteLink->getPageName(),
				'badges' => array_map(
					function( ItemId $id ) {
						return $id->getSerialization();
					},
					$siteLink->getBadges()
				)
			);
		}

		return $links;
	}

	private function getStatementList( EntityDocument $entity ) {
		return $entity instanceof StatementListProvider
			? $entity->getStatements()
			: new StatementList();
	}

}
