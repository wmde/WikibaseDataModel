<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Helper for managing objects grouped by property id.
 *
 * @since 1.1
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ByPropertyIdArrayNew {

	/**
	 * @var ByPropertyIdGrouper
	 */
	private $byPropertyIdGrouper;

	/**
	 * @var PropertyIdProvider[]
	 */
	private $flatArray;

	/**
	 * @param PropertyIdProvider[] $propertyIdProviders
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $propertyIdProviders ) {
		$this->byPropertyIdGrouper = new ByPropertyIdGrouper( $propertyIdProviders );
		$this->flatArray = array();

		foreach ( $this->byPropertyIdGrouper->getPropertyIds() as $propertyId ) {
			$propertyIdProviders = $this->byPropertyIdGrouper->getForPropertyId( $propertyId );
			$this->flatArray = array_merge( $this->flatArray, $propertyIdProviders );
		}
	}

	/**
	 * Returns a list of all PropertyIdProvider instances grouped by their PropertyId.
	 *
	 * @since 1.1
	 *
	 * @return PropertyIdProvider[]
	 */
	public function getFlatArray() {
		return $this->flatArray;
	}

	/**
	 * Returns the index of the given PropertyIdPovider in the flat array.
	 *
	 * @since 1.1
	 *
	 * @param PropertyIdProvider $propertyIdProvider
	 * @return int
	 *
	 * @throws OutOfBoundsException
	 */
	public function getIndex( PropertyIdProvider $propertyIdProvider ) {
		$index = array_search( $propertyIdProvider, $this->flatArray, true );

		if ( $index === false ) {
			throw new OutOfBoundsException( 'The given PropertyIdProvider was not found.' );
		}

		return $index;
	}

	/**
	 * Adds the given PropertyIdProvider to the array at the given index.
	 *
	 * @since 1.1
	 *
	 * @param PropertyIdProvider $propertyIdProvider
	 * @param int $index
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function addAtIndex( PropertyIdProvider $propertyIdProvider, $index ) {
		$this->assertValidIndex( $index );

		$groupIndices = $this->getFlatArrayGroupIndices();
		$propertyId = $propertyIdProvider->getPropertyId();
		$idSerialization = $propertyId->getSerialization();

		if ( isset( $groupIndices[$idSerialization] ) ) {
			$groupIndex = $groupIndices[$idSerialization];
			$count = count( $this->byPropertyIdGrouper->getForPropertyId( $propertyId ) );
		} else {
			$groupIndex = 0;
			$count = 0;
		}

		// if not inside of group also move property group
		if ( $index < $groupIndex || $groupIndex + $count <= $index ) {
			// find real index to insert
			$index = $index === 0 ? 0 : $this->findNextIndex( $index );
			$this->moveGroupInFlatArray( $groupIndex, $count, $index );
		}

		$this->addtoFlatArray( $propertyIdProvider, $index ); 
		$this->byPropertyIdGrouper = new ByPropertyIdGrouper( $this->flatArray );
	}

	/**
	 * Removes the PropertyIdProvider at the given index and returns it.
	 *
	 * @since 1.1
	 *
	 * @param int $index
	 * @return PropertyIdProvider
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function removeAtIndex( $index ) {
		$this->assertValidIndex( $index );

		$object = $this->removeFromFlatArray( $index );
		$this->byPropertyIdGrouper = new ByPropertyIdGrouper( $this->flatArray );

		return $object;
	}

	/**
	 * Removes the given PropertyIdProvider and returns it.
	 *
	 * @since 1.1
	 * 
	 * @param PropertyIdProvider $propertyIdProvider
	 * @return PropertyIdProvider
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function removeObject( PropertyIdProvider $propertyIdProvider ) {
		return $this->removeAtIndex( $this->getIndex( $propertyIdProvider ) );
	}

	/**
	 * Moves a PropertyIdProvider from the old to the new index and returns it.
	 *
	 * @since 1.1
	 *
	 * @param int $oldIndex
	 * @param int $newIndex
	 * @return PropertyIdProvider
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function moveToIndex( $oldIndex, $newIndex ) {
		$this->assertValidIndex( $oldIndex );
		$this->assertValidIndex( $newIndex );

		$object = $this->removeAtIndex( $oldIndex );
		$this->addAtIndex( $object, $newIndex );

		return $object;
	}

	/**
	 * Moves the given PropertyIdProvider to the new index and returns it.
	 *
	 * @since 1.1
	 *
	 * @param PropertyIdProvider $propertyIdProvider
	 * @param int $index
	 * @return PropertyIdProvider
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function moveObject( PropertyIdProvider $propertyIdProvider, $index ) {
		return $this->moveToIndex( $this->getIndex( $propertyIdProvider ), $index );
	}

	/**
	 * Adds the object at the given index.
	 * @see array_splice
	 *
	 * @param PropertyIdProvider $propertyIdProvider
	 * @param int $index
	 */
	private function addtoFlatArray( PropertyIdProvider $propertyIdProvider, $index ) {
		array_splice( $this->flatArray, $index, 0, array( $propertyIdProvider ) );
	}

	/**
	 * Removes the object at the given index and returns it.
	 * @see array_splice
	 *
	 * @param int $index
	 * @return PropertyIdProvider
	 */
	private function removeFromFlatArray( $index ) {
		$objects = array_splice( $this->flatArray, $index, 1 );
		return $objects[0];
	}

	/**
	 * Moves a list of objects with the given length from the start to the target index.
	 * @see array_splice
	 *
	 * @param int $start
	 * @param int $length
	 * @param int $target
	 */
	private function moveGroupInFlatArray( $start, $length, $target ) {
		// make sure we do not exceed the limits
		if ( $start < $target ) {
			$target = $target - $length;
		}

		$objects = array_splice( $this->flatArray, $start, $length );
		array_splice( $this->flatArray, $target, 0, $objects );
	}

	/**
	 * Finds the next index in the flat array which starts a new PropertyId group.
	 *
	 * @param int $index
	 * @return int
	 */
	private function findNextIndex( $index ) {
		$groupIndices = $this->getFlatArrayGroupIndices();

		foreach ( $groupIndices as $groupIndex ) {
			if ( $groupIndex >= $index ) {
				return $groupIndex;
			}
		}

		return count( $this->flatArray );
	}

	/**
	 * Finds all indeces where a new PropertyId group starts.
	 *
	 * @return int[]
	 */
	private function getFlatArrayGroupIndices() {
		$indices = array();
		$index = 0;

		foreach ( $this->byPropertyIdGrouper->getPropertyIds() as $propertyId ) {
			$indices[$propertyId->getSerialization()] = $index;
			$index += count( $this->byPropertyIdGrouper->getForPropertyId( $propertyId ) );
		}

		return $indices;
	}

	/**
	 * Asserts that the given paramter is a valid index.
	 *
	 * @param int $index
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	private function assertValidIndex( $index ) {
		if ( !is_int( $index ) ) {
			throw new InvalidArgumentException( 'Only integer indices are supported.' );
		}

		if ( $index < 0 || $index > count( $this->flatArray ) ) {
			throw new OutOfBoundsException( 'The index exceeds the array dimensions.' );
		}
	}

}
