<?php

namespace Wikibase\DataModel\Term;

/**
 * @deprecated since 3.0.0, use EntityTermsProvider instead, will be dropped in 4.0.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface FingerprintProvider {

	/**
	 * @deprecated since 3.0.0, use getEntityTerms instead, will be dropped in 4.0.0
	 *
	 * @return Fingerprint
	 */
	public function getFingerprint();

}
