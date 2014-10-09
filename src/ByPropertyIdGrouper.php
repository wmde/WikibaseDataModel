<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use OutOfBoundsException;
use Traversable;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Groups property id providers by their property id.
 *
 * @since 1.1
 *
 * @license GNU GpL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ByPropertyIdGrouper {

	/**
	 * @var PropertyIdProvider[][]
	 */
	private $byPropertyId;

	/**
	 * @param PropertyIdProvider[] $propertyIdProviders
	 * @throws InvalidArgumentException
	 */
	public function __construct( $propertyIdProviders ) {
		$this->assertArePropertyIdProviders( $propertyIdProviders );
		$this->indexPropertyIdProviders( $propertyIdProviders );
	}

	private function assertArePropertyIdProviders( $propertyIdProviders ) {
		if ( !is_array( $propertyIdProviders ) && !( $propertyIdProviders instanceof Traversable ) ) {
			throw new InvalidArgumentException( '$propertyIdProviders should be an array or a Traversable' );
		}

		foreach ( $propertyIdProviders as $propertyIdProvider ) {
			if ( !( $propertyIdProvider instanceof PropertyIdProvider ) ) {
				throw new InvalidArgumentException( 'All elements need implement PropertyIdProvider' );
			}
		}
	}

	private function indexPropertyIdProviders( $propertyIdProviders ) {
		$this->byPropertyId = array();

		foreach ( $propertyIdProviders as $propertyIdProvider ) {
			$this->addPropertyIdProvider( $propertyIdProvider );
		}
	}

	private function addPropertyIdProvider( PropertyIdProvider $propertyIdProvider ) {
		$idSerialization = $propertyIdProvider->getPropertyId()->getSerialization();

		if ( isset( $this->byPropertyId[$idSerialization] ) ) {
			$this->byPropertyId[$idSerialization][] = $propertyIdProvider;
		} else {
			$this->byPropertyId[$idSerialization] = array( $propertyIdProvider );
		}
	}

	/**
	 * Returns all PropertyId instances which were found.
	 *
	 * @since 1.1
	 *
	 * @return PropertyId[]
	 */
	public function getPropertyIds() {
		$propertyIds = array_keys( $this->byPropertyId );

		array_walk( $propertyIds, function( &$propertyId ) {
			$propertyId = new PropertyId( $propertyId );
		} );

		return $propertyIds;
	}

	/**
	 * Returns the PropertyIdProvider instances for the given PropertyId.
	 *
	 * @since 1.1
	 *
	 * @param PropertyId $propertyId
	 * @return PropertyIdProvider[]
	 * @throws OutOfBoundsException
	 */
	public function getByPropertyId( PropertyId $propertyId ) {
		$idSerialization = $propertyId->getSerialization();

		if ( !isset( $this->byPropertyId[$idSerialization] ) ) {
			throw new OutOfBoundsException( 'Could not find the given PropertyId.' );
		}

		return $this->byPropertyId[$idSerialization];
	}

	/**
	 * Checks if there are PropertyIdProvider instances for the given PropertyId.
	 *
	 * @since 1.1
	 *
	 * @param PropertyId $propertyId
	 * @return boolean
	 */
	public function hasPropertyId( PropertyId $propertyId ) {
		return isset( $this->byPropertyId[$propertyId->getSerialization()] );
	}

}
