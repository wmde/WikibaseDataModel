<?php

namespace Wikibase\DataModel\Term;

use Comparable;
use Countable;

/**
 * Immutable value object.
 *
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrderedLanguageTextsSet implements LanguageTexts, Comparable, Countable {

	private $languageCode;
	private $termTexts;

	/**
	 * @param string $languageCode
	 * @param string[] $termTexts
	 */
	public function __construct( $languageCode, array $termTexts ) {
		$this->languageCode = $languageCode;

		$this->termTexts = array_values(
			array_filter(
				array_unique(
					array_map(
						'trim',
						$termTexts
					)
				),
				function( $string ) {
					return $string !== '';
				}
			)
		);
	}

	/**
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->languageCode;
	}

	/**
	 * @return string[]
	 */
	public function getTexts() {
		return $this->termTexts;
	}

	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return empty( $this->termTexts );
	}

	/**
	 * @see Comparable::equals
	 *
	 * @param mixed $target
	 *
	 * @return boolean
	 */
	public function equals( $target ) {
		return $target instanceof OrderedLanguageTextsSet
			&& $this->languageCode === $target->getLanguageCode()
			&& $this->arraysAreEqual( $this->termTexts, $target->getTexts() );
	}

	private function arraysAreEqual( array $a, array $b ) {
		return array_diff( $a, $b ) === array();
	}

	/**
	 * @see Countable::count
	 * @return int
	 */
	public function count() {
		return count( $this->termTexts );
	}

}