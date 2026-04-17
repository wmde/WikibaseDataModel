<?php

declare( strict_types = 1 );

namespace Wikibase\DataModel\Term;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * Unordered list of Term objects.
 * If multiple terms with the same language code are provided, only the last one will be retained.
 * Empty terms are skipped and treated as non-existing.
 *
 * @since 0.7.3
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermList implements Countable, IteratorAggregate {

	/**
	 * @var Term[]
	 */
	private array $terms = [];

	/**
	 * @param iterable|Term[] $terms Can be a non-array since 8.1
	 * @throws InvalidArgumentException
	 */
	public function __construct( iterable $terms = [] ) {
		$this->addAll( $terms );
	}

	/**
	 * @see Countable::count
	 */
	public function count(): int {
		return count( $this->terms );
	}

	/**
	 * Returns an array with language codes as keys and the term text as values.
	 *
	 * @return string[]
	 */
	public function toTextArray(): array {
		$array = [];

		foreach ( $this->terms as $term ) {
			$array[$term->getLanguageCode()] = $term->getText();
		}

		return $array;
	}

	/**
	 * @see IteratorAggregate::getIterator
	 * @return ArrayIterator<Term>
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->terms );
	}

	/**
	 * @throws OutOfBoundsException
	 */
	public function getByLanguage( string $languageCode ): Term {
		if ( !array_key_exists( $languageCode, $this->terms ) ) {
			throw new OutOfBoundsException( 'Term with languageCode "' . $languageCode . '" not found' );
		}

		return $this->terms[$languageCode];
	}

	/**
	 * @since 2.5
	 *
	 * @param string[] $languageCodes
	 */
	public function getWithLanguages( array $languageCodes ): self {
		return new self( array_intersect_key( $this->terms, array_flip( $languageCodes ) ) );
	}

	public function removeByLanguage( string $languageCode ): void {
		unset( $this->terms[$languageCode] );
	}

	public function hasTermForLanguage( string $languageCode ): bool {
		return array_key_exists( $languageCode, $this->terms );
	}

	/**
	 * Replaces non-empty or removes empty terms.
	 */
	public function setTerm( Term $term ): void {
		if ( $term->getText() === '' ) {
			unset( $this->terms[$term->getLanguageCode()] );
		} else {
			$this->terms[$term->getLanguageCode()] = $term;
		}
	}

	/**
	 * @since 0.8
	 */
	public function setTextForLanguage( string $languageCode, string $termText ): void {
		$this->setTerm( new Term( $languageCode, $termText ) );
	}

	/**
	 * @since 0.7.4
	 */
	public function equals( mixed $target ): bool {
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
	 */
	public function isEmpty(): bool {
		return $this->terms === [];
	}

	/**
	 * @since 0.7.4
	 */
	public function hasTerm( Term $term ): bool {
		return array_key_exists( $term->getLanguageCode(), $this->terms )
			&& $this->terms[$term->getLanguageCode()]->equals( $term );
	}

	/**
	 * Removes all terms from this list.
	 *
	 * @since 7.0
	 */
	public function clear(): void {
		$this->terms = [];
	}

	/**
	 * @since 8.1
	 *
	 * @param iterable|Term[] $terms
	 * @throws InvalidArgumentException
	 */
	public function addAll( iterable $terms ): void {
		foreach ( $terms as $term ) {
			if ( !( $term instanceof Term ) ) {
				throw new InvalidArgumentException( 'Every element in $terms must be an instance of Term' );
			}

			$this->setTerm( $term );
		}
	}

}
