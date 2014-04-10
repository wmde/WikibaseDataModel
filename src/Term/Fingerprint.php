<?php

namespace Wikibase\DataModel\Term;

/**
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Fingerprint {

	/**
	 * @return Fingerprint
	 */
	public static function newEmpty() {
		return new self(
			new TermList( array() ),
			new TermList( array() ),
			new LanguageTextsList( array() )
		);
	}

	private $labels;
	private $descriptions;
	private $aliases;

	public function __construct( TermList $labels, TermList $descriptions, LanguageTextsList $aliases ) {
		$this->labels = $labels;
		$this->descriptions = $descriptions;
		$this->aliases = $aliases;
	}

	/**
	 * @return TermList
	 */
	public function getLabels() {
		return $this->labels;
	}

	/**
	 * @return TermList
	 */
	public function getDescriptions() {
		return $this->descriptions;
	}

	/**
	 * @return LanguageTextsList
	 */
	public function getAliases() {
		return $this->aliases;
	}

}
