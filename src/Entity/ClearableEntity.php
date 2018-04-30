<?php

namespace Wikibase\DataModel\Entity;

/**
 * Interface for Entity objects that can be cleared.
 *
 * @since 7.4
 *
 * @license GPL-2.0+
 */
interface ClearableEntity {

	/**
	 * Clears all fields of the entity except for its EntityID
	 */
	public function clear();

}
