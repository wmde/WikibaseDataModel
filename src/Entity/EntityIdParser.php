<?php

namespace Wikibase\DataModel\Entity;

use Wikibase\DataModel\InterfaceInterface;

/**
 * Interface for objects that can parse strings into EntityIds
 *
 * @since 0.5
 *
 * @licence GNU GPL v2+
 * @author Adam Shorland
 */
interface EntityIdParser extends InterfaceInterface {

	/**
	 * @since 0.5
	 *
	 * @param string $entityId
	 *
	 * @return EntityId
	 * @throws EntityIdParsingException
	 */
	public function parse( $entityId );

}