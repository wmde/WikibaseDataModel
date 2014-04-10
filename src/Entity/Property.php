<?php

namespace Wikibase\DataModel\Entity;

use DataValues\Deserializers\DataValueDeserializer;
use InvalidArgumentException;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\InternalSerialization\DeserializerFactory;

/**
 * Represents a single Wikibase property.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Properties
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Property extends Entity {

	const ENTITY_TYPE = 'property';

	/**
	 * @var string
	 */
	private $dataTypeId;

	/**
	 * @since 0.8
	 *
	 * @param PropertyId|null $id
	 * @param Fingerprint $fingerprint
	 * @param string $dataTypeId
	 */
	public function __construct( PropertyId $id = null, Fingerprint $fingerprint, $dataTypeId ) {
		$this->id = $id;
		$this->fingerprint = $fingerprint;
		$this->setDataTypeId( $dataTypeId );
	}

	/**
	 * @since 0.4
	 *
	 * @param string $dataTypeId
	 *
	 * @throws InvalidArgumentException
	 */
	public function setDataTypeId( $dataTypeId ) {
		if ( !is_string( $dataTypeId ) ) {
			throw new InvalidArgumentException( '$dataTypeId needs to be a string' );
		}

		$this->dataTypeId = $dataTypeId;
	}

	/**
	 * @since 0.4
	 *
	 * @return string
	 */
	public function getDataTypeId() {
		return $this->dataTypeId;
	}

	/**
	 * @see Entity::getType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getType() {
		return self::ENTITY_TYPE;
	}

	/**
	 * @see Entity::newFromArray
	 *
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return Property
	 */
	public static function newFromArray( array $data ) {
		return self::getDeserializer()->deserialize( $data );
	}

	private static function getDeserializer() {
		$deserializerFactory = new DeserializerFactory(
			new DataValueDeserializer( $GLOBALS['evilDataValueMap'] ),
			new BasicEntityIdParser()
		);

		return $deserializerFactory->newEntityDeserializer();
	}

	/**
	 * @since 0.3
	 *
	 * @param string $dataTypeId
	 *
	 * @return Property
	 */
	public static function newFromType( $dataTypeId ) {
		return new self(
			null,
			Fingerprint::newEmpty(),
			$dataTypeId
		);
	}

	/**
	 * @since 0.5
	 *
	 * @param string $idSerialization
	 *
	 * @return EntityId
	 */
	protected function idFromSerialization( $idSerialization ) {
		return new PropertyId( $idSerialization );
	}

}
