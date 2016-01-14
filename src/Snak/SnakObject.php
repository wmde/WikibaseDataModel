<?php

namespace Wikibase\DataModel\Snak;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Facet\FacetContainer;
use Wikibase\DataModel\Facet\NoSuchFacetException;
use Wikibase\DataModel\Internal\FacetManager;

/**
 * Base class for snaks.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#Snaks
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
abstract class SnakObject implements Snak, FacetContainer {

	/**
	 * @var FacetManager|null
	 */
	private $facetManager = null;

	/**
	 * @since 0.1
	 *
	 * @var PropertyId
	 */
	protected $propertyId;

	/**
	 * Support for passing in an EntityId instance that is not a PropertyId instance has
	 * been deprecated since 0.5.
	 *
	 * @since 0.1
	 *
	 * @param PropertyId|EntityId|int $propertyId
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $propertyId ) {
		if ( is_int( $propertyId ) ) {
			$propertyId = PropertyId::newFromNumber( $propertyId );
		}

		if ( !( $propertyId instanceof EntityId ) ) {
			throw new InvalidArgumentException( '$propertyId must be an instance of EntityId' );
		}

		if ( $propertyId->getEntityType() !== Property::ENTITY_TYPE ) {
			throw new InvalidArgumentException( '$propertyId must have an entityType of ' . Property::ENTITY_TYPE );
		}

		if ( !( $propertyId instanceof PropertyId ) ) {
			$propertyId = new PropertyId( $propertyId->getSerialization() );
		}

		$this->propertyId = $propertyId;
	}

	/**
	 * @see PropertyIdProvider::getPropertyId
	 *
	 * @since 0.1
	 *
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->propertyId;
	}

	/**
	 * @see Hashable::getHash
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
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return is_object( $target )
			&& get_called_class() === get_class( $target )
			&& $this->getHash() === $target->getHash();
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( $this->propertyId->getNumericId() );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.1
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		$this->propertyId = PropertyId::newFromNumber( unserialize( $serialized ) );
	}

	/**
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasFacet( $name ) {
		return $this->facetManager && $this->facetManager->hasFacet( $name );
	}

	/**
	 * @return string[]
	 */
	public function listFacets() {
		return $this->facetManager ? $this->facetManager->listFacets() : array();
	}

	/**
	 * @param string $name
	 * @param string|null $type The desired type
	 *
	 * @return object
	 */
	public function getFacet( $name, $type = null ) {
		if ( !$this->facetManager ) {
			throw new NoSuchFacetException( $name );
		}

		return $this->facetManager->getFacet( $name, $type );
	}

	/**
	 * @param string $name
	 * @param object $facetObject
	 */
	public function addFacet( $name, $facetObject ) {
		if ( !$this->facetManager ) {
			$this->facetManager = new FacetManager();
		}

		$this->facetManager->addFacet( $name, $facetObject );
	}

}
