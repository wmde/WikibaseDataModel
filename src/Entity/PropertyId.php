<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;

/**
 * @since 0.5
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PropertyId extends EntityId implements NumericEntityId {

	/**
	 * @since 0.5
	 */
	const PATTERN = '/^P[1-9]\d*\z/i';

	/**
	 * @param string $idSerialization
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $idSerialization ) {
		$this->assertValidIdFormat( $idSerialization );
		$this->serialization = strtoupper( $idSerialization );
	}

	private function assertValidIdFormat( $idSerialization ) {
		if ( !is_string( $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must be a string; got ' . gettype( $idSerialization ) );
		}

		if ( !preg_match( self::PATTERN, $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must match ' . self::PATTERN );
		}
	}

	/**
	 * @since 0.5
	 *
	 * @return int
	 */
	public function getNumericId() {
		return (int)substr( $this->serialization, 1 );
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
		return json_encode( array( 'property', $this->serialization ) );
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
