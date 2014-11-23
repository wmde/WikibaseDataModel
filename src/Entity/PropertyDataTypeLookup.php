<?php

namespace Wikibase\DataModel\Entity;

use Wikibase\DataModel\InterfaceInterface;

/**
 * @since 1.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface PropertyDataTypeLookup extends InterfaceInterface {

	/**
	 * Returns the data type for the Property of which the id is given.
	 *
	 * @param PropertyId $propertyId
	 *
	 * @return string
	 * @throws PropertyNotFoundException
	 */
	public function getDataTypeIdForProperty( PropertyId $propertyId );

}
