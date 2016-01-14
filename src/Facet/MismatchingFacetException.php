<?php

namespace Wikibase\DataModel\Facet;

use RuntimeException;

/**
 * Exception thrown when the concrete type of the facet object does not match the type
 * requested by a consumer.
 *
 * @since 5.0
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class MismatchingFacetException extends RuntimeException {

	/**
	 * @param string $facetName
	 */
	public function __construct( $facetName, $expectedType ) {
		parent::__construct( "Facet `$facetName` is not compatible with `$expectedType`." );
	}

}
