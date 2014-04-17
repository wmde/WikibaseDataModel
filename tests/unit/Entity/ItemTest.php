<?php

namespace Wikibase\Test\Entity;

use DataValues\StringValue;
use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpChange;
use Diff\DiffOp\DiffOpRemove;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\EntityDiff;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemDiff;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;

/**
 * @covers Wikibase\DataModel\Entity\Item
 * @covers Wikibase\DataModel\Entity\Entity
 *
 * Some tests for this class are located in ItemMultilangTextsTest,
 * ItemNewEmptyTest and ItemNewFromArrayTest.
 *
 * @since 0.1
 *
 * @group Wikibase
 * @group WikibaseItem
 * @group WikibaseDataModel
 * @group WikibaseItemTest
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author John Erling Blad < jeblad@gmail.com >
 * @author Michał Łazowik
 */
class ItemTest extends EntityTest {

	/**
	 * Returns several more or less complex claims
	 *
	 * @return array
	 */
	public function makeClaims() {
		$id9001 = new EntityIdValue( new ItemId( 'q9001' ) );
		$id1 = new EntityIdValue( new ItemId( 'q1' ) );

		$claims = array();

		$claims[] = new Claim( new PropertyNoValueSnak( 42 ) );

		$claims[] = new Statement(
			new PropertyNoValueSnak( 42 ),
			null,
			new ReferenceList( array(
				new Reference( new SnakList( array(
					new PropertyNoValueSnak( 24 ),
					new PropertyValueSnak( 1, new StringValue( 'onoez' ) ) ) )
				),
				new Reference( new SnakList( array(
					new PropertyValueSnak( 1, $id9001 ) ) )
				)
			) )
		);

		$claims[] = new Claim( new PropertySomeValueSnak( 43 ) );

		$claims[] = new Claim(
			new PropertyNoValueSnak( 42 ),
			new SnakList( array(
				new PropertyNoValueSnak( 42 ),
				new PropertySomeValueSnak( 43 ),
				new PropertyValueSnak( 1, new StringValue( 'onoez' ) ),
			) )
		);

		$claims[] = new Claim(
			new PropertyValueSnak( 2, $id9001 ),
			new SnakList( array(
				new PropertyNoValueSnak( 42 ),
				new PropertySomeValueSnak( 43 ),
				new PropertyValueSnak( 1, new StringValue( 'onoez' ) ),
				new PropertyValueSnak( 2, $id1 ),
			) )
		);

		/**
		 * @var Claim $claim
		 */
		foreach ( $claims as $i => $claim ) {
			$claim->setGuid( "ItemTest\$claim-$i" );
		}

		return $claims;
	}

	/**
	 * @see EntityTest::getNewEmpty
	 *
	 * @since 0.1
	 *
	 * @return Item
	 */
	protected function getNewEmpty() {
		return Item::newEmpty();
	}

	/**
	 * @see   EntityTest::getNewFromArray
	 *
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return Entity
	 */
	protected function getNewFromArray( array $data ) {
		return Item::newFromArray( $data );
	}

	public function testToArray() {
		/**
		 * @var Item $item
		 */
		foreach ( TestItems::getItems() as $item ) {
			$this->assertInternalType( 'array', $item->toArray() );
		}
	}

	public function testGetId() {
		/**
		 * @var Item $item
		 */
		foreach ( TestItems::getItems() as $item ) {
			$this->assertTrue( is_null( $item->getId() ) || $item->getId() instanceof ItemId );
		}
	}

	public function testSetIdUsingNumber() {
		foreach ( TestItems::getItems() as $item ) {
			$item->setId( 42 );
			$this->assertEquals( new ItemId( 'Q42' ), $item->getId() );
		}
	}

	public function itemProvider() {
		$items = array();

		$items[] = Item::newEmpty();

		$item = Item::newEmpty();
		$item->setDescription( 'en', 'foo' );
		$items[] = $item;

		$item = Item::newEmpty();
		$item->setDescription( 'en', 'foo' );
		$item->setDescription( 'de', 'foo' );
		$item->setLabel( 'en', 'foo' );
		$item->setAliases( 'de', array( 'bar', 'baz' ) );
		$items[] = $item;

		/**
		 * @var Item $item;
		 */
		$item = $item->copy();
		$item->addClaim( new Statement(
			new PropertyNoValueSnak( new PropertyId( 'P42' ) )
		) );
		$items[] = $item;

		$argLists = array();

		foreach ( $items as $item ) {
			$argLists[] = array( $item );
		}

		return $argLists;
	}

	public function diffProvider() {
		$argLists = parent::diffProvider();

		// Addition of a sitelink
		$entity0 = $this->getNewEmpty();
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink( new SiteLink( 'enwiki', 'Berlin' ) );

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'page'   => new DiffOpAdd( 'Berlin' )
				), true),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Addition of badges
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' )
				)
			)
		);
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'badges' => new Diff( array(
						new DiffOpAdd( 'Q3' )
					), false ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Addition of a sitelink with badges
		$entity0 = $this->getNewEmpty();
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' )
				)
			)
		);

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'page'   => new DiffOpAdd( 'Berlin' ),
					'badges' => new Diff( array(
						new DiffOpAdd( 'Q42' )
					), false ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Removal of a sitelink
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink( new SiteLink( 'enwiki', 'Berlin' ) );
		$entity1 = $this->getNewEmpty();

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'page'   => new DiffOpRemove( 'Berlin' ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Removal of badges
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' )
				)
			)
		);

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'badges' => new Diff( array(
						new DiffOpRemove( 'Q3' )
					), false ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Removal of sitelink with badges
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);
		$entity1 = $this->getNewEmpty();

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'name'   => new DiffOpRemove( 'Berlin' ),
					'badges' => new Diff( array(
						new DiffOpRemove( 'Q42' ),
						new DiffOpRemove( 'Q3' )
					), false ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Modification of a sitelink
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink(
			new SiteLink(
				'enwiki',
				'Foobar',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'name'   => new DiffOpChange( 'Berlin', 'Foobar' ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Modification of badges
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q4' )
				)
			)
		);

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'badges' => new Diff( array(
						new DiffOpRemove( 'Q3' ),
						new DiffOpAdd( 'Q4' )
					), false ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );


		// Modification of a sitelink and badges
		$entity0 = $this->getNewEmpty();
		$entity0->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q3' )
				)
			)
		);
		$entity1 = $this->getNewEmpty();
		$entity1->addSiteLink(
			new SiteLink(
				'enwiki',
				'Foobar',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q4' )
				)
			)
		);

		$expected = new EntityDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'name'   => new DiffOpChange( 'Berlin', 'Foobar' ),
					'badges' => new Diff( array(
						new DiffOpRemove( 'Q3' ),
						new DiffOpAdd( 'Q4' )
					), false ),
				), true ),
			), true ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );

		return $argLists;
	}

	public function patchProvider() {
		$argLists = parent::patchProvider();

		// Addition of a sitelink
		$source = $this->getNewEmpty();
		$patch = new ItemDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'name'   => new DiffOpAdd( 'Berlin' ),
					'badges' => new Diff( array(
						new DiffOpAdd( 'Q42' ),
					), false ),
				), true ),
			), true ),
		) );
		$expected = clone $source;
		$expected->addSiteLink(
			new SiteLink(
				'enwiki',
				'Berlin',
				array(
					new ItemId( 'Q42' )
				)
			)
		);

		$argLists[] = array( $source, $patch, $expected );


		// Retaining of a sitelink
		$source = clone $expected;
		$patch = new ItemDiff();
		$expected = clone $source;

		$argLists[] = array( $source, $patch, $expected );


		// Modification of a sitelink
		$source = clone $expected;
		$patch = new ItemDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'name'   => new DiffOpChange( 'Berlin', 'Foobar' ),
					'badges' => new Diff( array(
						new DiffOpRemove( 'Q42' ),
						new DiffOpAdd( 'Q149' )
					), false ),
				), true ),
			), true )
		) );
		$expected = $this->getNewEmpty();
		$expected->addSiteLink(
			new SiteLink(
				'enwiki',
				'Foobar',
				array(
					new ItemId( 'Q149' )
				)
			)
		);

		$argLists[] = array( $source, $patch, $expected );


		// Removal of a sitelink
		$source = clone $expected;
		$patch = new ItemDiff( array(
			'links' => new Diff( array(
				'enwiki' => new Diff( array(
					'name'   => new DiffOpRemove( 'Foobar' ),
					'badges' => new Diff( array(
						new DiffOpRemove( 'Q149' ),
					), false ),
				), true ),
			), true )
		) );
		$expected = $this->getNewEmpty();

		$argLists[] = array( $source, $patch, $expected );

		return $argLists;
	}

	public function testGetSiteLinkWithNonSetSiteId() {
		$item = Item::newEmpty();

		$this->setExpectedException( 'OutOfBoundsException' );
		$item->getSiteLink( 'enwiki' );
	}

	/**
	 * @dataProvider simpleSiteLinkProvider
	 */
	public function testAddSiteLink( SiteLink $siteLink ) {
		$item = Item::newEmpty();

		$item->addSiteLink( $siteLink );

		$this->assertEquals(
			$siteLink,
			$item->getSiteLink( $siteLink->getSiteId() )
		);
	}

	public function simpleSiteLinkProvider() {
		$argLists = array();

		$argLists[] = array(
			new SiteLink(
				'enwiki',
				'Wikidata',
				array(
					new ItemId( 'Q42' )
				)
			)
		);
		$argLists[] = array(
			new SiteLink(
				'nlwiki',
				'Wikidata'
			)
		);
		$argLists[] = array(
			new SiteLink(
				'enwiki',
				'Nyan!',
				array(
					new ItemId( 'Q42' ),
					new ItemId( 'Q149' )
				)
			)
		);
		$argLists[] = array(
			new SiteLink(
				'foo bar',
				'baz bah',
				array(
					new ItemId( 'Q3' ),
					new ItemId( 'Q7' )
				)
			)
		);

		return $argLists;
	}

	/**
	 * @dataProvider simpleSiteLinksProvider
	 */
	public function testGetSiteLinks() {
		$siteLinks = func_get_args();
		$item = Item::newEmpty();

		foreach ( $siteLinks as $siteLink ) {
			$item->addSiteLink( $siteLink );
		}

		$this->assertInternalType( 'array', $item->getSiteLinks() );
		$this->assertEquals( $siteLinks, $item->getSiteLinks() );
	}

	public function simpleSiteLinksProvider() {
		$argLists = array();

		$argLists[] = array();

		$argLists[] = array( new SiteLink( 'enwiki', 'Wikidata', array( new ItemId( 'Q42' ) ) ) );

		$argLists[] = array(
			new SiteLink( 'enwiki', 'Wikidata' ),
			new SiteLink( 'nlwiki', 'Wikidata', array( new ItemId( 'Q3' ) ) )
		);

		$argLists[] = array(
			new SiteLink( 'enwiki', 'Wikidata' ),
			new SiteLink( 'nlwiki', 'Wikidata' ),
			new SiteLink( 'foo bar', 'baz bah', array( new ItemId( 'Q2' ) ) )
		);

		return $argLists;
	}

	public function testHasLinkToSiteForFalse() {
		$item = Item::newEmpty();
		$item->addSiteLink( new SiteLink( 'ENWIKI', 'Wikidata', array( new ItemId( 'Q42' ) ) ) );

		$this->assertFalse( $item->hasLinkToSite( 'enwiki' ) );
		$this->assertFalse( $item->hasLinkToSite( 'dewiki' ) );
		$this->assertFalse( $item->hasLinkToSite( 'foo bar' ) );
	}

	public function testHasLinkToSiteForTrue() {
		$item = Item::newEmpty();
		$item->addSiteLink( new SiteLink( 'enwiki', 'Wikidata', array( new ItemId( 'Q42' ) ) ) );
		$item->addSiteLink( new SiteLink( 'dewiki', 'Wikidata' ) );
		$item->addSiteLink( new SiteLink( 'foo bar', 'Wikidata' ) );

		$this->assertTrue( $item->hasLinkToSite( 'enwiki' ) );
		$this->assertTrue( $item->hasLinkToSite( 'dewiki' ) );
		$this->assertTrue( $item->hasLinkToSite( 'foo bar' ) );
	}

	public function testNewClaimReturnsStatementWithProvidedMainSnak() {
		$snak = $this->getMock( 'Wikibase\DataModel\Snak\Snak' );

		$item = Item::newEmpty();
		$statement = $item->newClaim( $snak );

		$this->assertInstanceOf( 'Wikibase\DataModel\Claim\Statement', $statement );
		$this->assertEquals( $snak, $statement->getMainSnak() );
	}

}
