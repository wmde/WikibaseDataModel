<?php

namespace Wikibase\DataModel\Claim;

use Comparable;
use Hashable;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Snak\Snaks;

/**
 * Class that represents a single Wikibase claim.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Statements
 *
 * @since 0.4 (as 'ClaimObject' and interface 'Claim' since 0.1)
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Claim implements Hashable, Comparable {

	/**
	 * Rank enum. Higher values are more preferred.
	 *
	 * @since 0.1
	 */
	const RANK_TRUTH = 3;
	const RANK_PREFERRED = 2;
	const RANK_NORMAL = 1;
	const RANK_DEPRECATED = 0;

	/**
	 * @since 0.1
	 *
	 * @var Snak
	 */
	private $mainSnak;

	/**
	 * The property snaks that are qualifiers for this claim.
	 *
	 * @since 0.1
	 *
	 * @var Snaks
	 */
	private $qualifiers;

	/**
	 * @since 0.2
	 *
	 * @var string|null
	 */
	private $guid = null;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Snak $mainSnak
	 * @param null|Snaks $qualifiers
	 */
	public function __construct( Snak $mainSnak, Snaks $qualifiers = null ) {
		$this->mainSnak = $mainSnak;
		$this->qualifiers = $qualifiers === null ? new SnakList() : $qualifiers;
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
	 * @see Hashable::getHash
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHash() {
		return sha1(
			$this->mainSnak->getHash()
				. $this->qualifiers->getHash()
		);
	}

	/**
	 * Returns the id of the property of the main snak.
	 * Short for ->getMainSnak()->getPropertyId()
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
			throw new InvalidArgumentException( 'Can only set the GUID to string values or null' );
		}

		$this->guid = $guid;
	}

	/**
	 * Gets the rank of the claim.
	 * The rank is an element of the Claim::RANK_ enum.
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getRank() {
		return self::RANK_TRUTH;
	}

	/**
	 * Returns a list of all Snaks on this Claim. This includes at least the main snak,
	 * and the snaks from qualifiers.
	 *
	 * This is a convenience method for use in code that needs to operate on all snaks, e.g.
	 * to find all referenced Entities.
	 *
	 * @return Snak[]
	 */
	public function getAllSnaks() {
		$snaks = array();

		$snaks[] = $this->getMainSnak();
		$snaks = array_merge( $snaks, iterator_to_array( $this->getQualifiers() ) );

		return $snaks;
	}

	/**
	 * @see Comparable::equals
	 *
	 * @since 0.7.4
	 *
	 * @param mixed $target
	 *
	 * @return boolean
	 */
	public function equals( $target ) {
		if ( !( $target instanceof self ) || $target instanceof Statement ) {
			return false;
		}

		return $this->claimFieldsEqual( $target );
	}

	private function claimFieldsEqual( Claim $target ) {
		return $this->guid === $target->guid
			&& $this->mainSnak->equals( $target->mainSnak )
			&& $this->qualifiers->equals( $target->qualifiers );
	}

}
