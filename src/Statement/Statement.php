<?php

namespace Wikibase\DataModel\Statement;

use InvalidArgumentException;
use Wikibase\DataModel\Claim\Claim;
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
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class Statement extends Claim {

	/**
	 * @var References
	 */
	private $references;

	/**
	 * @var integer, element of the Claim::RANK_ enum
	 */
	private $rank = self::RANK_NORMAL;

	/**
	 * Parameters as of 2.0:
	 * - Claim $claim
	 * - References|null $references
	 *
	 * Alternate parameters, deprecated since 2.0:
	 * - Snak $mainSnak
	 * - Snaks|null $qualifiers
	 * - References|null $references
	 */
	public function __construct( /* $arguments */ ) {
		$arguments = func_get_args();

		if ( reset( $arguments ) instanceof Claim ) {
			call_user_func_array( array( $this, 'initFromClaim' ), $arguments );
		} else {
			call_user_func_array( array( $this, 'initFromSnaks' ), $arguments );
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param Claim $claim
	 * @param References $references
	 *
	 * @return Statement
	 */
	public static function newFromClaim( Claim $claim, References $references = null ) {
		return new self( $claim, $references );
	}

	/**
	 * @since 2.0
	 *
	 * @param Snak $mainSnak
	 *
	 * @return Statement
	 */
	public static function newFromMainSnak( Snak $mainSnak ) {
		return new self( new Claim( $mainSnak ) );
	}

	private function initFromClaim( Claim $claim, References $references = null ) {
		$this->setClaim( $claim );
		$this->references = $references === null ? new ReferenceList() : $references;
	}

	private function initFromSnaks( Snak $mainSnak, Snaks $qualifiers = null, References $references = null ) {
		$this->initFromClaim( new Claim( $mainSnak, $qualifiers ), $references );
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
	 * Sets the rank of the statement.
	 * The rank is an element of the Claim::RANK_ enum, excluding RANK_TRUTH.
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
				parent::getHash(),
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
		$snaks = parent::getAllSnaks();

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
		if ( !( $target instanceof self ) ) {
			return false;
		}

		return $this->claimFieldsEqual( $target )
			&& $this->references->equals( $target->references );
	}

	/**
	 * @since 1.1
	 *
	 * @param Claim $claim
	 */
	public function setClaim( Claim $claim ) {
		$this->mainSnak = $claim->getMainSnak();
		$this->qualifiers = $claim->getQualifiers();
		$this->guid = $claim->getGuid();
	}

	/**
	 * @since 1.0
	 *
	 * @return Claim
	 */
	public function getClaim() {
		return $this;
	}

}
