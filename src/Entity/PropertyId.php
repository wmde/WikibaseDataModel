<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;
use RuntimeException;

/**
 * @since 0.5
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PropertyId extends EntityId implements Int32EntityId {

	/**
	 * @since 0.5
	 */
	const PATTERN = '/^P[1-9]\d{0,9}\z/i';

	/**
	 * @param string $idSerialization
	 * @param string $repositoryName
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $idSerialization, $repositoryName = '' ) {
		if ( !is_string( $idSerialization ) ) {
			throw new InvalidArgumentException( '$idSerialization must be a string' );
		}

		$parts = explode( ':', $idSerialization );
		$localId = end( $parts );
		$this->assertValidIdFormat( $localId );
		$parts[count( $parts ) - 1] = strtoupper( $parts[count( $parts ) - 1] );

		$this->serialization = implode( ':', $parts );
		$this->repositoryName = $repositoryName;
	}

	private function assertValidIdFormat( $localId ) {
		if ( !preg_match( self::PATTERN, $localId ) ) {
			throw new InvalidArgumentException( '$idSerialization must match ' . self::PATTERN );
		}

		if ( strlen( $localId ) > 10
			&& substr( $localId, 1 ) > Int32EntityId::MAX
		) {
			throw new InvalidArgumentException( '$idSerialization can not exceed '
				. Int32EntityId::MAX );
		}
	}

	/**
	 * @return int
	 *
	 * @throws RuntimeException if called on a foreign ID.
	 */
	public function getNumericId() {
		if ( $this->isForeign() ) {
			throw new RuntimeException( 'getNumericId must not be called on foreign PropertyIds' );
		}

		return (int)substr( $this->getSerialization(), 1 );
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
		$data = [ 'property', $this->serialization ];

		if ( $this->isForeign() ) {
			$data[] = $this->repositoryName;
		}

		return json_encode( $data );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		list( , $this->serialization, $this->repositoryName ) = array_merge( json_decode( $serialized ), [ '' ] );
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
