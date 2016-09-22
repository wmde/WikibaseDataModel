<?php

namespace Wikibase\DataModel\Tests\Entity;

use ReflectionClass;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers Wikibase\DataModel\Entity\EntityId
 * @uses Wikibase\DataModel\Entity\ItemId
 * @uses Wikibase\DataModel\Entity\PropertyId
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group EntityIdTest
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author John Erling Blad < jeblad@gmail.com >
 */
class EntityIdTest extends \PHPUnit_Framework_TestCase {

	public function instanceProvider() {
		$ids = array();

		$ids[] = array( new ItemId( 'Q1' ), '' );
		$ids[] = array( new ItemId( 'Q42' ), '' );
		$ids[] = array( new ItemId( 'Q31337' ), '' );
		$ids[] = array( new ItemId( 'Q2147483647' ), '' );
		$ids[] = array( new ItemId( 'Q2147483647' ), '' );
		$ids[] = array( new ItemId( 'Q2147483647', 'foo' ), 'foo' );
		$ids[] = array( new PropertyId( 'P101010' ), '' );
		$ids[] = array( new PropertyId( 'bar:P101010', 'foo' ), 'foo' );

		return $ids;
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testEqualsSimple( EntityId $id ) {
		$this->assertTrue( $id->equals( $id ) );
		$this->assertTrue( $id->equals( unserialize( serialize( $id ) ) ) );
		$this->assertFalse( $id->equals( $id->getSerialization() ) );
		$this->assertFalse( $id->equals( $id->getEntityType() ) );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSerializationRoundtrip( EntityId $id ) {
		$this->assertEquals( $id, unserialize( serialize( $id ) ) );
	}

	public function testDeserializationCompatibility() {
		$v05serialization = 'C:32:"Wikibase\DataModel\Entity\ItemId":15:{["item","Q123"]}';

		$this->assertEquals(
			new ItemId( 'q123' ),
			unserialize( $v05serialization )
		);
	}

	/**
	 * This test will change when the serialization format changes.
	 * If it is being changed intentionally, the test should be updated.
	 * It is just here to catch unintentional changes.
	 */
	public function testSerializationStability() {
		$v05serialization = 'C:32:"Wikibase\DataModel\Entity\ItemId":15:{["item","Q123"]}';
		$id = new ItemId( 'q123' );

		$this->assertEquals(
			serialize( $id ),
			$v05serialization
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testReturnTypeOfToString( EntityId $id ) {
		$this->assertInternalType( 'string', $id->__toString() );
	}

	public function testIsForeign() {
		$this->assertFalse( ( new ItemId( 'Q42' ) )->isForeign() );
		$this->assertFalse( ( new ItemId( ':Q42' ) )->isForeign() );
		$this->assertFalse( ( new PropertyId( ':P42' ) )->isForeign() );

		$this->assertTrue( ( new ItemId( 'Q42', 'foo' ) )->isForeign() );
		$this->assertTrue( ( new PropertyId( 'P42', 'foo' ) )->isForeign() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetRepoName( EntityId $id, $repoName ) {
		$this->assertSame( $repoName, $id->getRepoName() );
	}

	public function serializationSplitProvider() {
		return array(
			array( 'Q42', array( '', '', 'Q42' ) ),
			array( 'foo:Q42', array( 'foo', '', 'Q42' ) ),
			array( '0:Q42', array( '0', '', 'Q42' ) ),
			array( 'foo:bar:baz:Q42', array( 'foo', 'bar:baz', 'Q42' ) ),
		);
	}

	public function invalidJoinSerializationDataProvider() {
		return array(
			array( array( 'Q42', '', '' ) ),
			array( array( '', 'Q42', '' ) ),
			array( array( 'foo', 'Q42', '' ) ),
		);
	}

	/**
	 * @dataProvider localPartDataProvider
	 */
	public function testGetLocalPart( EntityId $id, $localPart ) {
		$this->assertSame( $localPart, $id->getLocalPart() );
	}

	public function localPartDataProvider() {
		return array(
			array( new ItemId( 'Q42' ), 'Q42' ),
			array( new ItemId( 'Q42', 'foo' ), 'Q42' ),
			array( new ItemId( 'bar:Q42', 'foo' ), 'bar:Q42' ),
		);
	}

}
