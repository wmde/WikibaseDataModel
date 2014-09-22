<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Description of ByPropertyIdArrayNew
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

	private function updatePropertySuggester() {
		$this->byPropertyIdGrouper = new ByPropertyIdGrouper( $this->flatArray );
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
	 * 
	 * @param PropertyIdProvider $propertyIdProvider
	 * @param integer $index
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

		$this->updatePropertySuggester();
	}

	/**
	 * 
	 * @param integer $index
	 */
	public function removeAtIndex( $index ) {
		$this->assertValidIndex( $index );

		$object = $this->removeFromFlatArray( $index );
		$this->updatePropertySuggester();

		return $object;
	}

	/**
	 * 
	 * @param integer $oldIndex
	 * @param integer $newIndex
	 */
	public function moveToIndex( $oldIndex, $newIndex ) {
		$this->assertValidIndex( $oldIndex );
		$this->assertValidIndex( $newIndex );

		$object = $this->removeAtIndex( $oldIndex );
		$this->addAtIndex( $object, $newIndex );
	}

	private function addtoFlatArray( PropertyIdProvider $propertyIdProvider, $index ) {
		array_splice( $this->flatArray, $index, 0, array( $propertyIdProvider ) );
	}

	private function removeFromFlatArray( $index ) {
		return array_splice( $this->flatArray, $index, 1 )[0];
	}

	private function moveGroupInFlatArray( $start, $length, $to ) {
		if ( $start < $to ) {
			$to = $to - $length;
		}

		$objects = array_splice( $this->flatArray, $start, $length );
		array_splice( $this->flatArray, $to, 0, $objects );
	}

	private function findNextIndex( $index ) {
		$groupIndices = $this->getFlatArrayGroupIndices();

		foreach ( $groupIndices as $groupIndex ) {
			if ( $groupIndex > $index ) {
				return $groupIndex;
			}
		}

		return count( $this->flatArray );
	}

	private function getFlatArrayGroupIndices() {
		$indices = array();
		$index = 0;

		foreach ( $this->byPropertyIdGrouper->getPropertyIds() as $propertyId ) {
			$indices[$propertyId->getSerialization()] = $index;
			$index += count( $this->byPropertyIdGrouper->getForPropertyId( $propertyId ) );
		}

		return $indices;
	}

	private function assertValidIndex( $index ) {
		if ( !is_int( $index ) ) {
			throw new InvalidArgumentException( 'Only integer indices are supported.' );
		}

		if ( $index < 0 || $index >= count( $this->flatArray ) ) {
			throw new OutOfBoundsException( 'The index exceeds the array dimensions.' );
		}
	}

}
