<?php

namespace Wikibase\DataModel\Entity;

use DataValues\Deserializers\DataValueDeserializer;
use Diff\Comparer\CallbackComparer;
use Diff\Patcher\MapPatcher;
use Diff\Patcher\Patcher;
use InvalidArgumentException;
use OutOfBoundsException;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\InternalSerialization\DeserializerFactory;

/**
 * Represents a single Wikibase item.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Items
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Item extends Entity {

	const ENTITY_TYPE = 'item';

	/**
	 * @var SiteLink[]
	 */
	private $siteLinks;

	/**
	 * @var Statement[]
	 */
	private $statements;

	/**
	 * @since 0.8
	 *
	 * @param ItemId|null $id
	 * @param Fingerprint $fingerprint
	 * @param SiteLinkList $links
	 * @param Statement[] $statements
	 */
	public function __construct( ItemId $id = null, Fingerprint $fingerprint, SiteLinkList $links, array $statements ) {
		$this->id = $id;
		$this->fingerprint = $fingerprint;
		$this->siteLinks = iterator_to_array( $links );
		$this->statements = $statements;
	}

	/**
	 * Adds a site link to the list of site links.
	 * If there already is a site link with the site id of the provided site link,
	 * then that one will be overridden by the provided one.
	 *
	 * @since 0.6
	 *
	 * @param SiteLink $siteLink
	 */
	public function addSiteLink( SiteLink $siteLink ) {
		$this->siteLinks[$siteLink->getSiteId()] = $siteLink;
	}

	/**
	 * Removes the sitelink with specified site ID if the Item has such a sitelink.
	 * A page name can be provided to have removal only happen when it matches what is set.
	 * A boolean is returned indicating if a link got removed or not.
	 *
	 * @since 0.1
	 *
	 * @param string $siteId the target site's id
	 * @param bool|string $pageName he target page's name (in normalized form)
	 *
	 * @return bool Success indicator
	 */
	public function removeSiteLink( $siteId, $pageName = false ) {
		if ( $pageName !== false ) {
			$success = array_key_exists( $siteId, $this->siteLinks ) && $this->siteLinks[ $siteId ]->getPageName() === $pageName;
		}
		else {
			$success = array_key_exists( $siteId, $this->siteLinks );
		}

		if ( $success ) {
			unset( $this->siteLinks[ $siteId ] );
		}

		return $success;
	}

	/**
	 * @since 0.6
	 *
	 * @return SiteLink[]
	 */
	public function getSiteLinks() {
		return $this->siteLinks;
	}

	/**
	 * @since 0.6
	 *
	 * @param string $siteId
	 *
	 * @return SiteLink
	 * @throws OutOfBoundsException
	 */
	public function getSiteLink( $siteId ) {
		if ( !array_key_exists( $siteId, $this->siteLinks ) ) {
			throw new OutOfBoundsException( "There is no site link with site id $siteId" );
		}

		return $this->siteLinks[ $siteId ];
	}

	/**
	 * @since 0.4
	 *
	 * @param string $siteId
	 *
	 * @return bool
	 */
	public function hasLinkToSite( $siteId ) {
		return array_key_exists( $siteId, $this->siteLinks );
	}

	/**
	 * @since 0.5
	 *
	 * @return bool
	 */
	 public function hasSiteLinks() {
		 return !empty( $this->siteLinks );
	 }

	/**
	 * @see Entity::newFromArray
	 *
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return Item
	 */
	public static function newFromArray( array $data ) {
		return self::getDeserializer()->deserialize( $data );
	}

	private static function getDeserializer() {
		$deserializerFactory = new DeserializerFactory(
			new DataValueDeserializer( $GLOBALS['evilDataValueMap'] ),
			new BasicEntityIdParser()
		);

		return $deserializerFactory->newEntityDeserializer();
	}

	/**
	 * @since 0.1
	 *
	 * @return Item
	 */
	public static function newEmpty() {
		return new self(
			null,
			Fingerprint::newEmpty(),
			new SiteLinkList( array() ),
			array()
		);
	}

	/**
	 * @see Entity::getType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getType() {
		return Item::ENTITY_TYPE;
	}

	/**
	 * @see Entity::newClaim
	 *
	 * @since 0.3
	 *
	 * @param Snak $mainSnak
	 *
	 * @return Statement
	 */
	public function newClaim( Snak $mainSnak ) {
		return new Statement( $mainSnak );
	}

	/**
	 * @see Entity::entityToDiffArray
	 *
	 * @since 0.4
	 *
	 * @param Entity $entity
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function entityToDiffArray( Entity $entity ) {
		if ( !( $entity instanceof Item ) ) {
			throw new InvalidArgumentException( 'ItemDiffer only accepts Item objects' );
		}

		$array = parent::entityToDiffArray( $entity );

		// FIXME 8
		$array['links'] = array();

		return $array;
	}

	/**
	 * @see Entity::patchSpecificFields
	 *
	 * @since 0.4
	 *
	 * @param EntityDiff $patch
	 * @param MapPatcher $patcher
	 */
	protected function patchSpecificFields( EntityDiff $patch, MapPatcher $patcher ) {
		if ( $patch instanceof ItemDiff ) {
			$this->patchSiteLinks( $patch, $patcher );
			$this->patchClaims( $patch, $patcher );
		}
	}

	private function patchSiteLinks( EntityDiff $patch, MapPatcher $patcher ) {
		$siteLinksDiff = $patch->getSiteLinkDiff();

		if ( !$siteLinksDiff->isEmpty() ) {
			// FIXME 8
			$links = array();
			$links = $patcher->patch( $links, $siteLinksDiff );

			$this->siteLinks = array();
			foreach ( $links as $siteId => $linkSerialization ) {
				if ( array_key_exists( 'name', $linkSerialization ) ) {
					// FIXME 8
					//$this->siteLinks[$siteId] = SiteLink::newFromArray( $siteId, $linkSerialization );
				}
			}
		}
	}

	private function patchClaims( EntityDiff $patch, MapPatcher $patcher ) {
		$patcher->setValueComparer( new CallbackComparer(
			function( Claim $firstClaim, Claim $secondClaim ) {
				return $firstClaim->getHash() === $secondClaim->getHash();
			}
		) );

		$claims = array();

		foreach ( $this->getClaims() as $claim ) {
			$claims[$claim->getGuid()] = $claim;
		}

		$claims = $patcher->patch( $claims, $patch->getClaimsDiff() );

		$this->setClaims( new Claims( $claims ) );
	}

	/**
	 * @since 0.5
	 *
	 * @param string $idSerialization
	 *
	 * @return EntityId
	 */
	protected function idFromSerialization( $idSerialization ) {
		return new ItemId( $idSerialization );
	}

	/**
	 * @see ClaimListAccess::addClaim
	 *
	 * @since 0.3
	 *
	 * @param Claim $claim
	 *
	 * @throws InvalidArgumentException
	 */
	public function addClaim( Claim $claim ) {
		if ( $claim->getGuid() === null ) {
			throw new InvalidArgumentException( 'Can\'t add a Claim without a GUID.' );
		}

		// TODO: ensure guid is valid for entity

		$this->statements[] = $claim;
	}

	/**
	 * @see ClaimAggregate::getClaims
	 *
	 * @since 0.3
	 *
	 * @return Claim[]
	 */
	public function getClaims() {
		return $this->statements;
	}

	/**
	 * TODO: change to take Claim[]
	 *
	 * @since 0.4
	 *
	 * @param Claims $claims
	 */
	public function setClaims( Claims $claims ) {
		$this->statements = iterator_to_array( $claims );
	}

	/**
	 * Convenience function to check if the entity contains any claims.
	 *
	 * On top of being a convenience function, this implementation allows for doing
	 * the check without forcing an unstub in contrast to count( $this->getClaims() ).
	 *
	 * @since 0.2
	 *
	 * @return bool
	 */
	public function hasClaims() {
		return !empty( $this->statements );
	}

}
