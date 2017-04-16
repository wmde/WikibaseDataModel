<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use OutOfBoundsException;
use Traversable;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Maps property id providers by their property id.
 *
 * This class allows to reorder elements having the same property id
 * as well as changíng the order of the property ids themselves.
 *
 * @since 3.0
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ByPropertyIdMap {

	/**
	 * Map of serialized property ids pointing to
	 * a list of elements with that property id
	 *
	 * @var array[]
	 */
	private $byPropertyId = array();

	/**
	 * @var PropertyId[]
	 */
	private $propertyIds = array();

	/**
	 * @param PropertyIdProvider[]|Traversable $propertyIdProviders
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $propertyIdProviders ) {
		$byPropertyIdGrouper = new ByPropertyIdGrouper( $propertyIdProviders );

		$this->propertyIds = $byPropertyIdGrouper->getPropertyIds();
		foreach ( $this->propertyIds as $propertyId ) {
			$this->byPropertyId[$propertyId->getSerialization()] = $byPropertyIdGrouper->getByPropertyId( $propertyId );
		}
	}

	/**
	 * @param PropertyId $propertyId
	 * @param int|null $index
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function moveGroupToIndex( PropertyId $propertyId, $index ) {
		$this->assertIsIndex( $index );

		$oldIndex = array_search( $propertyId, $this->propertyIds );

		if ( $oldIndex === false ) {
			throw new OutOfBoundsException( 'There is no group for property id ' . $propertyId->getSerialization() );
		}

		if ( $index === null ) {
			$index = count( $this->propertyIds );
		}

		array_splice( $this->propertyIds, $oldIndex, 1 );
		array_splice( $this->propertyIds, $index, 0, array( $propertyId ) );

		$this->propertyIds = array_values( $this->propertyIds );
	}

	/**
	 * @param PropertyIdProvider $propertyIdProvider
	 * @param int|null $index
	 *
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function moveElementToIndex( PropertyIdProvider $propertyIdProvider, $index ) {
		$this->assertIsIndex( $index );

		$idSerialization = $propertyIdProvider->getPropertyId()->getSerialization();

		if ( !isset( $this->byPropertyId[$idSerialization] ) ) {
			throw new OutOfBoundsException( 'There is no group for property id ' . $idSerialization );
		}

		$oldIndex = array_search( $propertyIdProvider, $this->byPropertyId[$idSerialization] );

		if ( $oldIndex === false ) {
			throw new OutOfBoundsException( 'The property id provider does not exist in this map' );
		}

		if ( $index === null ) {
			$index = count( $this->byPropertyId[$idSerialization] );
		}

		array_splice( $this->byPropertyId[$idSerialization], $oldIndex, 1 );
		array_splice( $this->byPropertyId[$idSerialization], $index, 0, array( $propertyIdProvider ) );

		$this->byPropertyId[$idSerialization] = array_values( $this->byPropertyId[$idSerialization] );
	}

	/**
	 * @param PropertyIdProvider $propertyIdProvider
	 * @param int|null $index
	 *
	 * @throws InvalidArgumentException
	 */
	public function addElementAtIndex( PropertyIdProvider $propertyIdProvider, $index ) {
		$this->assertIsIndex( $index );

		$idSerialization = $propertyIdProvider->getPropertyId()->getSerialization();

		if ( !isset( $this->byPropertyId[$idSerialization] ) ) {
			$this->byPropertyId[$idSerialization] = array();
			$this->propertyIds[] = $propertyIdProvider->getPropertyId();
		}

		if ( $index === null ) {
			$index = count( $this->byPropertyId[$idSerialization] );
		}

		array_splice( $this->byPropertyId[$idSerialization], $index, 0, array( $propertyIdProvider ) );

		$this->byPropertyId[$idSerialization] = array_values( $this->byPropertyId[$idSerialization] );
	}

	private function assertIsIndex( $index ) {
		if ( ( !is_int( $index ) || $index < 0 ) && $index !== null ) {
			throw new InvalidArgumentException( '$index must be a non-negative integer or null' );
		}
	}

	/**
	 * @return PropertyIdProvider[]
	 */
	public function getFlatArray() {
		$propertyIdProviders = array();

		foreach ( $this->propertyIds as $propertyId ) {
			foreach ( $this->byPropertyId[$propertyId->getSerialization()] as $propertyIdProvider ) {
				$propertyIdProviders[] = $propertyIdProvider;
			}
		}

		return $propertyIdProviders;
	}

}
