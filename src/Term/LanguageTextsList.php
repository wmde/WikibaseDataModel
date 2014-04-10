<?php

namespace Wikibase\DataModel\Term;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

/**
 * Only one collection per language code. If multiple groups with the same language code
 * are provided, only the last one will be retained.
 *
 * Empty groups are not stored.
 *
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LanguageTextsList extends ByLanguageCollection {

	/**
	 * @param LanguageTexts[] $languageTextsArray
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $languageTextsArray ) {
		foreach ( $languageTextsArray as $texts ) {
			if ( !( $texts instanceof LanguageTexts ) ) {
				throw new InvalidArgumentException( 'LanguageTextsList can only contain LanguageTexts instances' );
			}

			$this->setTexts( $texts );
		}
	}

	/**
	 * If the group is empty, it will not be stored.
	 * In case the language of that group had an associated group, that group will be removed.
	 *
	 * @param LanguageTexts $languageTexts
	 */
	public function setTexts( LanguageTexts $languageTexts ) {
		if ( $languageTexts->isEmpty() ) {
			unset( $this->byLanguageIdentifiables[$languageTexts->getLanguageCode()] );
		}
		else {
			$this->byLanguageIdentifiables[$languageTexts->getLanguageCode()] = $languageTexts;
		}
	}

}
