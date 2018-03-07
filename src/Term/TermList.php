<?php

namespace Wikibase\DataModel\Term;

use ArrayIterator;
use Comparable;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * Unordered list of Term objects.
 * If multiple terms with the same language code are provided, only the last one will be retained.
 * Empty terms are skipped and treated as non-existing.
 *
 * @since 0.7.3
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermList implements Countable, IteratorAggregate, Comparable {

	/**
	 * @var Term[]
	 */
	private $terms = [];

	/**
	 * @param Term[] $terms
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $terms = [] ) {
		foreach ( $terms as $term ) {
			if ( !( $term instanceof Term ) ) {
				throw new InvalidArgumentException( 'Every element in $terms must be an instance of Term' );
			}

			$this->setTerm( $term );
		}
	}

	/**
	 * @see Countable::count
	 * @return int
	 */
	public function count() {
		return count( $this->terms );
	}

	/**
	 * Returns an array with language codes as keys and the term text as values.
	 *
	 * @return string[]
	 */
	public function toTextArray() {
		$array = [];

		foreach ( $this->terms as $term ) {
			$array[$term->getLanguageCode()] = $term->getText();
		}

		return $array;
	}

	/**
	 * @see IteratorAggregate::getIterator
	 * @return Iterator|Term[]
	 */
	public function getIterator() {
		return new ArrayIterator( $this->terms );
	}

	/**
	 * @param string $languageCode
	 *
	 * @return Term
	 * @throws OutOfBoundsException
	 */
	public function getByLanguage( $languageCode ) {
		if ( !array_key_exists( $languageCode, $this->terms ) ) {
			throw new OutOfBoundsException( 'Term with languageCode "' . $languageCode . '" not found' );
		}

		return $this->terms[$languageCode];
	}

	/**
	 * @since 2.5
	 *
	 * @param string[] $languageCodes
	 *
	 * @return self
	 */
	public function getWithLanguages( array $languageCodes ) {
		return new self( array_intersect_key( $this->terms, array_flip( $languageCodes ) ) );
	}

	/**
	 * @param string $languageCode
	 */
	public function removeByLanguage( $languageCode ) {
		unset( $this->terms[$languageCode] );
	}

	/**
	 * @param string $languageCode
	 *
	 * @return bool
	 */
	public function hasTermForLanguage( $languageCode ) {
		return array_key_exists( $languageCode, $this->terms );
	}

	/**
	 * Replaces non-empty or removes empty terms.
	 *
	 * @param Term $term
	 */
	public function setTerm( Term $term ) {
		if ( $term->getText() === '' ) {
			unset( $this->terms[$term->getLanguageCode()] );
		} else {
			$this->terms[$term->getLanguageCode()] = $term;
		}
	}

	/**
	 * @since 0.8
	 *
	 * @param string $languageCode
	 * @param string $termText
	 */
	public function setTextForLanguage( $languageCode, $termText ) {
		$this->setTerm( new Term( $languageCode, $termText ) );
	}

	/**
	 * @see Comparable::equals
	 *
	 * @since 0.7.4
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		if ( !( $target instanceof self )
			|| $this->count() !== $target->count()
		) {
			return false;
		}

		foreach ( $this->terms as $term ) {
			if ( !$target->hasTerm( $term ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @since 2.4.0
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->terms );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param Term $term
	 *
	 * @return boolean
	 */
	public function hasTerm( Term $term ) {
		return array_key_exists( $term->getLanguageCode(), $this->terms )
			&& $this->terms[$term->getLanguageCode()]->equals( $term );
	}

	/**
	 * Removes all terms from this list.
	 *
	 * @since 7.0
	 */
	public function clear() {
		$this->terms = [];
	}

}
