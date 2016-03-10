<?php

/**
 * Entry point for the Wikibase DataModel component.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

define( 'WIKIBASE_DATAMODEL_VERSION', '5.1.0' );

if ( defined( 'MEDIAWIKI' ) && function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'WikibaseDataModel', __DIR__ . '/mediawiki-extension.json' );
}
