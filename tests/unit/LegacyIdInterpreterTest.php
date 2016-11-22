<?php

namespace Wikibase\DataModel\Tests;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\LegacyIdInterpreter;

/**
 * @covers Wikibase\DataModel\LegacyIdInterpreter
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @license GPL-2.0+
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class LegacyIdInterpreterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider idProvider
	 */
	public function testNewIdFromTypeAndNumber( EntityId $expected, $type, $number ) {
		$actual = LegacyIdInterpreter::newIdFromTypeAndNumber( $type, $number );

		$this->assertEquals( $actual, $expected );
	}

	public function idProvider() {
		return [
			[ new ItemId( 'Q42' ), 'item', 42 ],
			[ new PropertyId( 'P42' ), 'property', 42 ],
		];
	}

	public function testNewIdFromTypeAndNumberWithRepositoryName() {
		$fooItemId = LegacyIdInterpreter::newIdFromTypeAndNumber( 'item', 42, 'foo' );
		$fooPropertyId = LegacyIdInterpreter::newIdFromTypeAndNumber( 'property', 42, 'foo' );

		$this->assertEquals( new ItemId( 'foo:Q42' ), $fooItemId );
		$this->assertEquals( new PropertyId( 'foo:P42' ), $fooPropertyId );
	}

	/**
	 * @dataProvider invalidInputProvider
	 */
	public function testNewIdFromTypeAndNumber_withInvalidInput( $type, $number ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		LegacyIdInterpreter::newIdFromTypeAndNumber( $type, $number );
	}

	public function invalidInputProvider() {
		return [
			[ 'kittens', 42 ],
			[ 'item', [ 'kittens' ] ],
			[ 'item', true ],
		];
	}

}
