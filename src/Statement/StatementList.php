<?php

namespace Wikibase\DataModel\Statement;

use ArrayIterator;
use Comparable;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Snak\Snaks;

/**
 * Ordered, non-unique, collection of Statement objects.
 * Provides various filter operations.
 *
 * Does not do any indexing by default.
 * Does not provide complex modification functionality.
 *
 * @since 1.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StatementList implements IteratorAggregate, Comparable, Countable {

	/**
	 * @var Statement[]
	 */
	private $statements = array();

	/**
	 * @param Statement[]|Traversable|Statement $statements
	 * @param Statement [$statement2, ...]
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $statements = array() /* Statement, ... */ ) {
		if ( $statements instanceof Statement ) {
			$statements = func_get_args();
		}

		if ( !is_array( $statements ) && !( $statements instanceof Traversable ) ) {
			throw new InvalidArgumentException( '$statements must be an array or an instance of Traversable' );
		}

		foreach ( $statements as $statement ) {
			if ( !( $statement instanceof Statement ) ) {
				throw new InvalidArgumentException( 'Every element in $statements must be an instance of Statement' );
			}

			$this->statements[] = $statement;
		}
	}

	/**
	 * Returns the best statements per property.
	 * The best statements are those with the highest rank for a particular property.
	 * Deprecated ranks are never included.
	 *
	 * @return self
	 */
	public function getBestStatementPerProperty() {
		$bestStatementsFinder = new BestStatementsFinder( $this );
		return new self( $bestStatementsFinder->getBestStatementsPerProperty() );
	}

	/**
	 * Returns the property ids used by the statements.
	 * The keys of the returned array hold the serializations of the property ids.
	 *
	 * @return PropertyId[]
	 */
	public function getPropertyIds() {
		$propertyIds = array();

		foreach ( $this->statements as $statement ) {
			$propertyIds[$statement->getPropertyId()->getSerialization()] = $statement->getPropertyId();
		}

		return $propertyIds;
	}

	public function addStatement( Statement $statement ) {
		$this->statements[] = $statement;
	}

	/**
	 * @param Snak $mainSnak
	 * @param Snak[]|Snaks|null $qualifiers
	 * @param Reference[]|ReferenceList|null $references
	 * @param string|null $guid
	 */
	public function addNewStatement( Snak $mainSnak, $qualifiers = null, $references = null, $guid = null ) {
		$qualifiers = is_array( $qualifiers ) ? new SnakList( $qualifiers ) : $qualifiers;
		$references = is_array( $references ) ? new ReferenceList( $references ) : $references;

		$statement = new Statement( $mainSnak, $qualifiers, $references );
		$statement->setGuid( $guid );

		$this->addStatement( $statement );
	}

	/**
	 * Statements that have a main snak already in the list are filtered out.
	 * The last occurrences are retained.
	 *
	 * @return self
	 */
	public function getWithUniqueMainSnaks() {
		$statements = array();

		foreach ( $this->statements as $statement ) {
			$statements[$statement->getMainSnak()->getHash()] = $statement;
		}

		return new self( $statements );
	}

	/**
	 * @since 2.3
	 *
	 * @param PropertyId $id
	 *
	 * @return StatementList
	 */
	public function getWithPropertyId( PropertyId $id ) {
		$statements = array();

		foreach ( $this->statements as $statement ) {
			if ( $statement->getPropertyId()->equals( $id ) ) {
				$statements[] = $statement;
			}
		}

		return new self( $statements );
	}

	/**
	 * @since 2.4
	 *
	 * @param int|int[] $acceptableRanks
	 *
	 * @return StatementList
	 */
	public function getWithRank( $acceptableRanks ) {
		$acceptableRanks = array_flip( (array)$acceptableRanks );
		$statements = array();

		foreach ( $this->statements as $statement ) {
			if ( array_key_exists( $statement->getRank(), $acceptableRanks ) ) {
				$statements[] = $statement;
			}
		}

		return new self( $statements );
	}

	/**
	 * Returns the so called "best statements".
	 * If there are preferred statements, then this is all the preferred statements.
	 * If there are no preferred statements, then this is all normal statements.
	 *
	 * @since 2.4
	 *
	 * @return StatementList
	 */
	public function getBestStatements() {
		$statements = $this->getWithRank( Statement::RANK_PREFERRED );

		if ( !$statements->isEmpty() ) {
			return $statements;
		}

		return $this->getWithRank( Statement::RANK_NORMAL );
	}

	/**
	 * Returns a list of all Snaks on this StatementList. This includes at least the main snaks of
	 * Claims, the snaks from Claim qualifiers, and the snaks from Statement References.
	 *
	 * This is a convenience method for use in code that needs to operate on all snaks, e.g.
	 * to find all referenced Entities.
	 *
	 * @since 1.1
	 *
	 * @return Snak[]
	 */
	public function getAllSnaks() {
		$snaks = array();

		foreach ( $this->statements as $statement ) {
			foreach( $statement->getAllSnaks() as $snak ) {
				$snaks[] = $snak;
			}
		}

		return $snaks;
	}

	/**
	 * @since 2.3
	 *
	 * @return Snak[]
	 */
	public function getMainSnaks() {
		$snaks = array();

		foreach ( $this->statements as $statement ) {
			$snaks[] = $statement->getMainSnak();
		}

		return $snaks;
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
	 * @return int
	 */
	public function count() {
		return count( $this->statements );
	}

	/**
	 * @see Comparable::equals
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		if ( !( $target instanceof self )
			|| $this->count() !== $target->count()
		) {
			return false;
		}

		return $this->statementsEqual( $target->statements );
	}

	private function statementsEqual( array $statements ) {
		reset( $statements );

		foreach ( $this->statements as $statement ) {
			if ( !$statement->equals( current( $statements ) ) ) {
				return false;
			}

			next( $statements );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->statements );
	}

}
