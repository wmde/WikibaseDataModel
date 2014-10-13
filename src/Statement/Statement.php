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

/**
 * Class representing a Wikibase statement.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Statements
 *
 * @since 0.1
 * Does not inherit from Claim anymore since 3.0
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
	const RANK_PREFERRED = Claim::RANK_PREFERRED;
	const RANK_NORMAL = Claim::RANK_NORMAL;
	const RANK_DEPRECATED = Claim::RANK_DEPRECATED;

	/**
	 * @var Claim
	 */
	private $claim;

	/**
	 * @var ReferenceList
	 */
	private $references;

	/**
	 * @var integer, element of the Statement::RANK_ enum
	 */
	private $rank = self::RANK_NORMAL;

	/**
	 * @since 0.1
	 *
	 * @param Claim $claim
	 * @param ReferenceList|null $references
	 */
	public function __construct( Claim $claim, ReferenceList $references = null ) {
		$this->claim = $claim;
		$this->references = $references === null ? new ReferenceList() : $references;
	}

	/**
	 * @since 1.1
	 *
	 * @param Claim $claim
	 */
	public function setClaim( Claim $claim ) {
		$this->claim = $claim;
	}

	/**
	 * @since 1.0
	 *
	 * @return Claim
	 */
	public function getClaim() {
		return $this->claim;
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
	 * Gets the rank of the claim.
	 * The rank is an element of the Statement::RANK_ enum.
	 *
	 * @since 0.1
	 *
	 * @return integer
	 */
	public function getRank() {
		return $this->rank;
	}

	/**
	 * @since 0.2
	 *
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->claim->getPropertyId();
	}

	/**
	 * @see Claim::getMainSnak
	 *
	 * @since 0.1
	 * @deprecated since 3.0 - use getClaim instead
	 *
	 * @return Snak
	 */
	public function getMainSnak() {
		return $this->claim->getMainSnak();
	}

	/**
	 * @see Claim::setMainSnak
	 *
	 * @since 0.1
	 * @deprecated since 3.0 - use getClaim instead
	 *
	 * @param Snak $mainSnak
	 */
	public function setMainSnak( Snak $mainSnak ) {
		$this->claim->setMainSnak( $mainSnak );
	}

	/**
	 * @see Claim::getQualifiers
	 *
	 * @since 0.1
	 * @deprecated since 3.0 - use getClaim instead
	 *
	 * @return Snaks
	 */
	public function getQualifiers() {
		return $this->claim->getQualifiers();
	}

	/**
	 * @see Claim::setQualifiers
	 *
	 * @since 0.1
	 * @deprecated since 3.0 - use getClaim instead
	 *
	 * @param Snaks $propertySnaks
	 */
	public function setQualifiers( Snaks $propertySnaks ) {
		$this->claim->setQualifiers( $propertySnaks );
	}

	/**
	 * @see Claim::getGuid
	 *
	 * @since 0.2
	 *
	 * @return string|null
	 */
	public function getGuid() {
		return $this->claim->getGuid();
	}

	/**
	 * @see Claim::setGuid
	 *
	 * @since 0.2
	 *
	 * @param string|null $guid
	 *
	 * @throws InvalidArgumentException
	 */
	public function setGuid( $guid ) {
		$this->claim->setGuid( $guid );
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
				$this->claim->getHash(),
				$this->rank,
				$this->references->getValueHash(),
			)
		) );
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
		$snaks = $this->claim->getAllSnaks();

		/* @var Reference $reference */
		foreach( $this->getReferences() as $reference ) {
			$referenceSnaks = $reference->getSnaks();
			$snaks = array_merge( $snaks, iterator_to_array( $referenceSnaks ) );
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
	 * @return boolean
	 */
	public function equals( $target ) {
		if ( $target === $this ) {
			return true;
		}

		if ( !( $target instanceof self ) ) {
			return false;
		}

		return $this->claim->equals( $target->claim )
			&& $this->references->equals( $target->references )
			&& $this->rank === $target->rank;
	}

}
