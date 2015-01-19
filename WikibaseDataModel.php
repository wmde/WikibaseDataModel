<?php

/**
 * Entry point for the Wikibase DataModel component.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( defined( 'WIKIBASE_DATAMODEL_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'WIKIBASE_DATAMODEL_VERSION', '2.6.0 alpha' );

if ( defined( 'MEDIAWIKI' ) ) {
	call_user_func( function() {
		require_once __DIR__ . '/WikibaseDataModel.mw.php';
	} );
}

// Aliasing of classes that got renamed.
// For more details, see Aliases.php.

// Aliases introduced in 3.0.0
class_alias( 'Wikibase\DataModel\Entity\Diff\EntityTermsPatcher', 'Wikibase\DataModel\Entity\Diff\FingerprintPatcher' );
class_alias( 'Wikibase\DataModel\Term\EntityTerms', 'Wikibase\DataModel\Term\Fingerprint' );
