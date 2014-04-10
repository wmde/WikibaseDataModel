<?php

namespace Wikibase\DataModel;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;

/**
 * Value object representing a link to another site.
 *
 * @since 0.4
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Michał Łazowik
 */
class SiteLink {

	protected $siteId;
	protected $pageName;

	/**
	 * @var ItemId[]
	 */
	protected $badges;

	/**
	 * @param string $siteId
	 * @param string $pageName
	 * @param ItemId[] $badges
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $siteId, $pageName, $badges = array() ) {
		if ( !is_string( $siteId ) ) {
			throw new InvalidArgumentException( '$siteId needs to be a string' );
		}

		if ( !is_string( $pageName ) ) {
			throw new InvalidArgumentException( '$pageName needs to be a string' );
		}

		$this->assertBadgesAreValid( $badges );

		$this->siteId = $siteId;
		$this->pageName = $pageName;
		$this->badges = array_values( $badges );
	}

	/**
	 * @param ItemId[] $badges
	 *
	 * @throws InvalidArgumentException
	 */
	protected function assertBadgesAreValid( $badges ) {
		if ( !is_array( $badges ) ) {
			throw new InvalidArgumentException( '$badges needs to be an array' );
		}

		foreach( $badges as $badge ) {
			if ( !( $badge instanceof ItemId ) ) {
				throw new InvalidArgumentException( 'Each element in $badges needs to be an ItemId' );
			}
		}

		if ( count( $badges ) !== count( array_unique( $badges ) ) ) {
			throw new InvalidArgumentException( '$badges array cannot contain duplicates' );
		}
	}

	/**
	 * @since 0.4
	 *
	 * @return string
	 */
	public function getSiteId() {
		return $this->siteId;
	}

	/**
	 * @since 0.4
	 *
	 * @return string
	 */
	public function getPageName() {
		return $this->pageName;
	}

	/**
	 * Badges are not order dependent.
	 *
	 * @since 0.5
	 *
	 * @return ItemId[]
	 */
	public function getBadges() {
		return $this->badges;
	}

}
