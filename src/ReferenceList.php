<?php

namespace Wikibase\DataModel;

use Comparable;
use InvalidArgumentException;
use SplObjectStorage;
use Traversable;
use Wikibase\DataModel\Snak\Snak;

/**
 * List of Reference objects.
 *
 * Note that this implementation is based on SplObjectStorage and
 * is not enforcing the type of objects set via it's native methods.
 * Therefore one can add non-Reference-implementing objects when
 * not sticking to the methods of the References interface.
 *
 * @since 0.1
 * Does not implement References anymore since 2.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author H. Snater < mediawiki@snater.com >
 * @author Thiemo Mättig
 */
class ReferenceList extends SplObjectStorage implements Comparable {

	/**
	 * @param Reference[]|Traversable $references
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $references = array() ) {
		if ( !is_array( $references ) && !( $references instanceof Traversable ) ) {
			throw new InvalidArgumentException( '$references must be an array or an instance of Traversable' );
		}

		foreach ( $references as $reference ) {
			if ( !( $reference instanceof Reference ) ) {
				throw new InvalidArgumentException( 'Every element in $references must be an instance of Reference' );
			}

			$this->addReference( $reference );
		}
	}

	/**
	 * Adds the provided reference to the list.
	 *
	 * @since 0.1
	 *
	 * @param Reference $reference
	 * @param int|null $index
	 *
	 * @throws InvalidArgumentException
	 */
	public function addReference( Reference $reference, $index = null ) {
		if ( !is_int( $index ) && $index !== null ) {
			throw new InvalidArgumentException( '$index must be an integer or null' );
		}

		if ( $index === null || $index >= count( $this ) ) {
			// Append object to the end of the reference list.
			$this->attach( $reference );
		} else {
			$this->insertReferenceAtIndex( $reference, $index );
		}
	}

	/**
	 * @see SplObjectStorage::attach
	 *
	 * @param Reference $reference
	 * @param mixed $data Unused in the ReferenceList class.
	 */
	public function attach( $reference, $data = null ) {
		if ( !$reference->isEmpty() ) {
			parent::attach( $reference, $data );
		}
	}

	/**
	 * @since 1.1
	 *
	 * @param Snak[]|Snak $snaks
	 * @param Snak [$snak2,...]
	 *
	 * @throws InvalidArgumentException
	 */
	public function addNewReference( $snaks = array() /*...*/ ) {
		if ( $snaks instanceof Snak ) {
			$snaks = func_get_args();
		}

		$this->addReference( new Reference( $snaks ) );
	}

	/**
	 * @param Reference $reference
	 * @param int $index
	 */
	private function insertReferenceAtIndex( Reference $reference, $index ) {
		$referencesToShift = array();
		$i = 0;

		// Determine the references that need to be shifted and detach them:
		foreach( $this as $object ) {
			if( $i++ >= $index ) {
				$referencesToShift[] = $object;
			}
		}

		foreach( $referencesToShift as $object ) {
			$this->detach( $object );
		}

		// Attach the new reference and reattach the previously detached references:
		$this->attach( $reference );

		foreach( $referencesToShift as $object ) {
			$this->attach( $object );
		}
	}

	/**
	 * Returns if the list contains a reference with the same hash as the provided reference.
	 *
	 * @since 0.1
	 *
	 * @param Reference $reference
	 *
	 * @return boolean
	 */
	public function hasReference( Reference $reference ) {
		return $this->contains( $reference )
			|| $this->hasReferenceHash( $reference->getHash() );
	}

	/**
	 * Returns the index of a reference or false if the reference could not be found.
	 *
	 * @since 0.5
	 *
	 * @param Reference $reference
	 *
	 * @return int|boolean
	 */
	public function indexOf( Reference $reference ) {
		$index = 0;

		foreach( $this as $object ) {
			if( $object === $reference ) {
				return $index;
			}
			$index++;
		}

		return false;
	}

	/**
	 * Removes the reference with the same hash as the provided reference if such a reference exists in the list.
	 *
	 * @since 0.1
	 *
	 * @param Reference $reference
	 */
	public function removeReference( Reference $reference ) {
		$this->removeReferenceHash( $reference->getHash() );
	}

	/**
	 * Returns if the list contains a reference with the provided hash.
	 *
	 * @since 0.3
	 *
	 * @param string $referenceHash
	 *
	 * @return boolean
	 */
	public function hasReferenceHash( $referenceHash ) {
		return $this->getReference( $referenceHash ) !== null;
	}

	/**
	 * Removes the reference with the provided hash if it exists in the list.
	 *
	 * @since 0.3
	 *
	 * @param string $referenceHash	`
	 */
	public function removeReferenceHash( $referenceHash ) {
		$reference = $this->getReference( $referenceHash );

		if ( $reference !== null ) {
			$this->detach( $reference );
		}
	}

	/**
	 * Returns the reference with the provided hash, or null if there is no such reference in the list.
	 *
	 * @since 0.3
	 *
	 * @param string $referenceHash
	 *
	 * @return Reference|null
	 */
	public function getReference( $referenceHash ) {
		/**
		 * @var Hashable $hashable
		 */
		foreach ( $this as $hashable ) {
			if ( $hashable->getHash() === $referenceHash ) {
				return $hashable;
			}
		}

		return null;
	}

	/**
	 * The hash is purely value based. Order of the elements in the array is not held into account.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public function getValueHash() {
		$hashes = array();

		foreach ( $this->toArray() as $reference ) {
			$hashes[] = $reference->getHash();
		}

		sort( $hashes );

		return implode( '|', $hashes );
	}

	/**
	 * @return Reference[]
	 */
	public function toArray() {
		return iterator_to_array( $this );
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
			&& $this->getValueHash() === $target->getValueHash();
	}

}
