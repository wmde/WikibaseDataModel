<?php

namespace Wikibase\DataModel\Tests\Entity;

use PHPUnit_Framework_TestCase;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers Wikibase\DataModel\Entity\PropertyId
 * @covers Wikibase\DataModel\Entity\EntityId
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group EntityIdTest
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PropertyIdTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider idSerializationProvider
	 */
	public function testCanConstructId( $idSerialization ) {
		$id = new PropertyId( $idSerialization );

		$this->assertEquals(
			strtoupper( $idSerialization ),
			$id->getSerialization()
		);
	}

	public function idSerializationProvider() {
		return [
			[ 'p1' ],
			[ 'p100' ],
			[ 'p1337' ],
			[ 'p31337' ],
			[ 'P31337' ],
			[ 'P42' ],
			[ 'P2147483648' ],
		];
	}

	/**
	 * @dataProvider invalidIdSerializationProvider
	 */
	public function testCannotConstructWithInvalidSerialization( $invalidSerialization ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new PropertyId( $invalidSerialization );
	}

	public function invalidIdSerializationProvider() {
		return [
			[ 'p' ],
			[ 'q1' ],
			[ 'pp1' ],
			[ '1p' ],
			[ 'p01' ],
			[ 'p 1' ],
			[ ' p1' ],
			[ 'p1 ' ],
			[ '1' ],
			[ ' ' ],
			[ '' ],
			[ '0' ],
			[ 0 ],
			[ 1 ],
		];
	}

	public function testGetNumericId() {
		$id = new PropertyId( 'P1' );
		$this->assertSame( 1, $id->getNumericId() );
	}

	public function testGetEntityType() {
		$id = new PropertyId( 'P1' );
		$this->assertSame( 'property', $id->getEntityType() );
	}

	public function testSerialize() {
		$id = new PropertyId( 'P1' );
		$this->assertSame( '["property","P1"]', $id->serialize() );
	}

	/**
	 * @dataProvider serializationProvider
	 */
	public function testUnserialize( $json, $expected ) {
		$id = new PropertyId( 'P1' );
		$id->unserialize( $json );
		$this->assertSame( $expected, $id->getSerialization() );
	}

	public function serializationProvider() {
		return [
			[ '["property","P2"]', 'P2' ],

			// All these cases are kind of an injection vector and allow constructing invalid ids.
			[ '["string","P2"]', 'P2' ],
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
		$id = PropertyId::newFromNumber( $number );
		$this->assertEquals( 'P' . $number, $id->getSerialization() );
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
		PropertyId::newFromNumber( $number );
	}

	public function invalidNumericIdProvider() {
		return [
			[ 'P1' ],
			[ '42.1' ],
			[ 42.1 ],
			[ 2147483648.1 ],
		];
	}

}
