<?php

namespace Wikibase\DataModel\Term;

use Comparable;
use InvalidArgumentException;
use Wikibase\DataModel\Facet\FacetContainer;
use Wikibase\DataModel\Facet\NoSuchFacetException;
use Wikibase\DataModel\Internal\FacetManager;

/**
 * Immutable value object.
 *
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
class Term implements Comparable, FacetContainer {

	/**
	 * @var string Language code identifying the language of the text, but note that there is
	 * nothing this class can do to enforce this convention.
	 */
	private $languageCode;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @var FacetManager
	 */
	private $facetManager;

	/**
	 * @param string $languageCode Language of the text.
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

		return is_object( $target )
			&& get_called_class() === get_class( $target )
			&& $this->languageCode === $target->languageCode
			&& $this->text === $target->text;
	}

	/**
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasFacet( $name ) {
		return $this->facetManager && $this->facetManager->hasFacet( $name );
	}

	/**
	 * @return string[]
	 */
	public function listFacets() {
		return $this->facetManager ? $this->facetManager->listFacets() : array();
	}

	/**
	 * @param string $name
	 * @param string|null $type The desired type
	 *
	 * @return object
	 */
	public function getFacet( $name, $type = null ) {
		if ( !$this->facetManager ) {
			throw new NoSuchFacetException( $name );
		}

		return $this->facetManager->getFacet( $name, $type );
	}

	/**
	 * @param string $name
	 * @param object $facetObject
	 */
	public function addFacet( $name, $facetObject ) {
		if ( !$this->facetManager ) {
			$this->facetManager = new FacetManager();
		}

		$this->facetManager->addFacet( $name, $facetObject );
	}

}
