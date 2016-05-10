<?php

namespace Wikibase\DataModel\Entity;

/**
 * Interface for EntityIds that can be converted to numbers, and back from the entity type and the
 * number. Entity types that do not meet this criteria (e.g. when page titles or file names are used
 * as IDs) should not implement this interface. Never return a fallback value like 0 that's the same
 * for different IDs!
 *
 * Entity types are not required and not guaranteed to implement this interface. Use the full string
 * serialization whenever you can and avoid using numeric IDs.
 *
 * @since 6.1
 *
 * @see EntityId::getSerialization
 */
interface NumericEntityId {

	/**
	 * @since 6.1
	 *
	 * @return int|float Numeric representation of the EntityId as a positive integer number in the
	 *  [1..PHP_INT_MAX] range. A float when exceeding this range (e.g. when exceeding 2,147,483,647
	 *  on a 32 bit system).
	 */
	public function getNumericId();

}
