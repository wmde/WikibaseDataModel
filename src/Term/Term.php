<?php

namespace Wikibase\DataModel\Term;

use Comparable;
use InvalidArgumentException;

/**
 * Immutable value object.
 *
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Term implements Comparable {

	/**
	 * @var string Usually the requested and/or actual language of the text.
	 */
	private $languageCode;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @param string $languageCode Usually the requested and/or actual language of the text.
	 * @param string $text
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $languageCode, $text ) {
		if ( !is_string( $languageCode ) || $languageCode === '' ) {
			throw new InvalidArgumentException( '$languageCode must be a non-empty string' );
		}

		if ( !is_string( $text ) ) {
			throw new InvalidArgumentException( '$text must be a string' );
		}

		$this->languageCode = $languageCode;
		$this->text = $text;
	}

	/**
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->languageCode;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @see Comparable::equals
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->languageCode === $target->languageCode
			&& $this->text === $target->text;
	}

}
