<?php

namespace Wikibase\DataModel\Tests\Entity;

use Wikibase\DataModel\Entity\EntityRedirect;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers Wikibase\DataModel\Entity\EntityRedirect
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @license GPL-2.0+
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

	public function equalsProvider() {
		$q123 = new ItemId( 'Q123' );
		$q345 = new ItemId( 'Q345' );
		$q567 = new ItemId( 'Q567' );
		$q123_345 = new EntityRedirect( $q123, $q345 );

		$p123 = new PropertyId( 'P123' );
		$p345 = new PropertyId( 'P345' );
		$p123_345 = new EntityRedirect( $p123, $p345 );

		return [
			'same' => [ $q123_345, $q123_345, true ],
			'equal' => [ $q123_345, new EntityRedirect( $q123, $q345 ), true ],

			'different base' => [ $q123_345, new EntityRedirect( $q567, $q345 ), false ],
			'different target' => [ $q123_345, new EntityRedirect( $q123, $q567 ), false ],

			'different entity type' => [ $q123_345, $p123_345, false ],
			'different number' => [ $q123_345, new EntityRedirect( $q345, $q123 ), false ],

			'null' => [ $q123_345, null, false ],
			'string' => [ $q123_345, 'foo', false ],
			'id' => [ $q123_345, $q123, false ],
		];
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

	public function testToString() {
		$redirect = new EntityRedirect( new ItemId( 'Q1' ), new ItemId( 'Q2' ) );
		$this->assertSame( 'Q1->Q2', $redirect->__toString() );
	}

}
