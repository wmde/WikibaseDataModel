<?php

namespace Wikibase\DataModel\Term;

use Comparable;
use InvalidArgumentException;

/**
 * Imutable value object.
 *
 * @since 3.1.0
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class EntityTerms implements Comparable {

	/**
	 * @var string
	 */
	private $languageCode;

	/**
	 * @var Term
	 */
	private $label;

	/**
	 * @var Term
	 */
	private $description;

	/**
	 * @var AliasGroup
	 */
	private $aliases;

	/**
	 * @param string $languageCode
	 * @param Term|null $label
	 * @param Term|null $description
	 * @param AliasGroup|null $aliases
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		$languageCode,
		Term $label = null,
		Term $description = null,
		AliasGroup $aliases = null
	) {
		if ( !is_string( $languageCode ) || $languageCode === '' ) {
			throw new InvalidArgumentException( '$languageCode must be a non-empty string' );
		}

		$this->languageCode = $languageCode;
		$this->label = $label ?: new Term( $languageCode, '' );
		$this->description = $description ?: new Term( $languageCode, '' );
		$this->aliases = $aliases ?: new AliasGroup( $languageCode );
	}

	/**
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->languageCode;
	}

	/**
	 * @return Term
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @return Term
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return AliasGroup
	 */
	public function getAliases() {
		return $this->aliases;
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
			&& $this->label->equals( $target->label )
			&& $this->description->equals( $target->description )
			&& $this->aliases->equals( $target->aliases );
	}

}
