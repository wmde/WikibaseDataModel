<?php

namespace Wikibase\Test\Entity;

use DataValues\StringValue;
use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpChange;
use Diff\DiffOp\DiffOpRemove;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\EntityDiff;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Internal\ObjectComparer;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * Tests for the Wikibase\Entity deriving classes.
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
abstract class EntityTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Returns several more or less complex claims
	 *
	 * @return array
	 */
	public abstract function makeClaims();

	/**
	 * @since 0.1
	 *
	 * @return Entity
	 */
	protected abstract function getNewEmpty();

	/**
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return Entity
	 */
	protected abstract function getNewFromArray( array $data );

	public function labelProvider() {
		return array(
			array( 'en', 'spam' ),
			array( 'en', 'spam', 'spam' ),
			array( 'de', 'foo bar baz' ),
		);
	}

	/**
	 * @dataProvider labelProvider
	 * @param string $languageCode
	 * @param string $labelText
	 * @param string $moarText
	 */
	public function testSetLabel( $languageCode, $labelText, $moarText = 'ohi there' ) {
		$entity = $this->getNewEmpty();

		$entity->setLabel( $languageCode, $labelText );

		$this->assertEquals( $labelText, $entity->getLabel( $languageCode ) );

		$entity->setLabel( $languageCode, $moarText );

		$this->assertEquals( $moarText, $entity->getLabel( $languageCode ) );
	}

	/**
	 * @dataProvider labelProvider
	 * @param string $languageCode
	 * @param string $labelText
	 */
	public function testGetLabel( $languageCode, $labelText ) {
		$entity = $this->getNewEmpty();

		$this->assertFalse( $entity->getLabel( $languageCode ) );

		$entity->setLabel( $languageCode, $labelText );

		$this->assertEquals( $labelText, $entity->getLabel( $languageCode ) );
	}

	/**
	 * @dataProvider labelProvider
	 * @param string $languageCode
	 * @param string $labelText
	 */
	public function testRemoveLabel( $languageCode, $labelText ) {
		$entity = $this->getNewEmpty();
		$entity->setLabel( $languageCode, $labelText );
		$entity->removeLabel( $languageCode );
		$this->assertFalse( $entity->getLabel( $languageCode ) );

		$entity->setLabel( 'nl', 'sadefradtgsrduy' );
		$entity->setLabel( $languageCode, $labelText );
		$entity->removeLabel( array( $languageCode, 'nl' ) );
		$this->assertFalse( $entity->getLabel( $languageCode ) );
		$this->assertFalse( $entity->getLabel( 'nl' ) );
	}

	public function descriptionProvider() {
		return array(
			array( 'en', 'spam' ),
			array( 'en', 'spam', 'spam' ),
			array( 'de', 'foo bar baz' ),
		);
	}

	/**
	 * @dataProvider descriptionProvider
	 * @param string $languageCode
	 * @param string $labelText
	 * @param string $moarText
	 */
	public function testSetDescription( $languageCode, $labelText, $moarText = 'ohi there' ) {
		$entity = $this->getNewEmpty();

		$entity->setDescription( $languageCode, $labelText );

		$this->assertEquals( $labelText, $entity->getDescription( $languageCode ) );

		$entity->setDescription( $languageCode, $moarText );

		$this->assertEquals( $moarText, $entity->getDescription( $languageCode ) );
	}

	/**
	 * @dataProvider descriptionProvider
	 * @param string $languageCode
	 * @param string $labelText
	 */
	public function testGetDescription( $languageCode, $labelText ) {
		$entity = $this->getNewEmpty();

		$this->assertFalse( $entity->getDescription( $languageCode ) );

		$entity->setDescription( $languageCode, $labelText );

		$this->assertEquals( $labelText, $entity->getDescription( $languageCode ) );
	}

	/**
	 * @dataProvider descriptionProvider
	 * @param string $languageCode
	 * @param string $labelText
	 */
	public function testRemoveDescription( $languageCode, $labelText ) {
		$entity = $this->getNewEmpty();
		$entity->setDescription( $languageCode, $labelText );
		$entity->removeDescription( $languageCode );
		$this->assertFalse( $entity->getDescription( $languageCode ) );

		$entity->setDescription( 'nl', 'sadefradtgsrduy' );
		$entity->setDescription( $languageCode, $labelText );
		$entity->removeDescription( array( $languageCode, 'nl' ) );
		$this->assertFalse( $entity->getDescription( $languageCode ) );
		$this->assertFalse( $entity->getDescription( 'nl' ) );
	}

	public function aliasesProvider() {
		return array(
			array( array(
				'en' => array( array( 'spam' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar', 'baz' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar' ), array( 'baz', 'spam' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar', 'baz' ) ),
				'de' => array( array( 'foobar' ), array( 'baz' ) ),
			) ),
			// with duplicates
			array( array(
				'en' => array( array( 'spam', 'ham', 'ham' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar' ), array( 'bar', 'spam' ) )
			) ),
		);
	}

	/**
	 * @dataProvider aliasesProvider
	 */
	public function testAddAliases( array $aliasesLists ) {
		$entity = $this->getNewEmpty();

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			foreach ( $aliasesList as $aliases ) {
				$entity->addAliases( $langCode, $aliases );
			}
		}

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			$expected = array_values( array_unique( call_user_func_array( 'array_merge', $aliasesList ) ) );
			asort( $expected );

			$actual = $entity->getAliases( $langCode );
			asort( $actual );

			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * @dataProvider aliasesProvider
	 */
	public function testSetAliases( array $aliasesLists ) {
		$entity = $this->getNewEmpty();

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			foreach ( $aliasesList as $aliases ) {
				$entity->setAliases( $langCode, $aliases );
			}
		}

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			$expected = array_values( array_unique( array_pop( $aliasesList ) ) );
			asort( $aliasesList );

			$actual = $entity->getAliases( $langCode );
			asort( $actual );

			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * @dataProvider aliasesProvider
	 */
	public function testSetEmptyAlias( array $aliasesLists ) {
		$entity = $this->getNewEmpty();

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			foreach ( $aliasesList as $aliases ) {
				$entity->setAliases( $langCode, $aliases );
			}
		}
		$entity->setAliases( 'zh', array( 'wind', 'air', '', 'fire') );
		$entity->setAliases( 'zu', array( '', '') );

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			$expected = array_values( array_unique( array_pop( $aliasesList ) ) );
			asort( $aliasesList );

			$actual = $entity->getAliases( $langCode );
			asort( $actual );

			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * @dataProvider aliasesProvider
	 */
	public function testSetAllAliases( array $aliasesLists ) {

		$entity = $this->getNewEmpty();
		$entity->addAliases( 'zh' , array( 'qwertyuiop123' , '321poiuytrewq' ) );

		$aliasesToSet = array();
		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			foreach ( $aliasesList as $aliases ) {
				$aliasesToSet[$langCode]= $aliases;
			}
		}

		$entity->setAllAliases( $aliasesToSet );

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			$expected = array_values( array_unique( array_pop( $aliasesList ) ) );
			asort( $aliasesList );

			$actual = $entity->getAliases( $langCode );
			asort( $actual );

			$this->assertEquals( $expected, $actual );
		}

		foreach ( $entity->getAllAliases() as $langCode => $aliasesList ) {
			$this->assertEquals( $aliasesList , array_unique( $aliasesToSet[$langCode] ) );
		}

	}

	/**
	 * @dataProvider aliasesProvider
	 */
	public function testGetAliases( array $aliasesLists ) {
		$entity = $this->getNewEmpty();

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			$expected = array_unique( array_shift( $aliasesList ) );
			$entity->setAliases( $langCode, $expected );
			$actual = $entity->getAliases( $langCode );

			asort( $expected );
			asort( $actual );

			$this->assertEquals( $expected, $actual );
		}
	}

	public function duplicateAliasesProvider() {
		return array(
			array( array(
				'en' => array( array( 'foo', 'bar', 'baz' ), array( 'foo', 'bar', 'baz' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar', 'baz' ), array( 'foo', 'bar' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar' ), array( 'foo', 'bar', 'baz' ) )
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar' ), array( 'bar', 'baz' ) ),
				'de' => array( array(), array( 'foo' ) ),
				'nl' => array( array( 'foo' ), array() ),
			) ),
			array( array(
				'en' => array( array( 'foo', 'bar', 'baz' ), array( 'foo', 'bar', 'baz', 'foo', 'bar' ) )
			) ),
		);
	}

	/**
	 * @dataProvider duplicateAliasesProvider
	 */
	public function testRemoveAliases( array $aliasesLists ) {
		$entity = $this->getNewEmpty();

		foreach ( $aliasesLists as $langCode => $aliasesList ) {
			$aliases = array_shift( $aliasesList );
			$removedAliases =  array_shift( $aliasesList );

			$entity->setAliases( $langCode, $aliases );
			$entity->removeAliases( $langCode, $removedAliases );

			$expected = array_values( array_diff( $aliases, $removedAliases ) );
			$actual = $entity->getAliases( $langCode );

			asort( $expected );
			asort( $actual );

			$this->assertEquals( $expected, $actual );
		}
	}

	public function testIsEmpty() {
		$entity = $this->getNewEmpty();

		$this->assertTrue( $entity->isEmpty() );

		$entity->addAliases( 'en', array( 'ohi' ) );

		$this->assertFalse( $entity->isEmpty() );

		$entity = $this->getNewEmpty();
		$entity->setDescription( 'en', 'o_O' );

		$this->assertFalse( $entity->isEmpty() );

		$entity = $this->getNewEmpty();
		$entity->setLabel( 'en', 'o_O' );

		$this->assertFalse( $entity->isEmpty() );
	}

	public function testClear() {
		$entity = $this->getNewEmpty();

		$entity->addAliases( 'en', array( 'ohi' ) );
		$entity->setDescription( 'en', 'o_O' );
		$entity->setLabel( 'en', 'o_O' );

		$claim = new Claim( new PropertyNoValueSnak( new PropertyId( 'P42' ) ) );
		$claim->setGuid( 'foo-bar-foz' );
		$entity->addClaim( $claim );

		$entity->clear();

		$this->assertEmpty( $entity->getLabels(), "labels" );
		$this->assertEmpty( $entity->getDescriptions(), "descriptions" );
		$this->assertEmpty( $entity->getAllAliases(), "aliases" );
		$this->assertEmpty( $entity->getClaims(), "claims" );

		$this->assertTrue( $entity->isEmpty() );
	}

	public static function provideEquals() {
		return array(
			array( #0
				array(),
				array(),
				true
			),
			array( #1
				array( 'labels' => array() ),
				array( 'descriptions' => null ),
				true
			),
			array( #2
				array( 'entity' => array( 'item', 23 ) ),
				array(),
				true
			),
			array( #3
				array( 'entity' => array( 'item', 23 ) ),
				array( 'entity' => array( 'item', 24 ) ),
				true
			),
			array( #4
				array( 'labels' => array(
					'en' => 'foo',
					'de' => 'bar',
				) ),
				array( 'labels' => array(
					'en' => 'foo',
				) ),
				false
			),
			array( #5
				array( 'labels' => array(
					'en' => 'foo',
					'de' => 'bar',
				) ),
				array( 'labels' => array(
					'de' => 'bar',
					'en' => 'foo',
				) ),
				true
			),
			array( #6
				array( 'aliases' => array(
					'en' => array( 'foo', 'FOO' ),
				) ),
				array( 'aliases' => array(
					'en' => array( 'foo', 'FOO', 'xyz' ),
				) ),
				false
			),
		);
	}

	/**
	 * @covers Wikibase\DataModel\Entity\Entity::equals
	 * @covers Wikibase\DataModel\Internal\ObjectComparer::dataEquals
	 *
	 * @dataProvider provideEquals
	 */
	public function testEquals( array $a, array $b, $equals ) {
		$itemA = $this->getNewFromArray( $a );
		$itemB = $this->getNewFromArray( $b );

		$this->assertEquals( $equals, $itemA->equals( $itemB ) );
		$this->assertEquals( $equals, $itemB->equals( $itemA ) );
	}

	public function instanceProvider() {
		$entities = array();

		// empty
		$entity = $this->getNewEmpty();
		$entities[] = $entity;

		// ID only
		$entity = clone $entity;
		$entity->setId( 44 );

		$entities[] = $entity;

		// with labels and stuff
		$entity = $this->getNewEmpty();
		$entity->setAliases( 'en', array( 'o', 'noez' ) );
		$entity->setLabel( 'de', 'spam' );
		$entity->setDescription( 'en', 'foo bar baz' );

		$entities[] = $entity;

		// with labels etc and ID
		$entity = clone $entity;
		$entity->setId( 42 );

		$entities[] = $entity;

		// With claims
		$entity = $this->getNewEmpty();
		$entity->setClaims( new Claims( $this->makeClaims() ) );
		$entity->setId( 55 );

		$entities[] = $entity;

		$argLists = array();

		foreach ( $entities as $entity ) {
			$argLists[] = array( $entity );
		}

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testStub( Entity $entity ) {
		$copy = $entity->copy();
		$entity->stub();

		$this->assertTrue( $entity->equals( $copy ) );
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testCopy( Entity $entity ) {
		$copy = $entity->copy();

		// The equality method alone is not enough since it does not check the IDs.
		$this->assertTrue( $entity->equals( $copy ) );
		$this->assertEquals( $entity->getId(), $copy->getId() );

		// More checks that should also pass
		$this->assertEquals( $entity->toArray(), $copy->toArray() );
		$this->assertFalse( $entity === $copy );
	}


	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testSerialize( Entity $entity ) {
		$string = serialize( $entity );

		$this->assertInternalType( 'string', $string );

		$instance = unserialize( $string );

		$this->assertTrue( $entity->equals( $instance ) );
		$this->assertEquals( $entity->getId(), $instance->getId() );
	}

	public function oldSerializationProvider() {
		$serializations = array();

		// Empty item
		$serializations[] = array(
			'O:19:"Wikibase\ItemObject":3:{s:13:" * statements";N;s:7:" * data";a:5:{s:5:"label";a:0:{}s:11:"description";a:0:{}s:7:"aliases";a:0:{}s:5:"links";a:0:{}s:10:"statements";a:0:{}}s:5:" * id";b:0;}',
			null
		);

		// Id 42, set both in the internal array and the id field
		$serializations[] = array(
			'O:19:"Wikibase\ItemObject":3:{s:13:" * statements";N;s:7:" * data";a:6:{s:6:"entity";s:3:"q42";s:5:"label";a:0:{}s:11:"description";a:0:{}s:7:"aliases";a:0:{}s:5:"links";a:0:{}s:10:"statements";a:0:{}}s:5:" * id";i:42;}',
			42
		);

		// Id 42, only set as id field
		$serializations[] = array(
			'O:19:"Wikibase\ItemObject":3:{s:13:" * statements";N;s:7:" * data";a:5:{s:5:"label";a:0:{}s:11:"description";a:0:{}s:7:"aliases";a:0:{}s:5:"links";a:0:{}s:10:"statements";a:0:{}}s:5:" * id";i:42;}',
			42
		);

		return $serializations;
	}

	/**
	 * @dataProvider oldSerializationProvider
	 */
	public function testUnserializeCompat( $oldSerialization, $expectedId ) {
		/**
		 * @var Entity $instance
		 */
		$instance = unserialize( $oldSerialization );

		if ( $expectedId === null ) {
			$thisData = $instance->toArray();
			$thatData = Item::newEmpty()->toArray();

			$comparer = new ObjectComparer();
			$equals = $comparer->dataEquals( $thisData, $thatData, array( 'entity' ) );

			$this->assertTrue( $equals );
		}
		else {
			$this->assertEquals( $expectedId, $instance->getId()->getNumericId() );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testHasClaims( Entity $entity ) {
		$has = $entity->hasClaims();
		$this->assertInternalType( 'boolean', $has );

		$this->assertEquals( count( $entity->getClaims() ) !== 0, $has );
	}

	/**
	 * Tests Entity::newClaim and Entity::getIdFromClaimGuid
	 *
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testNewClaim( Entity $entity ) {
		if ( $entity->getId() === null ) {
			$entity->setId( new EntityId( $entity->getType(), 50 ) );
		}

		$snak = new PropertyNoValueSnak( 42 );
		$claim = new Statement( $snak );
		$claim->setGuid( 'q42$foobarbaz' );

		$this->assertInstanceOf( '\Wikibase\Claim', $claim );

		$this->assertTrue( $snak->equals( $claim->getMainSnak() ) );

		$guid = $claim->getGuid();

		$this->assertInternalType( 'string', $guid );

		$prefixedEntityId = Entity::getIdFromClaimGuid( $guid );

		$this->assertEquals( 'q42', $prefixedEntityId );
	}

	public function diffProvider() {
		$argLists = array();

		$emptyDiff = EntityDiff::newForType( $this->getNewEmpty()->getType() );

		$entity0 = $this->getNewEmpty();
		$entity1 = $this->getNewEmpty();
		$expected = clone $emptyDiff;

		$argLists[] = array( $entity0, $entity1, $expected );

		$entity0 = $this->getNewEmpty();
		$entity0->addAliases( 'nl', array( 'bah' ) );
		$entity0->addAliases( 'de', array( 'bah' ) );

		$entity1 = $this->getNewEmpty();
		$entity1->addAliases( 'en', array( 'foo', 'bar' ) );
		$entity1->addAliases( 'nl', array( 'bah', 'baz' ) );

		$entity1->setDescription( 'en', 'onoez' );

		$expected = new EntityDiff( array(
			'aliases' => new Diff( array(
				'en' => new Diff( array(
					new DiffOpAdd( 'foo' ),
					new DiffOpAdd( 'bar' ),
				), false ),
				'de' => new Diff( array(
					new DiffOpRemove( 'bah' ),
				), false ),
				'nl' => new Diff( array(
					new DiffOpAdd( 'baz' ),
				), false )
			) ),
			'description' => new Diff( array(
				'en' => new DiffOpAdd( 'onoez' ),
			) ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );

		$entity0 = clone $entity1;
		$entity1 = clone $entity1;
		$expected = clone $emptyDiff;

		$argLists[] = array( $entity0, $entity1, $expected );

		$entity0 = $this->getNewEmpty();

		$entity1 = $this->getNewEmpty();
		$entity1->setLabel( 'en', 'onoez' );

		$expected = new EntityDiff( array(
			'label' => new Diff( array(
				'en' => new DiffOpAdd( 'onoez' ),
			) ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );

		return $argLists;
	}

	/**
	 * @dataProvider diffProvider
	 *
	 * @param Entity $entity0
	 * @param Entity $entity1
	 * @param EntityDiff $expected
	 */
	public function testDiffEntities( Entity $entity0, Entity $entity1, EntityDiff $expected ) {
		$actual = $entity0->getDiff( $entity1 );

		$this->assertInstanceOf( '\Wikibase\EntityDiff', $actual );
		$this->assertEquals( count( $expected ), count( $actual ) );

		// TODO: equality check
		// (simple serialize does not work, since the order is not relevant, and not only on the top level)
	}

	public function patchProvider() {
		$claim0 = new Claim( new PropertyNoValueSnak( 42 ) );
		$claim1 = new Claim( new PropertySomeValueSnak( 42 ) );
		$claim2 = new Claim( new PropertyValueSnak( 42, new StringValue( 'ohi' ) ) );
		$claim3 = new Claim( new PropertyNoValueSnak( 1 ) );

		$claim0->setGuid( 'claim0' );
		$claim1->setGuid( 'claim1' );
		$claim2->setGuid( 'claim2' );
		$claim3->setGuid( 'claim3' );

		$argLists = array();


		$source = $this->getNewEmpty();
		$patch = new EntityDiff();
		$expected = clone $source;

		$argLists[] = array( $source, $patch, $expected );


		$source = $this->getNewEmpty();
		$source->setLabel( 'en', 'foo' );
		$source->setLabel( 'nl', 'bar' );
		$source->setDescription( 'de', 'foobar' );
		$source->setAliases( 'en', array( 'baz', 'bah' ) );
		$source->addClaim( $claim1 );

		$patch = new EntityDiff();
		$expected = clone $source;

		$argLists[] = array( $source, $patch, $expected );


		$source = clone $source;

		$patch = new EntityDiff( array(
			'description' => new Diff( array(
				'de' => new DiffOpChange( 'foobar', 'onoez' ),
				'en' => new DiffOpAdd( 'foobar' ),
			), true ),
		) );
		$expected = clone $source;
		$expected->setDescription( 'de', 'onoez' );
		$expected->setDescription( 'en', 'foobar' );

		$argLists[] = array( $source, $patch, $expected );


		$source = $this->getNewEmpty();
		$source->addClaim( $claim0 );
		$source->addClaim( $claim1 );
		$patch = new EntityDiff( array( 'claim' => new Diff( array(
			'claim0' => new DiffOpRemove( $claim0 ),
			'claim2' => new DiffOpAdd( $claim2 ),
			'claim3' => new DiffOpAdd( $claim3 )
		), false ) ) );
		$expected = $this->getNewEmpty();
		$expected->addClaim( $claim1 );
		$expected->addClaim( $claim2 );
		$expected->addClaim( $claim3 );

		$argLists[] = array( $source, $patch, $expected );

		return $argLists;
	}

	/**
	 * @dataProvider patchProvider
	 *
	 * @param Entity $source
	 * @param EntityDiff $patch
	 * @param Entity $expected
	 */
	public function testPatch( Entity $source, EntityDiff $patch, Entity $expected ) {
		$source->patch( $patch );
		$this->assertTrue( $expected->equals( $source ) );
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testGetClaims( Entity $entity ) {
		$claims = $entity->getClaims();

		$this->assertInternalType( 'array', $claims );
	}

	public function testSetClaims() {
		$entity = $this->getNewEmpty();
		$this->assertCount( 0, $entity->getClaims(), "initially, no claims" );

		$claims = array(
			$claim0 = new Claim( new PropertyNoValueSnak( 42 ) ),
			$claim1 = new Claim( new PropertySomeValueSnak( 42 ) ),
		);

		$claims[0]->setGuid( 'TEST$NVS42' );
		$claims[1]->setGuid( 'TEST$SVS42' );

		$entity->setClaims( new Claims( $claims ) );
		$this->assertSameSize( $claims, $entity->getClaims(), "added some claims" );

		$entity->setClaims( new Claims() );
		$this->assertCount( 0, $entity->getClaims(), "should be empty again" );
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Entity $entity
	 */
	public function testGetAllSnaks( Entity $entity ) {
		$snaks = $entity->getAllSnaks();
		$claims = $entity->getClaims();

		$this->assertInternalType( 'array', $snaks );

		$this->assertGreaterThanOrEqual( count( $claims ), count( $snaks ), "At least one snak per Claim" );

		foreach ( $claims as $claim ) {
			$snak = $claim->getMainSnak();
			$this->assertContains( $snak, $snaks, "main snak" );

			$qualifiers = $claim->getQualifiers();

			// check the first qualifier
			foreach ( $qualifiers as $snak ) {
				$this->assertContains( $snak, $snaks, "qualifier snak" );
			}

			// check the first reference
			if ( $claim instanceof Statement ) {
				$references = $claim->getReferences();

				/* @var Reference $ref */
				foreach ( $references as $ref ) {
					$refSnaks = $ref->getSnaks();

					foreach ( $refSnaks as $snak ) {
						$this->assertContains( $snak, $snaks, "reference snak" );
					}
				}
			}
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testArraySerlialzationRoundtrip( Entity $entity ) {
		$class = get_class( $entity );

		/**
		 * @var Entity $newEntity
		 */
		$newEntity = new $class( $entity->toArray() );

		$entity->stub();
		$newEntity->stub();

		$this->assertEquals( $entity, $newEntity );
	}

	public function testWhenNoStuffIsSet_getFingerprintReturnsEmptyFingerprint() {
		$entity = $this->getNewEmpty();

		$this->assertEquals(
			Fingerprint::newEmpty(),
			$entity->getFingerprint()
		);
	}

	public function testWhenLabelsAreSet_getFingerprintReturnsFingerprintWithLabels() {
		$entity = $this->getNewEmpty();

		$entity->setLabel( 'en', 'foo' );
		$entity->setLabel( 'de', 'bar' );

		$this->assertEquals(
			new Fingerprint(
				new TermList( array(
					new Term( 'en', 'foo' ),
					new Term( 'de', 'bar' ),
				) ),
				new TermList( array() ),
				new AliasGroupList( array() )
			),
			$entity->getFingerprint()
		);
	}

	public function testWhenTermsAreSet_getFingerprintReturnsFingerprintWithTerms() {
		$entity = $this->getNewEmpty();

		$entity->setLabel( 'en', 'foo' );
		$entity->setDescription( 'en', 'foo bar' );
		$entity->setAliases( 'en', array( 'foo', 'bar' ) );

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
			$entity->getFingerprint()
		);
	}

	public function testGivenEmptyFingerprint_noTermsAreSet() {
		$entity = $this->getNewEmpty();

		$entity->setFingerprint( Fingerprint::newEmpty() );

		$this->assertHasNoTerms( $entity );
	}

	private function assertHasNoTerms( Entity $entity ) {
		$this->assertEquals( array(), $entity->getLabels() );
		$this->assertEquals( array(), $entity->getDescriptions() );
		$this->assertEquals( array(), $entity->getAllAliases() );
	}

	public function testGivenEmptyFingerprint_existingTermsAreRemoved() {
		$entity = $this->getNewEmpty();

		$entity->setLabel( 'en', 'foo' );
		$entity->setDescription( 'en', 'foo bar' );
		$entity->setAliases( 'en', array( 'foo', 'bar' ) );

		$entity->setFingerprint( Fingerprint::newEmpty() );

		$this->assertHasNoTerms( $entity );
	}

	public function testWhenSettingFingerprint_getFingerprintReturnsIt() {
		$fingerprint = new Fingerprint(
			new TermList( array(
				new Term( 'en', 'foo' ),
			) ),
			new TermList( array(
				new Term( 'en', 'foo bar' )
			) ),
			new AliasGroupList( array(
				new AliasGroup( 'en', array( 'foo', 'bar' ) )
			) )
		);

		$entity = $this->getNewEmpty();

		$entity->setFingerprint( $fingerprint );
		$newFingerprint = $entity->getFingerprint();

		$this->assertEquals( $fingerprint, $newFingerprint );
	}

}
