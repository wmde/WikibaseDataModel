<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;

/**
 * Base class for parsers for HierarchicalEntityIds.
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
abstract class HierarchicalEntityIdParser implements EntityIdParser {

	/**
	 * @var EntityIdParser
	 */
	private $baseIdParser;

	/**
	 * HierarchicalEntityIdParser constructor.
	 *
	 * @param EntityIdParser $baseIdParser
	 */
	public function __construct( EntityIdParser $baseIdParser ) {
		$this->baseIdParser = $baseIdParser;
	}

	/**
	 * @param string $idSerialization
	 *
	 * @throws EntityIdParsingException
	 * @return ItemId
	 */
	public function parse( $idSerialization ) {
		try {
			list( $basePart, $relativePart )
				= HierarchicalEntityId::splitHierarchicalSerialization( $idSerialization );

			$base = $this->baseIdParser->parse( $basePart );
			return $this->newEntityId( $base, $relativePart );
		} catch ( InvalidArgumentException $ex ) {
			throw new EntityIdParsingException( $ex->getMessage(), 0, $ex );
		}
	}

	protected abstract function newEntityId( EntityId $base, $relativePart );

}
