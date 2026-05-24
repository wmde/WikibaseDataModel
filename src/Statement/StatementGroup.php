<?php

namespace Wikibase\DataModel\Statement;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\PropertyIdProvider;

/**
 * List of statements with the same property id.
 *
 * @since 4.3
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class StatementGroup implements IteratorAggregate, Countable {

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @var Statement[]
	 */
	private $statements = array();

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

		$this->statements[] = $statement;
	}

	/**
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
		$statements = array();

		foreach ( $this->statements as $statement ) {
			if ( $statement->getRank() === $rank ) {
				$statements[] = $statement;
			}
		}

		return $statements;
	}

	/**
	 * @return Traversable
	 */
	public function getIterator() {
		return new ArrayIterator( $this->statements );
	}

	/**
	 * @return Statement[] Numerically indexed (non-sparse) array.
	 */
	public function toArray() {
		return $this->statements;
	}

	/**
	 * @see Countable::count
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->statements );
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->statements );
	}

}
