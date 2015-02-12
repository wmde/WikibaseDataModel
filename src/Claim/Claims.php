<?php

namespace Wikibase\DataModel\Claim;

use ArrayObject;
use Comparable;
use Hashable;
use InvalidArgumentException;
use Traversable;
use Wikibase\DataModel\ByPropertyIdGrouper;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Statement\Statement;

/**
 * A claim (identified using it's GUID) can only be added once.
 *
 * @deprecated since 1.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 * @author H. Snater < mediawiki@snater.com >
 */
class Claims extends ArrayObject implements Hashable, Comparable {

	/**
	 * @see GenericArrayObject::__construct
	 *
	 * @since 0.3
	 *
	 * @param Claim[]|Traversable|null $input
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $input = null ) {
		parent::__construct( array() );

		if ( $input !== null ) {
			if ( !is_array( $input ) && !( $input instanceof Traversable ) ) {
				throw new InvalidArgumentException( '$input must be an array or an instance of Traversable' );
			}

			foreach ( $input as $claim ) {
				$this[] = $claim;
			}
		}
	}

	/**
	 * @param string $guid
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	private function getGuidKey( $guid ) {
		if ( !is_string( $guid ) ) {
			throw new InvalidArgumentException( '$guid must be a string; got ' . gettype( $guid ) );
		}

		$key = strtoupper( $guid );
		return $key;
	}

	/**
	 * @param Claim $claim
	 *
	 * @param Claim $claim
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	private function getClaimKey( Claim $claim ) {
		$guid = $claim->getGuid();

		if ( $guid === null ) {
			throw new InvalidArgumentException( 'Can\'t handle claims with no GUID set!' );
		}

		$key = $this->getGuidKey( $guid );
		return $key;
	}

	/**
	 * @since 0.1
	 *
	 * @param Claim $claim
	 * @param int|null $index
	 *
	 * @throws InvalidArgumentException
	 */
	public function addClaim( Claim $claim, $index = null ) {
		if ( !is_null( $index ) && !is_integer( $index ) ) {
			throw new InvalidArgumentException( '$index must be an integer or null; got ' . gettype( $index ) );
		} elseif ( is_null( $index ) || $index >= count( $this ) ) {
			$this[] = $claim;
		} else {
			$this->insertClaimAtIndex( $claim, $index );
		}
	}

	/**
	 * @param Claim $claim
	 * @param int $index
	 */
	private function insertClaimAtIndex( Claim $claim, $index ) {
		// Determine the claims to shift and remove them from the array:
		$claimsToShift = array_slice( (array)$this, $index );

		foreach ( $claimsToShift as $object ) {
			$this->offsetUnset( $this->getClaimKey( $object ) );
		}

		// Append the new claim and re-append the previously removed claims:
		$this[] = $claim;

		foreach ( $claimsToShift as $object ) {
			$this[] = $object;
		}
	}

	/**
	 * @since 0.1
	 *
	 * @param Claim $claim
	 *
	 * @return bool
	 */
	public function hasClaim( Claim $claim ) {
		$guid = $claim->getGuid();

		if ( $guid === null ) {
			return false;
		}

		$key = $this->getGuidKey( $guid );
		return $this->offsetExists( $key );
	}

	/**
	 * @since 0.5
	 *
	 * @param Claim $claim
	 *
	 * @return int|bool
	 */
	public function indexOf( Claim $claim ) {
		$guid = $claim->getGuid();
		$index = 0;

		/**
		 * @var Claim $claimObject
		 */
		foreach ( $this as $claimObject ) {
			if ( $claimObject->getGuid() === $guid ) {
				return $index;
			}
			$index++;
		}

		return false;
	}

	/**
	 * @since 0.1
	 *
	 * @param Claim $claim
	 */
	public function removeClaim( Claim $claim ) {
		$guid = $claim->getGuid();

		if ( $guid === null ) {
			return;
		}

		$key = $this->getGuidKey( $guid );

		if ( $this->offsetExists( $key ) ) {
			$this->offsetUnset( $key );
		}
	}

	/**
	 * @since 0.3
	 *
	 * @param string $claimGuid
	 *
	 * @return bool
	 */
	public function hasClaimWithGuid( $claimGuid ) {
		return $this->offsetExists( $claimGuid );
	}

	/**
	 * @since 0.3
	 *
	 * @param string $claimGuid
	 */
	public function removeClaimWithGuid( $claimGuid ) {
		if ( $this->offsetExists( $claimGuid ) ) {
			$this->offsetUnset( $claimGuid );
		}
	}

	/**
	 * @since 0.3
	 *
	 * @param string $claimGuid
	 *
	 * @return Claim|null
	 */
	public function getClaimWithGuid( $claimGuid ) {
		if ( $this->offsetExists( $claimGuid ) ) {
			return $this->offsetGet( $claimGuid );
		} else {
			return null;
		}
	}

	/**
	 * @see ArrayAccess::offsetExists
	 *
	 * @param string $guid
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 */
	public function offsetExists( $guid ) {
		$key = $this->getGuidKey( $guid );
		return parent::offsetExists( $key );
	}

	/**
	 * @see ArrayAccess::offsetGet
	 *
	 * @param string $guid
	 *
	 * @return Claim
	 *
	 * @throws InvalidArgumentException
	 */
	public function offsetGet( $guid ) {
		$key = $this->getGuidKey( $guid );
		return parent::offsetGet( $key );
	}

	/**
	 * @see ArrayAccess::offsetSet
	 *
	 * @param string $guid
	 * @param Claim $claim
	 *
	 * @throws InvalidArgumentException
	 */
	public function offsetSet( $guid, $claim ) {
		if ( !( $claim instanceof Claim ) ) {
			throw new InvalidArgumentException( '$claim must be an instance of Claim' );
		}

		$claimKey = $this->getClaimKey( $claim );

		if ( $guid !== null ) {
			$guidKey = $this->getGuidKey( $guid );

			if ( $guidKey !== $claimKey ) {
				throw new InvalidArgumentException( 'The key must be the claim\'s GUID.' );
			}
		}

		parent::offsetSet( $claimKey, $claim );
	}

	/**
	 * @see ArrayAccess::offsetUnset
	 *
	 * @param string $guid
	 */
	public function offsetUnset( $guid ) {
		$key = $this->getGuidKey( $guid );
		parent::offsetUnset( $key );
	}

	/**
	 * Get array of Claim guids
	 *
	 * @since 0.4
	 *
	 * @return string[]
	 */
	public function getGuids() {
		return array_map( function ( Claim $claim ) {
			return $claim->getGuid();
		}, iterator_to_array( $this ) );
	}

	/**
	 * Returns the claims for the given property.
	 *
	 * @since 0.4
	 *
	 * @param PropertyId $propertyId
	 *
	 * @return Claims
	 */
	public function getClaimsForProperty( PropertyId $propertyId ) {
		$byPropertyIdGrouper = new ByPropertyIdGrouper( $this );

		if ( !$byPropertyIdGrouper->hasPropertyId( $propertyId ) ) {
			return new self();
		}

		return new self( $byPropertyIdGrouper->getByPropertyId( $propertyId ) );
	}

	/**
	 * Returns the main Snaks of the claims in this list.
	 *
	 * @since 0.4
	 *
	 * @return Snak[]
	 */
	public function getMainSnaks() {
		$snaks = array();

		/* @var Claim $claim */
		foreach ( $this as $claim ) {
			$guid = $claim->getGuid();
			$snaks[$guid] = $claim->getMainSnak();
		}

		return $snaks;
	}

	/**
	 * Returns a map from GUIDs to claim hashes.
	 *
	 * @since 0.4
	 *
	 * @return string[]
	 */
	public function getHashes() {
		$snaks = array();

		/* @var Claim $claim */
		foreach ( $this as $claim ) {
			$guid = $claim->getGuid();
			$snaks[$guid] = $claim->getHash();
		}

		return $snaks;
	}

	/**
	 * Returns a hash based on the value of the object.
	 * The hash is based on the hashes of the claims contained,
	 * with the order of claims considered significant.
	 *
	 * @since 0.5
	 *
	 * @return string
	 */
	public function getHash() {
		$hash = sha1( '' );

		/* @var Claim $claim */
		foreach ( $this as $claim ) {
			$hash = sha1( $hash . $claim->getHash() );
		}

		return $hash;
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		return !$this->getIterator()->valid();
	}

	/**
	 * Returns a new instance only containing the claims with the given rank.
	 *
	 * @since 0.7
	 *
	 * @param int $rank
	 *
	 * @return Claims
	 */
	public function getByRank( $rank ) {
		$claims = new self();

		/* @var Claim $claim */
		foreach ( $this as $claim ) {
			if ( $claim->getRank() === $rank ) {
				$claims[] = $claim;
			}
		}

		return $claims;
	}

	/**
	 * Returns a new instance only containing the claims with the given ranks.
	 *
	 * @since 0.7.2
	 *
	 * @param int[] $ranks
	 *
	 * @return Claims
	 */
	public function getByRanks( array $ranks ) {
		$ranks = array_flip( $ranks );
		$claims = new self();

		/* @var Claim $claim */
		foreach ( $this as $claim ) {
			if ( isset( $ranks[$claim->getRank()] ) ) {
				$claims[] = $claim;
			}
		}

		return $claims;
	}

	/**
	 * Returns a new instance only containing the best claims (these are the highest
	 * ranked claims, but never deprecated ones). This implementation ignores the properties
	 * so you probably want to call Claims::getClaimsForProperty first or use
	 * StatementList::getBestStatementPerProperty instead.
	 *
	 * @see Claims::getClaimsForProperty
	 * @see StatementList::getBestStatementPerProperty
	 *
	 * @since 0.7
	 *
	 * @return Claims
	 */
	public function getBestClaims() {
		$rank = Claim::RANK_TRUTH;

		do {
			$claims = $this->getByRank( $rank );
			$rank--;
		} while ( $claims->isEmpty() && $rank > Statement::RANK_DEPRECATED );

		return $claims;
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

		if ( !( $target instanceof self )
			|| $this->count() !== $target->count()
		) {
			return false;
		}

		foreach ( $this as $claim ) {
			if ( !$target->hasExactClaim( $claim ) ) {
				return false;
			}
		}

		return true;
	}

	private function hasExactClaim( Claim $claim ) {
		return $this->hasClaim( $claim )
			&& $this->getClaimWithGuid( $claim->getGuid() )->equals( $claim );
	}

}
