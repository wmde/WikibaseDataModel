<?php

namespace Wikibase\DataModel\Term;

use InvalidArgumentException;

/**
 * Immutable value object.
 *
 * @since 2.4.0
 *
 * @licence GNU GPL v2+
 * @author Jan Zerebecki < jan.wikimedia@zerebecki.de >
 */
class TermFallback extends Term {

	/**
	 * @var string Actual language of the text.
	 */
	private $actualLanguageCode;

	/**
	 * @var string|null Source language if the text is a transliteration.
	 */
	private $sourceLanguageCode;

	/**
	 * @param string $requestedLanguageCode Requested language, not necessarily the language of the
	 * text.
	 * @param string $text
	 * @param string $actualLanguageCode Actual language of the text which is the fall back.
	 * @param string|null $sourceLanguageCode Source language if the text is a transliteration,
	 *		null if no transformation was done.
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $requestedLanguageCode, $text, $actualLanguageCode, $sourceLanguageCode ) {
		parent::__construct( $requestedLanguageCode, $text );

		if ( !is_string( $actualLanguageCode ) || $actualLanguageCode === '' ) {
			throw new InvalidArgumentException( '$actualLanguageCode must be a non-empty string' );
		}

		if ( !( $sourceLanguageCode === null
			|| ( is_string( $sourceLanguageCode ) && $sourceLanguageCode !== '' )
		) ) {
			throw new InvalidArgumentException( '$sourceLanguageCode must be a non-empty string or null' );
		}

		if ( $sourceLanguageCode === null && $actualLanguageCode === $requestedLanguageCode ) {
			throw new InvalidArgumentException(
				'$actualLanguageCode and $requestedLanguageCode must be different when $sourceLanguageCode is null, '.
					'use Term without Fallback when no fall back did occur'
			);
		}

		if ( $actualLanguageCode === $sourceLanguageCode ) {
			throw new InvalidArgumentException(
				'$actualLanguageCode and $sourceLanguageCode must be different, '.
					'set $sourceLanguageCode to null when no transformation took place'
			);
		}

		if ( $requestedLanguageCode === $sourceLanguageCode ) {
			throw new InvalidArgumentException(
				'$requestedLanguageCode and $sourceLanguageCode must be different; '.
					'Transforming one language to itself makes no sense; Each variant, '.
					'that can be transformed or transliterated to, needs a different language code'
			);
		}

		$this->actualLanguageCode = $actualLanguageCode;
		$this->sourceLanguageCode = $sourceLanguageCode;
	}

	/**
	 * @return string
	 */
	public function getActualLanguageCode() {
		return $this->actualLanguageCode;
	}

	/**
	 * @return string
	 */
	public function getSourceLanguageCode() {
		return $this->sourceLanguageCode;
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
			&& parent::equals( $target )
			&& $this->actualLanguageCode === $target->actualLanguageCode
			&& $this->sourceLanguageCode === $target->sourceLanguageCode;
	}

}
