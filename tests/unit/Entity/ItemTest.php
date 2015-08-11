<?php

namespace Wikibase\DataModel\Tests\Entity;

use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers Wikibase\DataModel\Entity\Item
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
class ItemTest extends \PHPUnit_Framework_TestCase {

	public function testGetId() {
		foreach ( TestItems::getItems() as $item ) {
			$id = $item->getId();
			$this->assertTrue( $id === null || $id instanceof ItemId );
		}
	}

	public function testSetIdUsingNumber() {
		foreach ( TestItems::getItems() as $item ) {
			$item->setId( 42 );
			$this->assertEquals( new ItemId( 'Q42' ), $item->getId() );
		}
	}

	public function testGetSiteLinkWithNonSetSiteId() {
		$item = new Item();

		$this->setExpectedException( 'OutOfBoundsException' );
		$item->getSiteLinkList()->getBySiteId( 'enwiki' );
	}

	/**
	 * @dataProvider simpleSiteLinkProvider
	 */
	public function testAddSiteLink( SiteLink $siteLink ) {
		$item = new Item();

		$item->getSiteLinkList()->addSiteLink( $siteLink );

		$this->assertEquals(
			$siteLink,
			$item->getSiteLinkList()->getBySiteId( $siteLink->getSiteId() )
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
		$item = new Item();

		foreach ( $siteLinks as $siteLink ) {
			$item->getSiteLinkList()->addSiteLink( $siteLink );
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
		$item = new Item();
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'ENWIKI', 'Wikidata', array( new ItemId( 'Q42' ) ) ) );

		$this->assertFalse( $item->getSiteLinkList()->hasLinkWithSiteId( 'enwiki' ) );
		$this->assertFalse( $item->getSiteLinkList()->hasLinkWithSiteId( 'dewiki' ) );
		$this->assertFalse( $item->getSiteLinkList()->hasLinkWithSiteId( 'foo bar' ) );
	}

	public function testHasLinkToSiteForTrue() {
		$item = new Item();
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'enwiki', 'Wikidata', array( new ItemId( 'Q42' ) ) ) );
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'dewiki', 'Wikidata' ) );
		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'foo bar', 'Wikidata' ) );

		$this->assertTrue( $item->getSiteLinkList()->hasLinkWithSiteId( 'enwiki' ) );
		$this->assertTrue( $item->getSiteLinkList()->hasLinkWithSiteId( 'dewiki' ) );
		$this->assertTrue( $item->getSiteLinkList()->hasLinkWithSiteId( 'foo bar' ) );
	}

	public function testSetClaims() {
		$item = new Item();

		$statement0 = new Statement( new PropertyNoValueSnak( 42 ) );
		$statement0->setGuid( 'TEST$NVS42' );

		$statement1 = new Statement( new PropertySomeValueSnak( 42 ) );
		$statement1->setGuid( 'TEST$SVS42' );

		$statements = array( $statement0, $statement1 );

		$item->setClaims( new Claims( $statements ) );
		$this->assertEquals( count( $statements ), $item->getStatements()->count(), "added some statements" );

		$item->setClaims( new Claims() );
		$this->assertTrue( $item->getStatements()->isEmpty(), "should be empty again" );
	}


	public function testEmptyItemReturnsEmptySiteLinkList() {
		$item = new Item();
		$this->assertTrue( $item->getSiteLinkList()->isEmpty() );
	}

	public function testAddSiteLinkOverridesOldLinks() {
		$item = new Item();

		$item->getSiteLinkList()->addSiteLink( new SiteLink( 'kittens', 'foo' ) );

		$newLink = new SiteLink( 'kittens', 'bar' );
		$item->addSiteLink( $newLink );

		$this->assertTrue( $item->getSiteLinkList()->getBySiteId( 'kittens' )->equals( $newLink ) );
	}

	public function testEmptyItemIsEmpty() {
		$item = new Item();
		$this->assertTrue( $item->isEmpty() );
	}

	public function testItemWithIdIsEmpty() {
		$item = new Item( new ItemId( 'Q1337' ) );
		$this->assertTrue( $item->isEmpty() );
	}

	public function testItemWithStuffIsNotEmpty() {
		$item = new Item();
		$item->getFingerprint()->setAliasGroup( 'en', array( 'foo' ) );
		$this->assertFalse( $item->isEmpty() );

		$item = new Item();
		$item->getSiteLinkList()->addNewSiteLink( 'en', 'o_O' );
		$this->assertFalse( $item->isEmpty() );

		$item = new Item();
		$item->getStatements()->addStatement( $this->newStatement() );
		$this->assertFalse( $item->isEmpty() );
	}

	public function testItemWithSitelinksHasSitelinks() {
		$item = new Item();
		$item->getSiteLinkList()->addNewSiteLink( 'en', 'foo' );
		$this->assertFalse( $item->getSiteLinkList()->isEmpty() );
	}

	public function testItemWithoutSitelinksHasNoSitelinks() {
		$item = new Item();
		$this->assertTrue( $item->getSiteLinkList()->isEmpty() );
	}

	private function newStatement() {
		$statement = new Statement( new PropertyNoValueSnak( 42 ) );
		$statement->setGuid( 'kittens' );
		return $statement;
	}

	public function testClearRemovesAllButId() {
		$item = new Item( new ItemId( 'Q42' ) );
		$item->getFingerprint()->setLabel( 'en', 'foo' );
		$item->getSiteLinkList()->addNewSiteLink( 'enwiki', 'Foo' );
		$item->getStatements()->addStatement( $this->newStatement() );

		$item->clear();

		$this->assertEquals( new ItemId( 'Q42' ), $item->getId() );
		$this->assertTrue( $item->getFingerprint()->isEmpty() );
		$this->assertTrue( $item->getSiteLinkList()->isEmpty() );
		$this->assertTrue( $item->getStatements()->isEmpty() );
	}

	public function testEmptyConstructor() {
		$item = new Item();

		$this->assertNull( $item->getId() );
		$this->assertTrue( $item->getFingerprint()->isEmpty() );
		$this->assertTrue( $item->getSiteLinkList()->isEmpty() );
		$this->assertTrue( $item->getStatements()->isEmpty() );
	}

	public function testCanConstructWithStatementList() {
		$statement = new Statement( new PropertyNoValueSnak( 42 ) );
		$statement->setGuid( 'meh' );

		$statements = new StatementList( $statement );

		$item = new Item( null, null, null, $statements );

		$this->assertEquals(
			$statements,
			$item->getStatements()
		);
	}

	public function testSetStatements() {
		$item = new Item();
		$item->getStatements()->addNewStatement( new PropertyNoValueSnak( 42 ) );

		$item->setStatements( new StatementList() );
		$this->assertTrue( $item->getStatements()->isEmpty() );
	}

	public function testGetStatementsReturnsCorrectTypeAfterClear() {
		$item = new Item();
		$item->clear();

		$this->assertTrue( $item->getStatements()->isEmpty() );
	}

	public function equalsProvider() {
		$firstItem = new Item();
		$firstItem->getStatements()->addNewStatement( new PropertyNoValueSnak( 42 ) );

		$secondItem = new Item();
		$secondItem->getStatements()->addNewStatement( new PropertyNoValueSnak( 42 ) );

		$secondItemWithId = unserialize( serialize( $secondItem ) );
		$secondItemWithId->setId( 42 );

		$differentId = unserialize( serialize( $secondItemWithId ) );
		$differentId->setId( 43 );

		return array(
			array( new Item(), new Item() ),
			array( $firstItem, $secondItem ),
			array( $secondItem, $secondItemWithId ),
			array( $secondItemWithId, $differentId ),
		);
	}

	/**
	 * @dataProvider equalsProvider
	 */
	public function testEquals( Item $firstItem, Item $secondItem ) {
		$this->assertTrue( $firstItem->equals( $secondItem ) );
		$this->assertTrue( $secondItem->equals( $firstItem ) );
	}

	private function getBaseItem() {
		$item = new Item( new ItemId( 'Q42' ) );
		$item->getFingerprint()->setLabel( 'en', 'Same' );
		$item->getFingerprint()->setDescription( 'en', 'Same' );
		$item->getFingerprint()->setAliasGroup( 'en', array( 'Same' ) );
		$item->getSiteLinkList()->addNewSiteLink( 'enwiki', 'Same' );
		$item->getStatements()->addNewStatement( new PropertyNoValueSnak( 42 ) );

		return $item;
	}

	public function notEqualsProvider() {
		$differentLabel = $this->getBaseItem();
		$differentLabel->getFingerprint()->setLabel( 'en', 'Different' );

		$differentDescription = $this->getBaseItem();
		$differentDescription->getFingerprint()->setDescription( 'en', 'Different' );

		$differentAlias = $this->getBaseItem();
		$differentAlias->getFingerprint()->setAliasGroup( 'en', array( 'Different' ) );

		$differentSiteLink = $this->getBaseItem();
		$differentSiteLink->getSiteLinkList()->removeLinkWithSiteId( 'enwiki' );
		$differentSiteLink->getSiteLinkList()->addNewSiteLink( 'enwiki', 'Different' );

		$differentStatement = $this->getBaseItem();
		$differentStatement->setStatements( new StatementList() );
		$differentStatement->getStatements()->addNewStatement( new PropertyNoValueSnak( 24 ) );

		$item = $this->getBaseItem();

		return array(
			'empty' => array( $item, new Item() ),
			'label' => array( $item, $differentLabel ),
			'description' => array( $item, $differentDescription ),
			'alias' => array( $item, $differentAlias ),
			'siteLink' => array( $item, $differentSiteLink ),
			'statement' => array( $item, $differentStatement ),
		);
	}

	/**
	 * @dataProvider notEqualsProvider
	 */
	public function testNotEquals( Item $firstItem, Item $secondItem ) {
		$this->assertFalse( $firstItem->equals( $secondItem ) );
		$this->assertFalse( $secondItem->equals( $firstItem ) );
	}

	public function testWhenNoStuffIsSet_getFingerprintReturnsEmptyFingerprint() {
		$item = new Item();

		$this->assertEquals(
			new Fingerprint(),
			$item->getFingerprint()
		);
	}

	public function testWhenLabelsAreSet_getFingerprintReturnsFingerprintWithLabels() {
		$item = new Item();

		$item->setLabel( 'en', 'foo' );
		$item->setLabel( 'de', 'bar' );

		$this->assertEquals(
			new Fingerprint(
				new TermList( array(
					new Term( 'en', 'foo' ),
					new Term( 'de', 'bar' ),
				) )
			),
			$item->getFingerprint()
		);
	}

	public function testWhenTermsAreSet_getFingerprintReturnsFingerprintWithTerms() {
		$item = new Item();

		$item->setLabel( 'en', 'foo' );
		$item->setDescription( 'en', 'foo bar' );
		$item->setAliases( 'en', array( 'foo', 'bar' ) );

		$this->assertEquals(
			new Fingerprint(
				new TermList( array(
					new Term( 'en', 'foo' ),
				) ),
				new TermList( array(
					new Term( 'en', 'foo bar' )
				) ),
				new AliasGroupList( array(
					new AliasGroup( 'en', array( 'foo', 'bar' ) )
				) )
			),
			$item->getFingerprint()
		);
	}

	public function testGivenEmptyFingerprint_noTermsAreSet() {
		$item = new Item();
		$item->setFingerprint( new Fingerprint() );

		$this->assertTrue( $item->getFingerprint()->isEmpty() );
	}

	public function testGivenEmptyFingerprint_existingTermsAreRemoved() {
		$item = new Item();

		$item->setLabel( 'en', 'foo' );
		$item->setDescription( 'en', 'foo bar' );
		$item->setAliases( 'en', array( 'foo', 'bar' ) );

		$item->setFingerprint( new Fingerprint() );

		$this->assertTrue( $item->getFingerprint()->isEmpty() );
	}

	public function testWhenSettingFingerprint_getFingerprintReturnsIt() {
		$fingerprint = new Fingerprint(
			new TermList( array(
				new Term( 'en', 'english label' ),
			) ),
			new TermList( array(
				new Term( 'en', 'english description' )
			) ),
			new AliasGroupList( array(
				new AliasGroup( 'en', array( 'first en alias', 'second en alias' ) )
			) )
		);

		$item = new Item();
		$item->setFingerprint( $fingerprint );
		$newFingerprint = $item->getFingerprint();

		$this->assertEquals( $fingerprint, $newFingerprint );
	}

}
