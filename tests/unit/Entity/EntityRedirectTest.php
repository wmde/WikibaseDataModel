<?php

namespace Wikibase\DataModel\Tests\Entity;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityRedirect;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers Wikibase\DataModel\Entity\EntityRedirect
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class EntityRedirectTest extends \PHPUnit_Framework_TestCase {

	public function testConstruction() {
		$entityId = new ItemId( 'Q123' );
		$targetId = new ItemId( 'Q345' );

		$redirect = new EntityRedirect( $entityId, $targetId );

		$this->assertEquals( $entityId, $redirect->getEntityId(), '$redirect->getEntityId()' );
		$this->assertEquals( $targetId, $redirect->getTargetId(), '$redirect->getTargetId()' );
	}

	public function testConstruction_baseType() {
		$this->setExpectedException( 'InvalidArgumentException' );

		$entityId = new ItemId( 'Q123' );
		$targetId = new PropertyId( 'P345' );

		new EntityRedirect( $entityId, $targetId );
	}

	/**
	 * @dataProvider selfRedirectProvider
	 */
	public function testConstruction_selfRedirect( EntityId $entityId, EntityId $targetId ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new EntityRedirect( $entityId, $targetId );
	}

	public function selfRedirectProvider() {
		$entityId = new ItemId( 'Q1' );

		return array(
			'same object' => array( $entityId, $entityId ),
			'different objects' => array( $entityId, new ItemId( 'Q1' ) ),
		);
	}

	public function equalsProvider() {
		$q123 = new ItemId( 'Q123' );
		$q345 = new ItemId( 'Q345' );
		$q567 = new ItemId( 'Q567' );
		$q123_345 = new EntityRedirect( $q123, $q345 );

		$p123 = new PropertyId( 'P123' );
		$p345 = new PropertyId( 'P345' );
		$p123_345 = new EntityRedirect( $p123, $p345 );

		return array(
			'same' => array( $q123_345, $q123_345, true ),
			'equal' => array( $q123_345, new EntityRedirect( $q123, $q345 ), true ),

			'different base' => array( $q123_345, new EntityRedirect( $q567, $q345 ), false ),
			'different target' => array( $q123_345, new EntityRedirect( $q123, $q567 ), false ),

			'different entity type' => array( $q123_345, $p123_345, false ),
			'different number' => array( $q123_345, new EntityRedirect( $q345, $q123 ), false ),

			'null' => array( $q123_345, null, false ),
			'string' => array( $q123_345, 'foo', false ),
			'id' => array( $q123_345, $q123, false ),
		);
	}

	/**
	 * @dataProvider equalsProvider
	 *
	 * @param EntityRedirect $a
	 * @param mixed $b
	 * @param bool $expected
	 */
	public function testEquals( $a, $b, $expected ) {
		$this->assertEquals( $expected, $a->equals( $b ), '$a->equals( $b )' );

		if ( $b instanceof EntityRedirect ) {
			$this->assertEquals( $expected, $b->equals( $a ), '$b->equals( $a )' );
		}
	}

}
