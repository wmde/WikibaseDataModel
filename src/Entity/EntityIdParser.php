<?php

declare( strict_types = 1 );

namespace Wikibase\DataModel\Entity;

/**
 * Interface for objects that can parse strings into EntityIds
 *
 * @since 4.2
 *
 * @license GPL-2.0-or-later
 * @author Addshore
 */
interface EntityIdParser {

	/**
	 * @throws EntityIdParsingException
	 */
	public function parse( string $idSerialization ): EntityId;

}
