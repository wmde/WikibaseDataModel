<?php

namespace Wikibase\DataModel\Snak;

use DataValues\DataValue;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Class representing a property value snak.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#PropertyValueSnak
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
class PropertyValueSnak implements Snak {

	/**
	 * @var PropertyId
	 */
	private $propertyId;

	/**
	 * @var DataValue
	 */
	private $dataValue;

	/**
	 * Support for passing in an EntityId instance that is not a PropertyId instance has
	 * been deprecated since 0.5.
	 *
	 * @since 0.1
	 *
	 * @param PropertyId|EntityId|integer $propertyId
	 * @param DataValue $dataValue
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $propertyId, DataValue $dataValue ) {
		if ( is_integer( $propertyId ) ) {
			$propertyId = PropertyId::newFromNumber( $propertyId );
		}

		if ( !( $propertyId instanceof EntityId ) ) {
			throw new InvalidArgumentException( '$propertyId should be a PropertyId' );
		}

		if ( $propertyId->getEntityType() !== 'property' ) {
			throw new InvalidArgumentException(
				'The $propertyId of a property snak can only be an ID of a Property object'
			);
		}

		if ( !( $propertyId instanceof PropertyId ) ) {
			$propertyId = new PropertyId( $propertyId->getSerialization() );
		}

		$this->propertyId = $propertyId;
		$this->dataValue = $dataValue;
	}

	/**
	 * @see Snak::getPropertyId
	 *
	 * @since 0.1
	 *
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->propertyId;
	}

	/**
	 * @see Snak::getHash
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHash() {
		return sha1( serialize( $this ) );
	}

	/**
	 * @see Comparable::equals
	 *
	 * @since 0.3
	 *
	 * @param mixed $target
	 *
	 * @return boolean
	 */
	public function equals( $target ) {
		if ( is_object( $target ) && ( $target instanceof Snak ) ) {
			return $this->getHash() === $target->getHash();
		}

		return false;
	}

	/**
	 * Returns the value of the property value snak.
	 *
	 * @since 0.1
	 *
	 * @return DataValue
	 */
	public function getDataValue() {
		return $this->dataValue;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( array( $this->propertyId->getNumericId(), $this->dataValue ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.1
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		list( $numericId, $dataValue ) = unserialize( $serialized );
		$this->__construct( $numericId, $dataValue );
	}

	/**
	 * @see Snak::getType
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	public function getType() {
		return 'value';
	}

}
