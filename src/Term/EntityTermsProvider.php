<?php

namespace Wikibase\DataModel\Term;

use InvalidArgumentException;

/**
 * Interface for classes that contain EntityTerms.
 *
 * @since 3.1.0
 *
 * @license GNU GPL v2+
 * @author Thiemo Mättig
 */
interface EntityTermsProvider {

	/**
	 * @param string $languageCode
	 *
	 * @throws InvalidArgumentException when the language code is invalid.
	 * @return EntityTerms
	 */
	public function getEntityTerms( $languageCode );

}
