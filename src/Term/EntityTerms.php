<?php

namespace Wikibase\DataModel\Term;

use Comparable;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * @since 0.7.3
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
class EntityTerms implements Comparable {

	/**
	 * @deprecated since 2.5, use new EntityTerms() instead.
	 *
	 * @return EntityTerms
	 */
	public static function newEmpty() {
		return new self();
	}

	/**
	 * @var TermList
	 */
	private $labels;

	/**
	 * @var TermList
	 */
	private $descriptions;

	/**
	 * @var AliasGroupList
	 */
	private $aliasGroups;

	/**
	 * @param TermList|null $labels
	 * @param TermList|null $descriptions
	 * @param AliasGroupList|null $aliasGroups
	 */
	public function __construct(
		TermList $labels = null,
		TermList $descriptions = null,
		AliasGroupList $aliasGroups = null
	) {
		$this->labels = $labels ?: new TermList();
		$this->descriptions = $descriptions ?: new TermList();
		$this->aliasGroups = $aliasGroups ?: new AliasGroupList();
	}

	/**
	 * @since 0.7.3
	 *
	 * @return TermList
	 */
	public function getLabels() {
		return $this->labels;
	}

	/**
	 * @since 0.9
	 *
	 * @param string $languageCode
	 *
	 * @return boolean
	 */
	public function hasLabel( $languageCode ) {
		return $this->labels->hasTermForLanguage( $languageCode );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param string $languageCode
	 *
	 * @return Term
	 * @throws OutOfBoundsException
	 * @throws InvalidArgumentException
	 */
	public function getLabel( $languageCode ) {
		return $this->labels->getByLanguage( $languageCode );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $languageCode
	 * @param string $labelText
	 *
	 * @throws InvalidArgumentException
	 */
	public function setLabel( $languageCode, $labelText ) {
		$this->labels->setTerm( new Term( $languageCode, $labelText ) );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param string $languageCode
	 */
	public function removeLabel( $languageCode ) {
		$this->labels->removeByLanguage( $languageCode );
	}

	/**
	 * @since 0.7.3
	 *
	 * @return TermList
	 */
	public function getDescriptions() {
		return $this->descriptions;
	}

	/**
	 * @since 0.9
	 *
	 * @param string $languageCode
	 *
	 * @return boolean
	 */
	public function hasDescription( $languageCode ) {
		return $this->descriptions->hasTermForLanguage( $languageCode );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param string $languageCode
	 *
	 * @return Term
	 * @throws OutOfBoundsException
	 * @throws InvalidArgumentException
	 */
	public function getDescription( $languageCode ) {
		return $this->descriptions->getByLanguage( $languageCode );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $languageCode
	 * @param string $descriptionText
	 *
	 * @throws InvalidArgumentException
	 */
	public function setDescription( $languageCode, $descriptionText ) {
		$this->descriptions->setTerm( new Term( $languageCode, $descriptionText ) );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param string $languageCode
	 */
	public function removeDescription( $languageCode ) {
		$this->descriptions->removeByLanguage( $languageCode );
	}

	/**
	 * @since 0.7.4
	 *
	 * @return AliasGroupList
	 */
	public function getAliasGroups() {
		return $this->aliasGroups;
	}

	/**
	 * @since 0.9
	 *
	 * @param string $languageCode
	 *
	 * @return boolean
	 */
	public function hasAliasGroup( $languageCode ) {
		return $this->aliasGroups->hasGroupForLanguage( $languageCode );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param string $languageCode
	 *
	 * @return AliasGroup
	 * @throws OutOfBoundsException
	 * @throws InvalidArgumentException
	 */
	public function getAliasGroup( $languageCode ) {
		return $this->aliasGroups->getByLanguage( $languageCode );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 *
	 * @throws InvalidArgumentException
	 */
	public function setAliasGroup( $languageCode, array $aliases ) {
		$this->aliasGroups->setGroup( new AliasGroup( $languageCode, $aliases ) );
	}

	/**
	 * @since 0.7.4
	 *
	 * @param string $languageCode
	 */
	public function removeAliasGroup( $languageCode ) {
		$this->aliasGroups->removeByLanguage( $languageCode );
	}

	/**
	 * @see Comparable::equals
	 *
	 * @since 0.7.4
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
			&& $this->descriptions->equals( $target->getDescriptions() )
			&& $this->labels->equals( $target->getLabels() )
			&& $this->aliasGroups->equals( $target->getAliasGroups() );
	}

	/**
	 * @since 0.7.4
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return $this->labels->isEmpty()
			&& $this->descriptions->isEmpty()
			&& $this->aliasGroups->isEmpty();
	}

	/**
	 * @since 0.7.4
	 *
	 * @param TermList $labels
	 */
	public function setLabels( TermList $labels ) {
		$this->labels = $labels;
	}

	/**
	 * @since 0.7.4
	 *
	 * @param TermList $descriptions
	 */
	public function setDescriptions( TermList $descriptions ) {
		$this->descriptions = $descriptions;
	}

	/**
	 * @since 0.7.4
	 *
	 * @param AliasGroupList $groups
	 */
	public function setAliasGroups( AliasGroupList $groups ) {
		$this->aliasGroups = $groups;
	}

}
