<?php

declare( strict_types = 1 );

namespace Wikibase\DataModel\Term;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

/**
 * Unordered list of AliasGroup objects.
 * Only one group per language code. If multiple groups with the same language code
 * are provided, only the last one will be retained.
 *
 * Empty groups are not stored.
 *
 * @since 0.7.3
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AliasGroupList implements Countable, IteratorAggregate {

	/**
	 * @var AliasGroup[]
	 */
	private array $groups = [];

	/**
	 * @param AliasGroup[] $aliasGroups
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $aliasGroups = [] ) {
		foreach ( $aliasGroups as $aliasGroup ) {
			if ( !( $aliasGroup instanceof AliasGroup ) ) {
				throw new InvalidArgumentException( 'Every element in $aliasGroups must be an instance of AliasGroup' );
			}

			$this->setGroup( $aliasGroup );
		}
	}

	/**
	 * @see Countable::count
	 */
	public function count(): int {
		return count( $this->groups );
	}

	/**
	 * @see IteratorAggregate::getIterator
	 * @return Traversable<AliasGroup>
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->groups );
	}

	/**
	 * The array keys are the language codes of their associated AliasGroup.
	 *
	 * @since 2.3
	 *
	 * @return AliasGroup[] Array indexed by language code.
	 */
	public function toArray(): array {
		return $this->groups;
	}

	/**
	 * @throws OutOfBoundsException
	 */
	public function getByLanguage( string $languageCode ): AliasGroup {
		if ( !array_key_exists( $languageCode, $this->groups ) ) {
			throw new OutOfBoundsException( 'AliasGroup with languageCode "' . $languageCode . '" not found' );
		}

		return $this->groups[$languageCode];
	}

	/**
	 * @since 2.5
	 *
	 * @param string[] $languageCodes
	 */
	public function getWithLanguages( array $languageCodes ): self {
		return new self( array_intersect_key( $this->groups, array_flip( $languageCodes ) ) );
	}

	public function removeByLanguage( string $languageCode ): void {
		unset( $this->groups[$languageCode] );
	}

	/**
	 * If the group is empty, it will not be stored.
	 * In case the language of that group had an associated group, that group will be removed.
	 */
	public function setGroup( AliasGroup $group ): void {
		if ( $group->isEmpty() ) {
			unset( $this->groups[$group->getLanguageCode()] );
		} else {
			$this->groups[$group->getLanguageCode()] = $group;
		}
	}

	/**
	 * @since 0.7.4
	 */
	public function equals( mixed $target ): bool {
		if ( $this === $target ) {
			return true;
		}

		if ( !( $target instanceof self )
			|| $this->count() !== $target->count()
		) {
			return false;
		}

		foreach ( $this->groups as $group ) {
			if ( !$target->hasAliasGroup( $group ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @since 2.4.0
	 */
	public function isEmpty(): bool {
		return $this->groups === [];
	}

	/**
	 * @since 0.7.4
	 */
	public function hasAliasGroup( AliasGroup $group ): bool {
		return array_key_exists( $group->getLanguageCode(), $this->groups )
			&& $this->groups[$group->getLanguageCode()]->equals( $group );
	}

	/**
	 * @since 0.8
	 */
	public function hasGroupForLanguage( string $languageCode ): bool {
		return array_key_exists( $languageCode, $this->groups );
	}

	/**
	 * @since 0.8
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function setAliasesForLanguage( string $languageCode, array $aliases ): void {
		$this->setGroup( new AliasGroup( $languageCode, $aliases ) );
	}

	/**
	 * Returns an array with language codes as keys the aliases as array values.
	 *
	 * @since 2.5
	 *
	 * @return array[]
	 */
	public function toTextArray(): array {
		$array = [];

		foreach ( $this->groups as $group ) {
			$array[$group->getLanguageCode()] = $group->getAliases();
		}

		return $array;
	}

	/**
	 * Removes all alias groups from this list.
	 *
	 * @since 7.0
	 */
	public function clear(): void {
		$this->groups = [];
	}

}
