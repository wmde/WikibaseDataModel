<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Snak\Snaks;

/**
 * Object that represents a single Wikibase reference.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#ReferenceRecords
 *
 * @since 0.1, instantiable since 0.4
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Reference implements \Hashable, \Comparable, \Immutable, \Countable {

	/**
	 * @var Snaks
	 */
	private $snaks;

	/**
	 * An array of Snak is only supported since version 1.1.
	 *
	 * @param Snaks|Snak[]|null $snaks
	 * @throws InvalidArgumentException
	 */
	public function __construct( $snaks = null ) {
		if ( $snaks === null ) {
			$this->snaks = new SnakList();
		}
		elseif ( $snaks instanceof Snaks ) {
			$this->snaks = $snaks;
		}
		elseif ( is_array( $snaks ) ) {
			$this->snaks = new SnakList( $snaks );
		}
		else {
			throw new InvalidArgumentException( '$snaks must be an instance of Snaks, an array of instances of Snak, or null' );
		}
	}

	/**
	 * Returns the property snaks that make up this reference.
	 * Modification of the snaks should NOT happen through this getter.
	 *
	 * @since 0.1
	 *
	 * @return Snaks
	 */
	public function getSnaks() {
		return $this->snaks;
	}

	/**
	 * @see Countable::count
	 *
	 * @since 0.3
	 *
	 * @return integer
	 */
	public function count() {
		return count( $this->snaks );
	}

	/**
	 * @since 2.6
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return $this->snaks->isEmpty();
	}

	/**
	 * @see Hashable::getHash
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHash() {
		// For considering the reference snaks' property order without actually manipulating the
		// reference snaks's order, a new SnakList is generated. The new SnakList is ordered
		// by property and its hash is returned.
		$orderedSnaks = new SnakList( $this->snaks );

		$orderedSnaks->orderByProperty();

		return $orderedSnaks->getHash();
	}

	/**
	 * @see Comparable::equals
	 *
	 * The comparison is done purely value based, ignoring the order of the snaks.
	 *
	 * @since 0.3
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->snaks->equals( $target->snaks );
	}

}
