<?php

namespace Wikibase\DataModel\Entity;

use Diff\Patcher\Patcher;
use InvalidArgumentException;
use OutOfBoundsException;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Snak\Snak;

/**
 * Represents a single Wikibase item.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Items
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Michał Łazowik
 */
class Item extends Entity {

	const ENTITY_TYPE = 'item';

	/**
	 * @since 0.5
	 *
	 * @var SiteLinkList|null
	 */
	protected $siteLinks = null;

	/**
	 * @since 0.8
	 *
	 * @return SiteLinkList
	 */
	public function getSiteLinkList() {
		$this->unstubSiteLinks();
		return $this->siteLinks;
	}

	/**
	 * @since 0.8
	 *
	 * @param SiteLinkList $siteLinks
	 */
	public function setSiteLinkList( SiteLinkList $siteLinks ) {
		$this->siteLinks = $siteLinks;
	}

	/**
	 * Adds a site link to the list of site links.
	 * If there already is a site link with the site id of the provided site link,
	 * then that one will be overridden by the provided one.
	 *
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.6
	 *
	 * @param SiteLink $siteLink
	 */
	public function addSiteLink( SiteLink $siteLink ) {
		$this->unstubSiteLinks();

		if ( $this->siteLinks->hasLinkWithSiteId( $siteLink->getSiteId() ) ) {
			$this->siteLinks->removeLinkWithSiteId( $siteLink->getSiteId() );
		}

		$this->siteLinks->addSiteLink( $siteLink );
	}

	/**
	 * @since 0.4
	 * @deprecated since 0.6, use addSiteLink instead
	 */
	public function addSimpleSiteLink( SiteLink $siteLink ) {
		$this->addSiteLink( $siteLink );
	}

	/**
	 * Removes the sitelink with specified site ID if the Item has such a sitelink.
	 * A page name can be provided to have removal only happen when it matches what is set.
	 * A boolean is returned indicating if a link got removed or not.
	 *
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.1
	 *
	 * @param string $siteId the target site's id
	 */
	public function removeSiteLink( $siteId ) {
		$this->unstubSiteLinks();
		$this->siteLinks->removeLinkWithSiteId( $siteId );
	}

	/**
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.6
	 *
	 * @return SiteLink[]
	 */
	public function getSiteLinks() {
		$this->unstubSiteLinks();

		$links = array();

		foreach ( $this->siteLinks as $link ) {
			$links[] = $link;
		}

		return $links;
	}

	/**
	 * @since 0.4
	 * @deprecated since 0.6, use getSiteLinks instead
	 *
	 * @return SiteLink[]
	 */
	public function getSimpleSiteLinks() {
		return $this->getSiteLinks();
	}

	/**
	 * @since 0.4
	 * @deprecated since 0.6, use getSiteLink instead
	 *
	 * @param string $siteId
	 *
	 * @return SiteLink
	 * @throws OutOfBoundsException
	 */
	public function getSimpleSiteLink( $siteId ) {
		return $this->getSiteLink( $siteId );
	}

	/**
	 * @since 0.6
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 *
	 * @param string $siteId
	 *
	 * @return SiteLink
	 * @throws OutOfBoundsException
	 */
	public function getSiteLink( $siteId ) {
		$this->unstubSiteLinks();
		return $this->siteLinks->getBySiteId( $siteId );
	}

	/**
	 * @since 0.4
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 *
	 * @param string $siteId
	 *
	 * @return bool
	 */
	public function hasLinkToSite( $siteId ) {
		$this->unstubSiteLinks();
		return $this->siteLinks->hasLinkWithSiteId( $siteId );
	}

	/**
	 * Unstubs sitelinks from the unserialized data.
	 *
	 * @since 0.5
	 */
	protected function unstubSiteLinks() {
		if ( $this->siteLinks === null ) {
			$this->siteLinks = new SiteLinkList();

			foreach ( $this->data['links'] as $siteId => $linkSerialization ) {
				$this->siteLinks->addSiteLink( SiteLink::newFromArray( $siteId, $linkSerialization ) );
			}
		}
	}

	/**
	 * Returns the SiteLinks as stubs.
	 *
	 * @since 0.5
	 *
	 * @return array
	 */
	protected function getStubbedSiteLinks() {
		if ( is_string( reset( $this->data['links'] ) ) ) {
			// legacy serialization
			$this->unstubSiteLinks();
		}

		if ( $this->siteLinks !== null ) {
			$siteLinks = array();

			/**
			 * @var SiteLink $siteLink
			 */
			foreach ( $this->siteLinks as $siteLink ) {
				$siteLinks[$siteLink->getSiteId()] = $siteLink->toArray();
			}
		} else {
			$siteLinks = $this->data['links'];
		}

		return $siteLinks;
	}

	/**
	 * @since 0.5
	 *
	 * @return bool
	 */
	 public function hasSiteLinks() {
		if ( $this->siteLinks === null ) {
			return $this->data['links'] !== array();
		} else {
			return !$this->siteLinks->isEmpty();
		}
	 }

	/**
	 * @see Entity::isEmpty
	 *
	 * @since 0.1
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		return parent::isEmpty()
			&& !$this->hasSiteLinks();
	}

	/**
	 * @see Entity::stub
	 *
	 * @since 0.5
	 */
	public function stub() {
		parent::stub();
		$this->data['links'] = $this->getStubbedSiteLinks();
	}

	/**
	 * @see Entity::cleanStructure
	 *
	 * @since 0.1
	 *
	 * @param boolean $wipeExisting
	 */
	protected function cleanStructure( $wipeExisting = false ) {
		parent::cleanStructure( $wipeExisting );

		foreach ( array( 'links' ) as $field ) {
			if (  $wipeExisting || !array_key_exists( $field, $this->data ) ) {
				$this->data[$field] = array();
			}
		}

		$this->siteLinks = null;
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
		return new static( $data );
	}

	/**
	 * @since 0.1
	 *
	 * @return Item
	 */
	public static function newEmpty() {
		return self::newFromArray( array() );
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

		$array['links'] = $entity->getStubbedSiteLinks();

		return $array;
	}

	/**
	 * @see Entity::patchSpecificFields
	 *
	 * @since 0.4
	 *
	 * @param EntityDiff $patch
	 * @param Patcher $patcher
	 */
	protected function patchSpecificFields( EntityDiff $patch, Patcher $patcher ) {
		if ( $patch instanceof ItemDiff ) {
			$siteLinksDiff = $patch->getSiteLinkDiff();

			if ( !$siteLinksDiff->isEmpty() ) {
				$links = $this->getStubbedSiteLinks();
				$links = $patcher->patch( $links, $siteLinksDiff );

				$this->siteLinks = new SiteLinkList();
				foreach ( $links as $siteId => $linkSerialization ) {
					if ( array_key_exists( 'name', $linkSerialization ) ) {
						$this->addSiteLink( SiteLink::newFromArray( $siteId, $linkSerialization ) );
					}
				}
			}
		}
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

}
