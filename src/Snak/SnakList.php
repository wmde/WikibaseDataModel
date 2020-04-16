<?php

namespace Wikibase\DataModel\Snak;

use Comparable;
use Countable;
use Hashable;
use InvalidArgumentException;
use Iterator;
use Serializable;
use Traversable;
use Wikibase\DataModel\Internal\MapValueHasher;

/**
 * List of Snak objects.
 * Indexes the snaks by hash and ensures no more than one snak with the same hash is in the list.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Addshore
 */
class SnakList implements Comparable, Countable, Hashable, Iterator, Serializable {

	/**
	 * @var Snak[]
	 */
	private $snaks = [];

	/**
	 * @param Snak[]|Traversable $snaks
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $snaks = [] ) {
		if ( !is_array( $snaks ) && !( $snaks instanceof Traversable ) ) {
			throw new InvalidArgumentException( '$snaks must be an array or an instance of Traversable' );
		}

		foreach ( $snaks as $value ) {
			if ( !( $value instanceof Snak ) ) {
				throw new InvalidArgumentException( '$value must be a Snak' );
			}

			$this->addSnak( $value );
		}
	}

	/**
	 * @since 0.1
	 *
	 * @param string $snakHash
	 *
	 * @return boolean
	 */
	public function hasSnakHash( $snakHash ) {
		return isset( $this->snaks[$snakHash] );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $snakHash
	 */
	public function removeSnakHash( $snakHash ) {
		unset( $this->snaks[$snakHash] );
	}

	/**
	 * @since 0.1
	 *
	 * @param Snak $snak
	 *
	 * @return boolean Indicates if the snak was added or not.
	 */
	public function addSnak( Snak $snak ) {
		$hash = $snak->getHash();

		if ( $this->hasSnakHash( $hash ) ) {
			return false;
		}

		$this->snaks[$hash] = $snak;
		return true;
	}

	/**
	 * @since 0.1
	 *
	 * @param Snak $snak
	 *
	 * @return boolean
	 */
	public function hasSnak( Snak $snak ) {
		return $this->hasSnakHash( $snak->getHash() );
	}

	/**
	 * @since 0.1
	 *
	 * @param Snak $snak
	 */
	public function removeSnak( Snak $snak ) {
		$this->removeSnakHash( $snak->getHash() );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $snakHash
	 *
	 * @return Snak|bool
	 */
	public function getSnak( $snakHash ) {
		return isset( $this->snaks[$snakHash] ) ? $this->snaks[$snakHash] : false;
	}

	/**
	 * @see Comparable::equals
	 *
	 * The comparison is done purely value based, ignoring the order of the elements in the array.
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
			&& $this->getHash() === $target->getHash();
	}

	/**
	 * @return int
	 */
	public function count() {
		return count( $this->snaks );
	}

	/**
	 * @see Hashable::getHash
	 *
	 * The hash is purely value based. Order of the elements in the array is not held into account.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHash() {
		$hasher = new MapValueHasher();
		return $hasher->hash( $this->snaks );
	}

	/**
	 * Groups snaks by property, and optionally orders them.
	 *
	 * @param string[] $order List of property ID strings to order by. Snaks with other properties
	 *  will also be grouped, but put at the end, in the order each property appeared first in the
	 *  original list.
	 *
	 * @since 0.5
	 */
	public function orderByProperty( array $order = [] ) {
		$byProperty = array_fill_keys( $order, [] );

		foreach ( $this->snaks as $snak ) {
			$byProperty[$snak->getPropertyId()->getSerialization()][$snak->getHash()] = $snak;
		}

		$this->snaks = [];
		foreach ( $byProperty as $snaks ) {
			$this->snaks = array_merge( $this->snaks, $snaks );
		}
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( [
			'data' => array_values( $this->snaks ),
			'index' => count( $this->snaks ) - 1,
		] );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		$serializationData = unserialize( $serialized );
		$this->snaks = [];
		foreach ( $serializationData['data'] as $snak ) {
			$this->addSnak( $snak );
		}
	}

	/**
	 * Returns if the ArrayObject has no elements.
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return $this->snaks === [];
	}

	public function current() {
		return current( $this->snaks );
	}

	public function next() {
		return next( $this->snaks );
	}

	public function key() {
		return key( $this->snaks );
	}

	public function valid() {
		return current( $this->snaks ) !== false;
	}

	public function rewind() {
		return reset( $this->snaks );
	}

}
