<?php

namespace Wikibase\DataModel\Facet;

/**
 * Interface for attaching and accessing facets (aka "roles objects" or "extensions").
 *
 * This interface provides the basis of an implementation of the Role Object Pattern, see
 * <http://c2.com/cgi/wiki?RoleObjectPattern>.
 *
 * @since 5.0
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
interface FacetContainer {

	/**
	 * Determines whether the given facet is supported.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasFacet( $name );

	/**
	 * Returns a list of supported facet names.
	 *
	 * @return string[]
	 */
	public function listFacets();

	/**
	 * Returns a facet object.
	 *
	 * @param string $name
	 * @param string|null $type The desired type
	 *
	 * @throws NoSuchFacetException if no facet object is found for $name
	 * @throws MismatchingFacetException if the facet object is not compatible with $type
	 * @return object
	 */
	public function getFacet( $name, $type = null );

	/**
	 * Adds a facet object. Any facet object previously registered under the same name
	 * is discarded.
	 *
	 * @param string $name
	 * @param object $facetObject The facet object
	 */
	public function addFacet( $name, $facetObject );

}
