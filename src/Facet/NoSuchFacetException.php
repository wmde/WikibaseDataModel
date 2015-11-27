<?php

namespace Wikibase\DataModel\Facet;

use RuntimeException;

/**
 * Exception thrown when there is no facet with the name requested by the consumer code.
 *
 * @since 5.0
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class NoSuchFacetException extends RuntimeException {

	/**
	 * @param string $facetName
	 */
	public function __construct( $facetName ) {
		parent::__construct( "No facet object defined for name `$facetName`." );
	}

}
