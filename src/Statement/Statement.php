<?php

namespace Wikibase\DataModel\Statement;

use Comparable;
use Hashable;
use InvalidArgumentException;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\PropertyIdProvider;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Snak\Snaks;

/**
 * Class representing a Wikibase statement.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#Statements
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class Statement implements Hashable, Comparable, PropertyIdProvider {

	/**
	 * Rank enum. Higher values are more preferred.
	 *
	 * @since 2.0
	 */
	const RANK_PREFERRED = 2;
	const RANK_NORMAL = 1;
	const RANK_DEPRECATED = 0;

	/** @deprecated since 2.0 */
	const RANK_TRUTH = 3;

	/**
	 * @var Snak
	 */
	private $mainSnak;

	/**
	 * The property snaks that are qualifiers for this claim.
	 *
	 * @var Snaks
	 */
	private $qualifiers;

	/**
	 * @var string|null
	 */
	private $guid = null;

	/**
	 * @var ReferenceList
	 */
	private $references;

	/**
	 * @var integer, element of the Statement::RANK_ enum
	 */
	private $rank = self::RANK_NORMAL;

	/**
	 * @since 2.0
	 *
	 * @param Snak $mainSnak
	 * @param Snaks|null $qualifiers
	 * @param ReferenceList|null $references
	 */
	public function __construct( Snak $mainSnak, Snaks $qualifiers = null, ReferenceList $references = null ) {
		$this->mainSnak = $mainSnak;
		$this->qualifiers = $qualifiers === null ? new SnakList() : $qualifiers;
		$this->references = $references === null ? new ReferenceList() : $references;
	}

	/**
	 * Returns the value snak.
	 *
	 * @since 0.1
	 *
	 * @return Snak
	 */
	public function getMainSnak() {
		return $this->mainSnak;
	}

	/**
	 * Sets the main snak.
	 *
	 * @since 0.1
	 *
	 * @param Snak $mainSnak
	 */
	public function setMainSnak( Snak $mainSnak ) {
		$this->mainSnak = $mainSnak;
	}

	/**
	 * Gets the property snaks making up the qualifiers for this claim.
	 *
	 * @since 0.1
	 *
	 * @return Snaks
	 */
	public function getQualifiers() {
		return $this->qualifiers;
	}

	/**
	 * Sets the property snaks making up the qualifiers for this claim.
	 *
	 * @since 0.1
	 *
	 * @param Snaks $propertySnaks
	 */
	public function setQualifiers( Snaks $propertySnaks ) {
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
	 * @param Snak $snak
	 *
	 * @throws InvalidArgumentException
	 */
	public function addNewReference( Snak $snak /* Snak, ... */ ) {
		$this->references->addReference( new Reference( new SnakList( func_get_args() ) ) );
	}

	/**
	 * Sets the rank of the statement.
	 * The rank is an element of the Statement::RANK_ enum.
	 *
	 * @since 0.1
	 *
	 * @param integer $rank
	 * @throws InvalidArgumentException
	 */
	public function setRank( $rank ) {
		$ranks = array( self::RANK_DEPRECATED, self::RANK_NORMAL, self::RANK_PREFERRED );

		if ( !in_array( $rank, $ranks, true ) ) {
			throw new InvalidArgumentException( 'Invalid rank specified for statement: ' . var_export( $rank, true ) );
		}

		$this->rank = $rank;
	}

	/**
	 * @see Claim::getRank
	 *
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
			array(
				sha1( $this->mainSnak->getHash() . $this->qualifiers->getHash() ),
				$this->rank,
				$this->references->getValueHash(),
			)
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
	 * Returns the GUID of the Claim.
	 *
	 * @since 0.2
	 *
	 * @return string|null
	 */
	public function getGuid() {
		return $this->guid;
	}

	/**
	 * Sets the GUID of the Claim.
	 *
	 * @since 0.2
	 *
	 * @param string|null $guid
	 *
	 * @throws InvalidArgumentException
	 */
	public function setGuid( $guid ) {
		if ( !is_string( $guid ) && $guid !== null ) {
			throw new InvalidArgumentException( '$guid must be a string or null; got ' . gettype( $guid ) );
		}

		$this->guid = $guid;
	}

	/**
	 * @see Claim::getAllSnaks.
	 *
	 * In addition to the Snaks returned by Claim::getAllSnaks(), this also includes all
	 * snaks from any References in this Statement.
	 *
	 * @return Snak[]
	 */
	public function getAllSnaks() {
		$snaks = array( $this->mainSnak );

		foreach( $this->qualifiers as $qualifier ) {
			$snaks[] = $qualifier;
		}

		/* @var Reference $reference */
		foreach( $this->getReferences() as $reference ) {
			foreach( $reference->getSnaks() as $referenceSnak ) {
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
			&& $this->claimFieldsEqual( $target )
			&& $this->rank === $target->getRank()
			&& $this->references->equals( $target->references );
	}

	private function claimFieldsEqual( Statement $target ) {
		return $this->guid === $target->guid
		&& $this->mainSnak->equals( $target->mainSnak )
		&& $this->qualifiers->equals( $target->qualifiers );
	}

}
