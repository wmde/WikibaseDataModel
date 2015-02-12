<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;
use OutOfBoundsException;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\StatementListProvider;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * Represents a single Wikibase item.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#Items
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Item extends Entity implements StatementListProvider {

	const ENTITY_TYPE = 'item';

	/**
	 * @var SiteLinkList
	 */
	private $siteLinks;

	/**
	 * @var StatementList
	 */
	private $statements;

	/**
	 * @since 1.0
	 *
	 * @param ItemId|null $id
	 * @param Fingerprint|null $fingerprint
	 * @param SiteLinkList|null $siteLinks
	 * @param StatementList|null $statements
	 */
	public function __construct(
		ItemId $id = null,
		Fingerprint $fingerprint = null,
		SiteLinkList $siteLinks = null,
		StatementList $statements = null
	) {
		$this->id = $id;
		$this->fingerprint = $fingerprint ?: new Fingerprint();
		$this->siteLinks = $siteLinks ?: new SiteLinkList();
		$this->statements = $statements ?: new StatementList();
	}

	/**
	 * Can be integer since 0.1.
	 * Can be ItemId since 0.5.
	 * Can be null since 1.0.
	 *
	 * @param ItemId|int|null $id
	 *
	 * @throws InvalidArgumentException
	 */
	public function setId( $id ) {
		if ( $id === null || $id instanceof ItemId ) {
			$this->id = $id;
		}
		elseif ( is_integer( $id ) ) {
			$this->id = ItemId::newFromNumber( $id );
		}
		else {
			throw new InvalidArgumentException( '$id must be an instance of ItemId, an integer, or null' );
		}
	}

	/**
	 * @since 0.1 return type changed in 0.3
	 *
	 * @return ItemId|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @since 0.8
	 *
	 * @return SiteLinkList
	 */
	public function getSiteLinkList() {
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
		if ( $this->siteLinks->hasLinkWithSiteId( $siteLink->getSiteId() ) ) {
			$this->siteLinks->removeLinkWithSiteId( $siteLink->getSiteId() );
		}

		$this->siteLinks->addSiteLink( $siteLink );
	}

	/**
	 * Removes the sitelink with specified site ID if the Item has such a sitelink.
	 *
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.1
	 *
	 * @param string $siteId the target site's id
	 */
	public function removeSiteLink( $siteId ) {
		$this->siteLinks->removeLinkWithSiteId( $siteId );
	}

	/**
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.6
	 *
	 * @return SiteLink[]
	 */
	public function getSiteLinks() {
		return array_values( iterator_to_array( $this->siteLinks ) );
	}

	/**
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.6
	 *
	 * @param string $siteId
	 *
	 * @return SiteLink
	 * @throws OutOfBoundsException
	 */
	public function getSiteLink( $siteId ) {
		return $this->siteLinks->getBySiteId( $siteId );
	}

	/**
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.4
	 *
	 * @param string $siteId
	 *
	 * @return bool
	 */
	public function hasLinkToSite( $siteId ) {
		return $this->siteLinks->hasLinkWithSiteId( $siteId );
	}

	/**
	 * @deprecated since 0.8, use getSiteLinkList and setSiteLinkList instead
	 * @since 0.5
	 *
	 * @return bool
	 */
	public function hasSiteLinks() {
		return !$this->siteLinks->isEmpty();
	}

	/**
	 * @deprecated since 2.5, use new Item() instead.
	 *
	 * @return Item
	 */
	public static function newEmpty() {
		return new self();
	}

	/**
	 * @see Entity::getType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getType() {
		return self::ENTITY_TYPE;
	}

	/**
	 * @deprecated since 1.0
	 *
	 * @param Snak $mainSnak
	 *
	 * @return Statement
	 */
	public function newClaim( Snak $mainSnak ) {
		return new Statement( $mainSnak );
	}

	/**
	 * Returns if the Item has no content.
	 * Having an id set does not count as having content.
	 *
	 * @since 0.1
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return $this->fingerprint->isEmpty()
			&& $this->statements->isEmpty()
			&& $this->siteLinks->isEmpty();
	}

	/**
	 * Removes all content from the Item.
	 * The id is not part of the content.
	 *
	 * @since 0.1
	 */
	public function clear() {
		$this->fingerprint = new Fingerprint();
		$this->siteLinks = new SiteLinkList();
		$this->statements = new StatementList();
	}

	/**
	 * @deprecated since 1.0, use getStatements instead
	 *
	 * @param Claim $statement This needs to be a Statement as of 1.0
	 *
	 * @throws InvalidArgumentException
	 */
	public function addClaim( Claim $statement ) {
		if ( !( $statement instanceof Statement ) ) {
			throw new InvalidArgumentException( '$statement must be an instance of Statement' );
		} elseif ( $statement->getGuid() === null ) {
			throw new InvalidArgumentException( 'Can\'t add a Claim without a GUID.' );
		}

		$this->statements->addStatement( $statement );
	}

	/**
	 * @since 1.0
	 *
	 * @return StatementList
	 */
	public function getStatements() {
		return $this->statements;
	}

	/**
	 * @since 1.0
	 *
	 * @param StatementList $statements
	 */
	public function setStatements( StatementList $statements ) {
		$this->statements = $statements;
	}

	/**
	 * @deprecated since 1.0, use getStatements instead
	 *
	 * @return Statement[]
	 */
	public function getClaims() {
		return $this->statements->toArray();
	}

	/**
	 * @deprecated since 1.0, use setStatements instead
	 *
	 * @param Claims $claims
	 */
	public function setClaims( Claims $claims ) {
		$this->statements = new StatementList( iterator_to_array( $claims ) );
	}

	/**
	 * @deprecated since 1.0, use getStatements instead
	 *
	 * @return bool
	 */
	public function hasClaims() {
		return !$this->statements->isEmpty();
	}

	/**
	 * @see Comparable::equals
	 *
	 * Two items are considered equal if they are of the same
	 * type and have the same value. The value does not include
	 * the id, so entities with the same value but different id
	 * are considered equal.
	 *
	 * @since 0.1
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
			&& $this->fingerprint->equals( $target->fingerprint )
			&& $this->siteLinks->equals( $target->siteLinks )
			&& $this->statements->equals( $target->statements );
	}

}
