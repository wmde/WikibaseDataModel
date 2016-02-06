<?php

namespace Wikibase\DataModel\Entity;

use Comparable;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\FingerprintHolder;
use Wikibase\DataModel\Term\TermList;

/**
 * Represents a single Wikibase entity.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#Values
 *
 * @deprecated since 1.0 - do not type hint against Entity. See
 * https://lists.wikimedia.org/pipermail/wikidata-tech/2014-June/000489.html
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class Entity implements Comparable, FingerprintHolder, EntityDocument {

	/**
	 * Sets the value for the label in a certain value.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string $value
	 */
	public function setLabel( $languageCode, $value ) {
		$this->getFingerprint()->setLabel( $languageCode, $value );
	}

	/**
	 * Sets the value for the description in a certain value.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string $value
	 */
	public function setDescription( $languageCode, $value ) {
		$this->getFingerprint()->setDescription( $languageCode, $value );
	}

	/**
	 * Removes the labels in the specified languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 */
	public function removeLabel( $languageCode ) {
		$this->getFingerprint()->removeLabel( $languageCode );
	}

	/**
	 * Removes the descriptions in the specified languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 */
	public function removeDescription( $languageCode ) {
		$this->getFingerprint()->removeDescription( $languageCode );
	}

	/**
	 * Returns the aliases for the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string[]
	 */
	public function getAliases( $languageCode ) {
		$aliases = $this->getFingerprint()->getAliasGroups();

		if ( $aliases->hasGroupForLanguage( $languageCode ) ) {
			return $aliases->getByLanguage( $languageCode )->getAliases();
		}

		return array();
	}

	/**
	 * Returns all the aliases for the item.
	 * The result is an array with language codes pointing to an array of aliases in the language they specify.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[]|null $languageCodes
	 *
	 * @return array[]
	 */
	public function getAllAliases( array $languageCodes = null ) {
		$aliases = $this->getFingerprint()->getAliasGroups();

		$textLists = array();

		/**
		 * @var AliasGroup $aliasGroup
		 */
		foreach ( $aliases as $languageCode => $aliasGroup ) {
			if ( $languageCodes === null || in_array( $languageCode, $languageCodes ) ) {
				$textLists[$languageCode] = $aliasGroup->getAliases();
			}
		}

		return $textLists;
	}

	/**
	 * Sets the aliases for the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function setAliases( $languageCode, array $aliases ) {
		$this->getFingerprint()->setAliasGroup( $languageCode, $aliases );
	}

	/**
	 * Add the provided aliases to the aliases list of the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function addAliases( $languageCode, array $aliases ) {
		$this->setAliases(
			$languageCode,
			array_merge(
				$this->getAliases( $languageCode ),
				$aliases
			)
		);
	}

	/**
	 * Removed the provided aliases from the aliases list of the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function removeAliases( $languageCode, array $aliases ) {
		$this->setAliases(
			$languageCode,
			array_diff(
				$this->getAliases( $languageCode ),
				$aliases
			)
		);
	}

	/**
	 * Returns the description of the entity in the language with the provided code,
	 * or false in cases there is none in this language.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string|bool
	 */
	public function getDescription( $languageCode ) {
		if ( !$this->getFingerprint()->hasDescription( $languageCode ) ) {
			return false;
		}

		return $this->getFingerprint()->getDescription( $languageCode )->getText();
	}

	/**
	 * Returns the label of the entity in the language with the provided code,
	 * or false in cases there is none in this language.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string|bool
	 */
	public function getLabel( $languageCode ) {
		if ( !$this->getFingerprint()->hasLabel( $languageCode ) ) {
			return false;
		}

		return $this->getFingerprint()->getLabel( $languageCode )->getText();
	}

	/**
	 * Replaces the currently set aliases with the provided ones.
	 * The aliases are provided as an associative array where the keys are
	 * language codes pointing to an array value that holds the aliases
	 * in that language.
	 *
	 * @since 0.4
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param array[] $aliasLists
	 */
	public function setAllAliases( array $aliasLists ) {
		$this->getFingerprint()->setAliasGroups( new AliasGroupList() );

		foreach ( $aliasLists as $languageCode => $aliasList ) {
			$this->setAliases( $languageCode, $aliasList );
		}
	}

	/**
	 * Returns a deep copy of the entity.
	 *
	 * @since 0.1
	 *
	 * @return self
	 */
	public function copy() {
		return unserialize( serialize( $this ) );
	}

	/**
	 * Removes all content from the Entity.
	 * The id is not part of the content.
	 *
	 * @since 0.1
	 * @deprecated since 1.0
	 */
	public abstract function clear();

}
