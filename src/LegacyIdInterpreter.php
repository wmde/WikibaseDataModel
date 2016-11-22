<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Turns legacy entity id serializations consisting of entity type + numeric id
 * into present day EntityId implementations.
 *
 * New usages of this class should be very carefully considered.
 * This class is internal to DataModel and should not be used by other components.
 *
 * @since 1.0
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LegacyIdInterpreter {

	/**
	 * @param string $entityType
	 * @param int|float|string $numericId
	 * @param string $repositoryName, defaults to an empty string (local repository)
	 *
	 * @return EntityId
	 * @throws InvalidArgumentException
	 */
	public static function newIdFromTypeAndNumber( $entityType, $numericId, $repositoryName = '' ) {
		if ( $entityType === 'item' ) {
			return ItemId::newFromNumber( $numericId, $repositoryName );
		} elseif ( $entityType === 'property' ) {
			return PropertyId::newFromNumber( $numericId, $repositoryName );
		}

		throw new InvalidArgumentException( 'Invalid entityType ' . $entityType );
	}

}
