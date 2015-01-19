<?php

namespace Wikibase\DataModel\Entity\Diff;

use Diff\DiffOp\Diff\Diff;
use Diff\Patcher\MapPatcher;
use InvalidArgumentException;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\EntityTerms;
use Wikibase\DataModel\Term\TermList;

/**
 * Package private.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityTermsPatcher {

	/**
	 * @var MapPatcher
	 */
	private $patcher;

	public function __construct() {
		$this->patcher = new MapPatcher();
	}

	/**
	 * @param EntityTerms $entityTerms
	 * @param EntityDiff $patch
	 *
	 * @throws InvalidArgumentException
	 */
	public function patchEntityTerms( EntityTerms $entityTerms, EntityDiff $patch ) {
		$labels = $this->patcher->patch(
			$entityTerms->getLabels()->toTextArray(),
			$patch->getLabelsDiff()
		);

		$entityTerms->setLabels( $this->newTermListFromArray( $labels ) );

		$descriptions = $this->patcher->patch(
			$entityTerms->getDescriptions()->toTextArray(),
			$patch->getDescriptionsDiff()
		);

		$entityTerms->setDescriptions( $this->newTermListFromArray( $descriptions ) );

		$this->patchAliases( $entityTerms, $patch->getAliasesDiff() );
	}

	private function newTermListFromArray( $termArray ) {
		$termList = new TermList();

		foreach ( $termArray as $language => $labelText ) {
			$termList->setTextForLanguage( $language, $labelText );
		}

		return $termList;
	}

	private function patchAliases( EntityTerms $entityTerms, Diff $aliasesDiff ) {
		$patchedAliases = $this->patcher->patch(
			$this->getAliasesArrayForPatching( $entityTerms->getAliasGroups() ),
			$aliasesDiff
		);

		$entityTerms->setAliasGroups( $this->getAliasesFromArrayForPatching( $patchedAliases ) );
	}

	private function getAliasesArrayForPatching( AliasGroupList $aliases ) {
		$textLists = array();

		/**
		 * @var AliasGroup $aliasGroup
		 */
		foreach ( $aliases as $languageCode => $aliasGroup ) {
			$textLists[$languageCode] = $aliasGroup->getAliases();
		}

		return $textLists;
	}

	private function getAliasesFromArrayForPatching( array $patchedAliases ) {
		$aliases = new AliasGroupList();

		foreach( $patchedAliases as $languageCode => $aliasList ) {
			$aliases->setAliasesForLanguage( $languageCode, $aliasList );
		}

		return $aliases;
	}

}
