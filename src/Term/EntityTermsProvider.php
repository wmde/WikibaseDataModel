<?php

namespace Wikibase\DataModel\Term;

/**
 * @since 3.0.0
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
interface EntityTermsProvider {

	/**
	 * @since 3.0.0
	 *
	 * @return EntityTerms
	 */
	public function getEntityTerms();

}
