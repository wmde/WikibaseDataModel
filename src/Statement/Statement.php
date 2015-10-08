<?php

namespace Wikibase\DataModel\Statement;

use Comparable;
use Hashable;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\PropertyIdProvider;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;

/**
 * Class representing a Wikibase statement.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#Statements
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class Statement implements Hashable, Comparable, PropertyIdProvider {

	/**
	 * @deprecated since 4.4, use StatementRank::PREFERRED instead.
	 */
	const RANK_PREFERRED = StatementRank::PREFERRED;

	/**
	 * @deprecated since 4.4, use StatementRank::NORMAL instead.
	 */
	const RANK_NORMAL = StatementRank::NORMAL;

	/**
	 * @deprecated since 4.4, use StatementRank::DEPRECATED instead.
	 */
	const RANK_DEPRECATED = StatementRank::DEPRECATED;

	/**
	 * @var string|null
	 */
	private $guid = null;

	/**
	 * @var Snak
	 */
	private $mainSnak;

	/**
	 * The property value snaks making up the qualifiers for this statement.
	 *
	 * @var SnakList
	 */
	private $qualifiers;

	/**
	 * @var ReferenceList
	 */
	private $references;

	/**
	 * @var int One of the StatementRank::... constants.
	 */
	private $rank = StatementRank::NORMAL;

	/**
	 * @since 2.0
	 *
	 * @param Snak $mainSnak
	 * @param SnakList|null $qualifiers
	 * @param ReferenceList|null $references
	 * @param string|null $guid
	 */
	public function __construct(
		Snak $mainSnak,
		SnakList $qualifiers = null,
		ReferenceList $references = null,
		$guid = null
	) {
		$this->mainSnak = $mainSnak;
		$this->qualifiers = $qualifiers ?: new SnakList();
		$this->references = $references ?: new ReferenceList();
		$this->setGuid( $guid );
	}

	/**
	 * @since 0.2
	 *
	 * @return string|null
	 */
	public function getGuid() {
		return $this->guid;
	}

	/**
	 * Sets the GUID of this statement.
	 *
	 * @since 0.2
	 *
	 * @param string|null $guid
	 *
	 * @throws InvalidArgumentException
	 */
	public function setGuid( $guid ) {
		if ( !is_string( $guid ) && $guid !== null ) {
			throw new InvalidArgumentException( '$guid must be a string or null' );
		}

		$this->guid = $guid;
	}

	/**
	 * Returns the main value snak of this statement.
	 *
	 * @since 0.1
	 *
	 * @return Snak
	 */
	public function getMainSnak() {
		return $this->mainSnak;
	}

	/**
	 * Sets the main value snak of this statement.
	 *
	 * @since 0.1
	 *
	 * @param Snak $mainSnak
	 */
	public function setMainSnak( Snak $mainSnak ) {
		$this->mainSnak = $mainSnak;
	}

	/**
	 * Returns the property value snaks making up the qualifiers for this statement.
	 *
	 * @since 0.1
	 *
	 * @return SnakList
	 */
	public function getQualifiers() {
		return $this->qualifiers;
	}

	/**
	 * Sets the property value snaks making up the qualifiers for this statement.
	 *
	 * @since 0.1
	 *
	 * @param SnakList $propertySnaks
	 */
	public function setQualifiers( SnakList $propertySnaks ) {
		$this->qualifiers = $propertySnaks;
	}

	/**
	 * Returns the references attached to this statement.
	 *
	 * @since 0.1
	 *
	 * @return ReferenceList
	 */
	public function getReferences() {
		return $this->references;
	}

	/**
	 * Sets the references attached to this statement.
	 *
	 * @since 0.1
	 *
	 * @param ReferenceList $references
	 */
	public function setReferences( ReferenceList $references ) {
		$this->references = $references;
	}

	/**
	 * @since 2.0
	 *
	 * @param Snak[]|Snak $snaks
	 * @param Snak [$snak2,...]
	 *
	 * @throws InvalidArgumentException
	 */
	public function addNewReference( $snaks = [] /*...*/ ) {
		if ( $snaks instanceof Snak ) {
			$snaks = func_get_args();
		}

		$this->references->addNewReference( $snaks );
	}

	/**
	 * Sets the rank of the statement.
	 *
	 * @since 0.1
	 *
	 * @param int $rank One of the StatementRank::... constants.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setRank( $rank ) {
		StatementRank::assertIsValid( $rank );

		$this->rank = $rank;
	}

	/**
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getRank() {
		return $this->rank;
	}

	/**
	 * @see Hashable::getHash
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHash() {
		return sha1( implode(
			'|',
			[
				sha1( $this->mainSnak->getHash() . $this->qualifiers->getHash() ),
				$this->rank,
				$this->references->getValueHash(),
			]
		) );
	}

	/**
	 * Returns the id of the property of the main snak.
	 * Short for ->getMainSnak()->getPropertyId()
	 *
	 * @see PropertyIdProvider::getPropertyId
	 *
	 * @since 0.2
	 *
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->getMainSnak()->getPropertyId();
	}

	/**
	 * Returns a list of all Snaks on this statement. This includes the main snak and all snaks
	 * from qualifiers and references.
	 *
	 * This is a convenience method for use in code that needs to operate on all snaks, e.g.
	 * to find all referenced Entities.
	 *
	 * @return Snak[]
	 */
	public function getAllSnaks() {
		$snaks = [ $this->mainSnak ];

		foreach ( $this->qualifiers as $qualifier ) {
			$snaks[] = $qualifier;
		}

		/* @var Reference $reference */
		foreach ( $this->getReferences() as $reference ) {
			foreach ( $reference->getSnaks() as $referenceSnak ) {
				$snaks[] = $referenceSnak;
			}
		}

		return $snaks;
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
			&& $this->guid === $target->guid
			&& $this->rank === $target->rank
			&& $this->mainSnak->equals( $target->mainSnak )
			&& $this->qualifiers->equals( $target->qualifiers )
			&& $this->references->equals( $target->references );
	}

	/**
	 * @see http://php.net/manual/en/language.oop5.cloning.php
	 *
	 * @since 5.1
	 */
	public function __clone() {
		$this->qualifiers = clone $this->qualifiers;
		$this->references = clone $this->references;
	}

}
