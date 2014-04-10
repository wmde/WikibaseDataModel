<?php

namespace Wikibase\DataModel\Term;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

/**
 * If multiple terms with the same language code are provided, only the last one will be retained.
 *
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermList extends ByLanguageCollection {

	/**
	 * @param Term[] $terms
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $terms ) {
		foreach ( $terms as $term ) {
			if ( !( $term instanceof Term ) ) {
				throw new InvalidArgumentException( 'TermList can only contain instances of Term' );
			}

			$this->byLanguageIdentifiables[$term->getLanguageCode()] = $term;
		}
	}

	public function hasTermForLanguage( $languageCode ) {
		$this->assertIsLanguageCode( $languageCode );
		return array_key_exists( $languageCode, $this->byLanguageIdentifiables );
	}

	public function setTerm( Term $term ) {
		$this->byLanguageIdentifiables[$term->getLanguageCode()] = $term;
	}

	/**
	 * Returns an array with language codes as keys and the term text as values.
	 *
	 * @return string[]
	 */
	public function toTextArray() {
		$array = array();

		/**
		 * @var Term $term
		 */
		foreach ( $this->byLanguageIdentifiables as $term ) {
			$array[$term->getLanguageCode()] = $term->getText();
		}

		return $array;
	}

}
