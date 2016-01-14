<?php

namespace Wikibase\DataModel\Term;

use InvalidArgumentException;

/**
 * Value object representing information about the application of language fallback.
 * Intended as a facet object to be attached to a Term object.
 *
 * @since 5.0
 *
 * @licence GNU GPL v2+
 * @author Jan Zerebecki < jan.wikimedia@zerebecki.de >
 * @author Daniel Kinzler
 */
class LanguageFallbackInfo {

	/**
	 * @var string Actual language of the text.
	 */
	private $actualLanguageCode;

	/**
	 * @var string|null Source language if the text is a transliteration.
	 */
	private $sourceLanguageCode;

	/**
	 * @param string $actualLanguageCode Actual language of the text.
	 * @param string|null $sourceLanguageCode Source language if the text is a transliteration.
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $actualLanguageCode, $sourceLanguageCode ) {
		if ( !is_string( $actualLanguageCode ) || $actualLanguageCode === '' ) {
			throw new InvalidArgumentException( '$actualLanguageCode must be a non-empty string' );
		}

		if ( !( $sourceLanguageCode === null
			|| ( is_string( $sourceLanguageCode ) && $sourceLanguageCode !== '' )
		) ) {
			throw new InvalidArgumentException( '$sourceLanguageCode must be a non-empty string or null' );
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
	 * @return string|null
	 */
	public function getSourceLanguageCode() {
		return $this->sourceLanguageCode;
	}

}
