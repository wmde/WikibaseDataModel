<?php

namespace Wikibase\DataModel\Claim;

use InvalidArgumentException;
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
class Statement extends Claim {

	/**
	 * @since 0.1
	 *
	 * @var References
	 */
	protected $references;

	/**
	 * @since 0.1
	 *
	 * @var integer, element of the Claim::RANK_ enum
	 */
	protected $rank = self::RANK_NORMAL;

	/**
	 * @since 0.1
	 *
	 * @param Snak $mainSnak
	 * @param Snaks|null $qualifiers
	 * @param References|null $references
	 */
	public function __construct( Snak $mainSnak, Snaks $qualifiers = null, References $references = null ) {
		parent::__construct( $mainSnak, $qualifiers );
		$this->references = $references === null ? new ReferenceList() : $references;
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
	 * @see Claim::toArray
	 *
	 * @since 0.3
	 *
	 * @return array
	 */
	public function toArray() {
		$data = parent::toArray();

		$data['rank'] = $this->rank;
		$data['refs'] = $this->references->toArray();

		return $data;
	}

	/**
	 * Constructs a new Statement from an array in the same format as Claim::toArray returns.
	 *
	 * @since 0.3
	 * @deprecated since 0.7.3
	 *
	 * @param array $data
	 *
	 * @return Statement
	 */
	public static function newFromArray( array $data ) {
		$rank = $data['rank'];
		unset( $data['rank'] );

		/**
		 * @var Statement $statement
		 */
		$statement = parent::newFromArray( $data );

		$statement->setRank( $rank );
		$statement->setReferences( ReferenceList::newFromArray( $data['refs'] ) );

		return $statement;
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.3
	 *
	 * @param string $serialization
	 *
	 * @return Statement
	 */
	public function unserialize( $serialization ) {
		$instance = static::newFromArray( json_decode( $serialization, true ) );

		$this->setMainSnak( $instance->getMainSnak() );
		$this->setQualifiers( $instance->getQualifiers() );
		$this->setGuid( $instance->getGuid() );
		$this->setRank( $instance->getRank() );
		$this->setReferences( $instance->getReferences() );
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
			$snaks = array_merge( $snaks, iterator_to_array( $reference->getSnaks() ) );
		}

		return $snaks;
	}
}
