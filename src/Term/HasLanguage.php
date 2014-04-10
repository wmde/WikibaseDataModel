<?php

namespace Wikibase\DataModel\Term;

/**
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface HasLanguage {

	/**
	 * @return string
	 */
	public function getLanguageCode();

}