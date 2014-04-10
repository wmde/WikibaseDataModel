<?php

namespace Wikibase\DataModel\Term;

/**
 * A collection of texts in a common language.
 *
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface LanguageTexts extends HasLanguage {

	/**
	 * @return string[]
	 */
	public function getTexts();

	/**
	 * @return boolean
	 */
	public function isEmpty();

}