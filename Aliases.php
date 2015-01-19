<?php

// This is a IDE helper to understand class aliasing.
// It should not be included anywhere.
// Actual aliasing happens in the entry point using class_alias.

namespace { throw new Exception( 'This code is not meant to be executed' ); }

namespace Wikibase\DataModel\Entity\Diff {

	/**
	 * @deprecated since 3.0.0, use EntityTermsPatcher instead, will be dropped in 4.0.0
	 */
	class FingerprintPatcher extends EntityTermsPatcher {}
}

namespace Wikibase\DataModel\Term {

	/**
	 * @deprecated since 3.0.0, use EntityTerms instead, will be dropped in 4.0.0
	 */
	class Fingerprint extends EntityTerms {}
}
