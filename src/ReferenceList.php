<?php

namespace Wikibase\DataModel;

use Hashable;
use InvalidArgumentException;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;

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
 */
class ReferenceList extends HashableObjectStorage {

	/**
	 * @param Reference[]|Traversable|Reference $references
	 * @param Reference [$reference2,...]
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $references = array() /*...*/ ) {
		if ( $references instanceof Reference ) {
			$references = func_get_args();
		}

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
		if( !is_null( $index ) && !is_integer( $index ) ) {
			throw new InvalidArgumentException( '$index must be an integer or null; got ' . gettype( $index ) );
		} elseif ( is_null( $index ) || $index >= count( $this ) ) {
			// Append object to the end of the reference list.
			$this->attach( $reference );
		} else {
			$this->insertReferenceAtIndex( $reference, $index );
		}
	}

	// @codingStandardsIgnoreStart
	/**
	 * @since 1.1
	 *
	 * @param Snak $snak
	 * @param Snak [$snak2, ...]
	 *
	 * @throws InvalidArgumentException
	 */
	public function addNewReference( Snak $snak /* Snak, ... */ ) {
		// @codingStandardsIgnoreEnd
		$this->addReference( new Reference( new SnakList( func_get_args() ) ) );
	}

	/**
	 * @since 0.5
	 *
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
	 * @see Serializable::serialize
	 *
	 * @since 2.1
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( iterator_to_array( $this ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 2.1
	 *
	 * @param string $data
	 */
	public function unserialize( $data ) {
		$this->__construct( unserialize( $data ) );
	}

}
