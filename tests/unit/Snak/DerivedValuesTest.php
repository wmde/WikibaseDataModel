<?php

namespace Wikibase\DataModel\Tests\Snak;

use DataValues\DataValue;
use DataValues\StringValue;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Wikibase\DataModel\Snak\DerivedValues;

/**
 * @covers Wikibase\DataModel\Snak\DerivedValues
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseSnak
 *
 * @licence GNU GPL v2+
 * @author Adam Shorland
 */
class DerivedValuesTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider validConstructorArgumentsProvider
	 */
	public function testConstructor( array $derivedDataValues ) {
		$snak = new DerivedValues( $derivedDataValues );
	}

	public function validConstructorArgumentsProvider() {
		return array(
			'Empty' => array(
				array(),
			),
			'two values' => array(
				array( 'foo' => new StringValue( 'foo' ), 'bar' => new StringValue( 'bar' ) ),
			),
		);
	}

	/**
	 * @dataProvider invalidConstructorArgumentsProvider
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenInvalidConstructorArguments_constructorThrowsException(
		array $derivedDataValues
	) {
		new DerivedValues( $derivedDataValues );
	}

	public function invalidConstructorArgumentsProvider() {
		return array(
			'fail - Integer key' => array(
				array( new StringValue( 'foo' ) ),
			),
			'fail - not a value' => array(
				array( 'foo' => 'bar' ),
			),
		);
	}

	public function testGetDerivedDataValues() {
		$derivedValues = array( 'foo' => new StringValue( 'foo' ), 'bar' => new StringValue( 'bar' ) );

		$snak = new DerivedValues(
			$derivedValues
		);

		$this->assertEquals( $derivedValues, $snak->getDerivedDataValues() );
	}

	public function testGetDerivedDataValue() {
		$foo = new StringValue( 'foo' );
		$bar = new StringValue( 'bar' );
		$derivedValues = array( 'foo' => $foo, 'bar' => $bar );

		$snak = new DerivedValues(
			$derivedValues
		);

		$this->assertEquals( $foo, $snak->getDerivedDataValue( 'foo' ) );
		$this->assertEquals( $bar, $snak->getDerivedDataValue( 'bar' ) );
	}

}
