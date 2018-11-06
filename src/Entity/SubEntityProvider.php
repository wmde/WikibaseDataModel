<?php


namespace Wikibase\DataModel\Entity;

/**
 * Common interface for classes (typically entities) that may contain subentities.
 *
 * This only provides a way to enumerate the subentities;
 * there is no generic mechanism for modifying them.
 *
 * @license GPL-2.0-or-later
 */
interface SubEntityProvider {

	/**
	 * Returns the subentities of this object,
	 * not necessarily in any particular order.
	 * Note that the returned list may be empty.
	 *
	 * @return EntityDocument[]
	 */
	public function getSubEntitities(): array;

}
