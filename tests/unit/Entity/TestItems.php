<?php

namespace Wikibase\DataModel\Tests\Entity;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\SiteLink;

/**
 * Holds Item objects for testing proposes.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class TestItems {

	/**
	 * @since 0.1
	 * @return Item[]
	 */
	public static function getItems() {
		$items = array();

		$items[] = new Item();

		$item = new Item();

		$item->getFingerprint()->setDescription( 'en', 'foo' );
		$item->getFingerprint()->setLabel( 'en', 'bar' );

		$items[] = $item;

		$item = new Item();

		$item->getFingerprint()->setAliasGroup( 'en', array( 'foobar', 'baz' ) );

		$items[] = $item;

		$item = new Item();
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'enwiki', 'spam' ) );

		$items[] = $item;

		$item = new Item();
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'enwiki', 'spamz' ) );
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'dewiki', 'foobar' ) );

		$item->getFingerprint()->setDescription( 'en', 'foo' );
		$item->getFingerprint()->setLabel( 'en', 'bar' );

		$item->getFingerprint()->setAliasGroup( 'en', array( 'foobar', 'baz' ) );
		$item->getFingerprint()->setAliasGroup( 'de', array( 'foobar', 'spam' ) );

		$items[] = $item;

		return $items;
	}

}
