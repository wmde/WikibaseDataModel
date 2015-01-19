<?php

namespace Wikibase\DataModel\Entity\Diff;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Statement\StatementListPatcher;

/**
 * @since 1.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PropertyPatcher implements EntityPatcherStrategy {

	/**
	 * @var EntityTermsPatcher
	 */
	private $entityTermsPatcher;

	/**
	 * @var StatementListPatcher
	 */
	private $statementListPatcher;

	public function __construct() {
		$this->entityTermsPatcher = new EntityTermsPatcher();
		$this->statementListPatcher = new StatementListPatcher();
	}

	/**
	 * @param string $entityType
	 *
	 * @return boolean
	 */
	public function canPatchEntityType( $entityType ) {
		return $entityType === 'property';
	}

	/**
	 * @param EntityDocument $entity
	 * @param EntityDiff $patch
	 *
	 * @return Property
	 * @throws InvalidArgumentException
	 */
	public function patchEntity( EntityDocument $entity, EntityDiff $patch ) {
		$this->assertIsProperty( $entity );

		$this->patchProperty( $entity, $patch );
	}

	private function assertIsProperty( EntityDocument $property ) {
		if ( !( $property instanceof Property ) ) {
			throw new InvalidArgumentException( '$property must be an instance of Property' );
		}
	}

	private function patchProperty( Property $property, EntityDiff $patch ) {
		$this->entityTermsPatcher->patchEntityTerms( $property->getEntityTerms(), $patch );

		$property->setStatements( $this->statementListPatcher->getPatchedStatementList(
			$property->getStatements(),
			$patch->getClaimsDiff()
		) );
	}

}
