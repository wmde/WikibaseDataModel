<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;
use RuntimeException;
use Wikibase\DataModel\Assert\RepositoryNameAssert;

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
	 * @throws RuntimeException if called on a foreign ID.
	 * @return int Guaranteed to be a distinct integer in the range [1..2147483647].
	 */
	public function getNumericId() {
		if ( $this->isForeign() ) {
			throw new RuntimeException( 'getNumericId must not be called on foreign PropertyIds' );
		}

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
	 * @param string $repositoryName, defaults to an empty string (local repository)
	 *
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromNumber( $numericId, $repositoryName = '' ) {
		if ( !is_numeric( $numericId ) ) {
			throw new InvalidArgumentException( '$numericId must be numeric' );
		}
		RepositoryNameAssert::assertParameterIsValidRepositoryName( $repositoryName, '$repositoryName' );

		return new self( EntityId::joinSerialization( [ $repositoryName, '', 'P' . $numericId ] ) );
	}

}
