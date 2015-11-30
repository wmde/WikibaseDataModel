<?php

namespace Wikibase\DataModel\Internal;

use Wikibase\DataModel\Facet\FacetContainer;
use Wikibase\DataModel\Facet\MismatchingFacetException;
use Wikibase\DataModel\Facet\NoSuchFacetException;

/**
 * Helper object for managing facet objects.
 *
 * @todo This should perhaps be a trait.
 *
 * @since 5.0
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class FacetManager implements FacetContainer {

	/**
	 * @var object[] Facet objects, by name.
	 */
	private $facets = array();

	/**
	 * @see FacetContainer::hasFacet
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasFacet( $name ) {
		return isset( $this->facets[$name] );
	}

	/**
	 * @see FacetContainer::listFacets
	 *
	 * @return string[]
	 */
	public function listFacets() {
		return array_keys( $this->facets );
	}

	/**
	 * @see FacetContainer::getFacet
	 *
	 * @param string $name
	 * @param string|null $type The desired type
	 *
	 * @return object
	 */
	public function getFacet( $name, $type = null ) {
		if ( !isset( $this->facets[$name] ) ) {
			throw new NoSuchFacetException( $name );
		}

		$facet = $this->facets[$name];

		// TODO: if $facet is callable, call it, and replace $this->facets[$name] with the result.

		if ( $type !== null && !is_a( $facet, $type ) ) {
			throw new MismatchingFacetException( $name, $type );
		}

		return $facet;
	}

	/**
	 * @see FacetContainer::addFacet
	 *
	 * @param string $name
	 * @param object $facetObject
	 */
	public function addFacet( $name, $facetObject ) {
		$this->facets[$name] = $facetObject;
	}

}
