<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;
use RuntimeException;

/**
 * @since 0.5
 *
 * @license GPL-2.0+
 */
class PropertyId extends EntityId implements Int32EntityId {

	/**
	 * @since 0.5
	 */
	const PATTERN = '/^P[1-9]\d{0,9}\z/i';

	/**
	 * @param string $idSerialization
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $idSerialization ) {
		$serializationParts = self::splitSerialization( $idSerialization );
		$localId = strtoupper( $serializationParts[2] );
		$this->assertValidIdFormat( $localId );
		parent::__construct( self::joinSerialization(
			[ $serializationParts[0], $serializationParts[1], $localId ] )
		);
	}

	private function assertValidIdFormat( $idSerialization ) {
		if ( !is_string( $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must be a string' );
		}

		if ( !preg_match( self::PATTERN, $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must match ' . self::PATTERN );
		}

		if ( strlen( $idSerialization ) > 10
			&& substr( $idSerialization, 1 ) > Int32EntityId::MAX
		) {
			throw new InvalidArgumentException( '$idSerialization can not exceed '
				. Int32EntityId::MAX );
		}
	}

	/**
	 * @see Int32EntityId::getNumericId
	 *
	 * @return int Guaranteed to be a distinct integer in the range [1..2147483647].
	 */
	public function getNumericId() {
		$serializationParts = self::splitSerialization( $this->serialization );
		return (int)substr( $serializationParts[2], 1 );
	}

	/**
	 * @return string
	 */
	public function getEntityType() {
		return 'property';
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return json_encode( [ 'property', $this->serialization ] );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		list( , $this->serialization ) = json_decode( $serialized );
	}

	/**
	 * Construct a PropertyId given the numeric part of its serialization.
	 *
	 * CAUTION: new usages of this method are discouraged. Typically you
	 * should avoid dealing with just the numeric part, and use the whole
	 * serialization. Not doing so in new code requires special justification.
	 *
	 * @param int|float|string $numericId
	 *
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromNumber( $numericId ) {
		if ( !is_numeric( $numericId ) ) {
			throw new InvalidArgumentException( '$numericId must be numeric' );
		}

		return new self( 'P' . $numericId );
	}

}
