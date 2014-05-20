<?php

namespace Wikibase\DataModel\Claim;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\References;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\Snaks;

/**
 * Class representing a Wikibase statement.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Statements
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Statement {

	/**
	 * @var Claim
	 */
	private $claim;

	/**
	 * @var References
	 */
	private $references;

	/**
	 * @var integer, element of the Claim::RANK_ enum
	 */
	private $rank;

	/**
	 * @since 1.0
	 *
	 * @param Claim $claim
	 * @param References $references
	 * @param integer $rank Element of the Claim::RANK_ enum
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( Claim $claim, References $references = null, $rank = Claim::RANK_NORMAL ) {
		$this->claim = $claim;
		$this->references = $references === null ? new ReferenceList() : $references;
		$this->setRank( $rank );
	}

	/**
	 * Returns the references attached to this statement.
	 *
	 * @since 0.1
	 *
	 * @return References
	 */
	public function getReferences() {
		return $this->references;
	}

	/**
	 * Sets the references attached to this statement.
	 *
	 * @since 0.1
	 *
	 * @param References $references
	 */
	public function setReferences( References $references ) {
		$this->references = $references;
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
	 * @since 1.0
	 *
	 * @param Claim $claim
	 */
	public function setClaim( Claim $claim ) {
		$this->claim = $claim;
	}

	/**
	 * Sets the rank of the statement.
	 * The rank is an element of the Claim::RANK_ enum, excluding RANK_TRUTH.
	 *
	 * @since 0.1
	 *
	 * @param integer $rank
	 * @throws InvalidArgumentException
	 */
	public function setRank( $rank ) {
		$ranks = array( Claim::RANK_DEPRECATED, Claim::RANK_NORMAL, Claim::RANK_PREFERRED );

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
			$snaks = array_merge( $snaks, iterator_to_array( $reference->getSnaks() ) );
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
		if ( !( $target instanceof self ) ) {
			return false;
		}

		return $this->claim->equals( $target->claim )
			&& $this->references->equals( $target->getReferences() );
	}

	/**
	 * @return PropertyId
	 */
	public function getPropertyId() {
		return $this->claim->getPropertyId();
	}

}
