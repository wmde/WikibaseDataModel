<?php

namespace Wikibase\DataModel\Statement;

use InvalidArgumentException;
use Traversable;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\PropertyIdProvider;

/**
 * List of statements with the same property id, grouped by rank.
 *
 * @since 4.2
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class StatementGroup implements PropertyIdProvider {

	/**
	 * @var Statement[][]
	 */
	private $statementsByRank = array();

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @param PropertyId|int $propertyId
	 */
	public function __construct( $propertyId ) {
		if ( is_int( $propertyId ) ) {
			$propertyId = PropertyId::newFromNumber( $propertyId );
		}

		if ( !( $propertyId instanceof PropertyId ) ) {
			throw new InvalidArgumentException( '$propertyId must be an integer or an instance of PropertyId' );
		}

		$this->propertyId = $propertyId;
	}

	/**
	 * @param Statement[]|Traversable $statements
	 * @throws InvalidArgumentException
	 */
	public function addStatements( $statements ) {
		if ( !is_array( $statements ) && !( $statements instanceof Traversable ) ) {
			throw new InvalidArgumentException( '$statements must be an array or an instance of Traversable' );
		}

		foreach ( $statements as $statement ) {
			if ( !( $statement instanceof Statement ) ) {
				throw new InvalidArgumentException( 'Every element in $statements must be an instance of Statement' );
			}

			$this->addStatement( $statement );
		}
	}

	/**
	 * @param Statement $statement
	 * @throws InvalidArgumentException
	 */
	public function addStatement( Statement $statement ) {
		if ( !$statement->getPropertyId()->equals( $this->propertyId ) ) {
			throw new InvalidArgumentException( '$statement must have the property id ' . $this->propertyId->getSerialization() );
		}

		$this->statementsByRank[$statement->getRank()][] = $statement;
	}

	/**
	 * @see PropertyIdProvider::getPropertyId
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->propertyId;
	}

	/**
	 * @param int $rank
	 * @return Statement[]
	 */
	public function getByRank( $rank ) {
		if ( isset( $this->statementsByRank[$rank] ) ) {
			return $this->statementsByRank[$rank];
		}

		return array();
	}

}
