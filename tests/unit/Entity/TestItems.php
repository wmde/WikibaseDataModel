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

		$item->setDescription( 'en', 'foo' );
		$item->setLabel( 'en', 'bar' );

		$items[] = $item;

		$item = new Item();

		$item->setAliases( 'en', array( 'foobar', 'baz' ) );

		$items[] = $item;

		$item = new Item();
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'enwiki', 'spam' ) );

		$items[] = $item;

		$item = new Item();
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'enwiki', 'spamz' ) );
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'dewiki', 'foobar' ) );

		$item->setDescription( 'en', 'foo' );
		$item->setLabel( 'en', 'bar' );

		$item->setAliases( 'en', array( 'foobar', 'baz' ) );
		$item->setAliases( 'de', array( 'foobar', 'spam' ) );

		$items[] = $item;

		return $items;
	}

}
