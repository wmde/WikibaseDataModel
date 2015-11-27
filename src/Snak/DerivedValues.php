<?php

namespace Wikibase\DataModel\Snak;

use DataValues\DataValue;
use InvalidArgumentException;

/**
 * Value objects for managing a set of derived DataValues.
 * This is intended to be used as a facet object attached to a PropertyValueSnak.
 *
 * @since 5.0
 *
 * @licence GNU GPL v2+
 * @author Adam Shorland
 * @author Daniel Kinzler
 */
class DerivedValues {

	/**
	 * @var DataValue[]
	 */
	private $derivedDataValues = array();

	/**
	 * @param DataValue[] $derivedDataValues
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		array $derivedDataValues
	) {
		foreach ( $derivedDataValues as $key => $extensionDataValue ) {
			if ( !( $extensionDataValue instanceof DataValue ) || !is_string( $key ) ) {
				throw new InvalidArgumentException(
					'$derivedDataValues must be an array of DataValue objects with string keys'
				);
			}
		}

		$this->derivedDataValues = $derivedDataValues;
	}

	/**
	 * @return DataValue[] with string keys
	 */
	public function getDerivedDataValues() {
		return $this->derivedDataValues;
	}

	/**
	 * @param string $key
	 *
	 * @return DataValue|null
	 */
	public function getDerivedDataValue( $key ) {
		if ( isset( $this->derivedDataValues[$key] ) ) {
			return $this->derivedDataValues[$key];
		}

		return null;
	}

}
