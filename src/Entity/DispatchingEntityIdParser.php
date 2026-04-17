<?php

declare( strict_types = 1 );

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;

/**
 * @since 4.2
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DispatchingEntityIdParser implements EntityIdParser {

	/**
	 * @var callable[]
	 */
	private array $idBuilders;

	/**
	 * Takes an array in which each key is a preg_match pattern.
	 * The first pattern the id matches against will be picked.
	 * The value this key points to has to be a builder function
	 * that takes as only required argument the id serialization
	 * (string) and returns an EntityId instance.
	 *
	 * @param callable[] $idBuilders
	 */
	public function __construct( array $idBuilders ) {
		$this->idBuilders = $idBuilders;
	}

	/**
	 * @throws EntityIdParsingException
	 */
	public function parse( string $idSerialization ): EntityId {
		if ( $this->idBuilders === [] ) {
			throw new EntityIdParsingException( 'No id builders are configured' );
		}

		foreach ( $this->idBuilders as $idPattern => $idBuilder ) {
			if ( preg_match( $idPattern, $idSerialization ) ) {
				return $this->buildId( $idBuilder, $idSerialization );
			}
		}

		throw new EntityIdParsingException(
			"The serialization \"$idSerialization\" is not recognized by the configured id builders"
		);
	}

	/**
	 * @throws EntityIdParsingException
	 */
	private function buildId( callable $idBuilder, string $idSerialization ): EntityId {
		try {
			return $idBuilder( $idSerialization );
		} catch ( InvalidArgumentException $ex ) {
			// Should not happen, but if it does, re-throw the original message
			throw new EntityIdParsingException( $ex->getMessage(), 0, $ex );
		}
	}

}
