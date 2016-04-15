<?php

namespace Wikibase\DataModel\Tests\Entity;

use PHPUnit_Framework_TestCase;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers Wikibase\DataModel\Entity\ItemId
 * @covers Wikibase\DataModel\Entity\EntityId
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group EntityIdTest
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ItemIdTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider idSerializationProvider
	 */
	public function testCanConstructId( $idSerialization ) {
		$id = new ItemId( $idSerialization );

		$this->assertEquals(
			strtoupper( $idSerialization ),
			$id->getSerialization()
		);
	}

	public function idSerializationProvider() {
		return [
			[ 'q1' ],
			[ 'q100' ],
			[ 'q1337' ],
			[ 'q31337' ],
			[ 'Q31337' ],
			[ 'Q42' ],
			[ 'Q2147483648' ],
		];
	}

	/**
	 * @dataProvider invalidIdSerializationProvider
	 */
	public function testCannotConstructWithInvalidSerialization( $invalidSerialization ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new ItemId( $invalidSerialization );
	}

	public function invalidIdSerializationProvider() {
		return [
			[ 'q' ],
			[ 'p1' ],
			[ 'qq1' ],
			[ '1q' ],
			[ 'q01' ],
			[ 'q 1' ],
			[ ' q1' ],
			[ 'q1 ' ],
			[ '1' ],
			[ ' ' ],
			[ '' ],
			[ '0' ],
			[ 0 ],
			[ 1 ],
		];
	}

	public function testGetNumericId() {
		$id = new ItemId( 'Q1' );
		$this->assertSame( 1, $id->getNumericId() );
	}

	public function testGetEntityType() {
		$id = new ItemId( 'Q1' );
		$this->assertSame( 'item', $id->getEntityType() );
	}

	public function testSerialize() {
		$id = new ItemId( 'Q1' );
		$this->assertSame( '["item","Q1"]', $id->serialize() );
	}

	/**
	 * @dataProvider serializationProvider
	 */
	public function testUnserialize( $json, $expected ) {
		$id = new ItemId( 'Q1' );
		$id->unserialize( $json );
		$this->assertSame( $expected, $id->getSerialization() );
	}

	public function serializationProvider() {
		return [
			[ '["item","Q2"]', 'Q2' ],

			// All these cases are kind of an injection vector and allow constructing invalid ids.
			[ '["string","Q2"]', 'Q2' ],
			[ '["","string"]', 'string' ],
			[ '["",""]', '' ],
			[ '["",2]', 2 ],
			[ '["",null]', null ],
			[ '', null ],
		];
	}

	/**
	 * @dataProvider numericIdProvider
	 */
	public function testNewFromNumber( $number ) {
		$id = ItemId::newFromNumber( $number );
		$this->assertEquals( 'Q' . $number, $id->getSerialization() );
	}

	public function numericIdProvider() {
		return [
			[ 42 ],
			[ '42' ],
			[ 42.0 ],
			// Check for 32-bit integer overflow on 32-bit PHP systems.
			[ 2147483648 ],
			[ '2147483648' ],
		];
	}

	/**
	 * @dataProvider invalidNumericIdProvider
	 */
	public function testNewFromNumberWithInvalidNumericId( $number ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		ItemId::newFromNumber( $number );
	}

	public function invalidNumericIdProvider() {
		return [
			[ 'Q1' ],
			[ '42.1' ],
			[ 42.1 ],
			[ 2147483648.1 ],
		];
	}

}
