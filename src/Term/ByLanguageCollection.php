<?php

namespace Wikibase\DataModel\Term;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

/**
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class ByLanguageCollection implements Countable, IteratorAggregate {

	/**
	 * @var HasLanguage[]
	 */
	protected $byLanguageIdentifiables = array();

	/**
	 * @see Countable::count
	 * @return int
	 */
	public function count() {
		return count( $this->byLanguageIdentifiables );
	}

	/**
	 * @see IteratorAggregate::getIterator
	 * @return Traversable
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->byLanguageIdentifiables );
	}

	/**
	 * @param string $languageCode
	 *
	 * @return OrderedLanguageTextsSet
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function getByLanguage( $languageCode ) {
		$this->assertIsLanguageCode( $languageCode );

		if ( !array_key_exists( $languageCode, $this->byLanguageIdentifiables ) ) {
			throw new OutOfBoundsException(
				'There is no entry with language code "' . $languageCode . '" in the list'
			);
		}

		return $this->byLanguageIdentifiables[$languageCode];
	}

	/**
	 * @param string $languageCode
	 * @throws InvalidArgumentException
	 */
	public function removeByLanguage( $languageCode ) {
		$this->assertIsLanguageCode( $languageCode );
		unset( $this->byLanguageIdentifiables[$languageCode] );
	}

	protected function assertIsLanguageCode( $languageCode ) {
		if ( !is_string( $languageCode ) ) {
			throw new InvalidArgumentException( '$languageCode should be a string' );
		}
	}

}