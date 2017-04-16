<?php

namespace Wikibase\Test;

use DataValues\StringValue;
use Wikibase\DataModel\ByPropertyIdMap;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @covers Wikibase\DataModel\ByPropertyIdMap
 *
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ByPropertyIdMapTest extends \PHPUnit_Framework_TestCase {

	private function getPropertyIdProvider( $propertyId, $value ) {
		return new PropertyValueSnak( new PropertyId( $propertyId ), new StringValue( $value ) );
	}

	private function assertTypesAreEqual( array $types, ByPropertyIdMap $byPropertyIdMap ) {
		$flatArray = $byPropertyIdMap->getFlatArray();

		array_walk( $flatArray, function( PropertyValueSnak &$snak ) {
			$snak = $snak->getDataValue()->getValue();
		} );

		$this->assertEquals( $types, $flatArray );
	}

	public function testConstructWithGroupedList_getFlatArrayReturnsSameList() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P789', 'boo' )
		) );

		$this->assertTypesAreEqual( array( 'foo', 'bar', 'baz', 'boo' ), $byPropertyIdMap );
	}

	public function testConstructWithUngroupedList_getFlatArrayReturnsGroupedList() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P321', 'boo' )
		) );

		$this->assertTypesAreEqual( array( 'foo', 'boo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenIntegerIndex_moveGroupToIndexMovesGroup() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P789', 'boo' )
		) );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P123' ), 2 );

		$this->assertTypesAreEqual( array( 'foo', 'boo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenZeroIndex_moveGroupToIndexMovesGroupToBeginning() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P789', 'boo' )
		) );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P789' ), 0 );

		$this->assertTypesAreEqual( array( 'boo', 'foo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenHugeIndex_moveGroupToIndexMovesGroupToEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P789', 'boo' )
		) );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P321' ), 999999 );

		$this->assertTypesAreEqual( array( 'bar', 'baz', 'boo', 'foo' ), $byPropertyIdMap );
	}

	public function testGivenNullIndex_moveGroupToIndexMovesGroupToEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P789', 'boo' )
		) );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P321' ), null );

		$this->assertTypesAreEqual( array( 'bar', 'baz', 'boo', 'foo' ), $byPropertyIdMap );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenNonIntegerIndex_moveGroupToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P42' ), false );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenNegativeIndex_moveGroupToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P42' ), -1 );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGivenNotExistingPropertyId_moveGroupToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->moveGroupToIndex( new PropertyId( 'P42' ), 0 );
	}

	public function testGivenIntegerIndex_moveElementToIndexMovesPropertyIdProvider() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P123', 'boo' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P123', 'bar' );
		$byPropertyIdMap->moveElementToIndex( $propertyIdProvider, 1 );

		$this->assertTypesAreEqual( array( 'foo', 'baz', 'bar', 'boo' ), $byPropertyIdMap );
	}

	public function testGivenZeroIndex_moveElementToIndexMovesPropertyIdProviderToBeginning() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P123', 'boo' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P123', 'boo' );
		$byPropertyIdMap->moveElementToIndex( $propertyIdProvider, 0 );

		$this->assertTypesAreEqual( array( 'foo', 'boo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenHugeIndex_moveElementToIndexMovesPropertyIdProviderToEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P123', 'boo' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P123', 'baz' );
		$byPropertyIdMap->moveElementToIndex( $propertyIdProvider, 999999 );

		$this->assertTypesAreEqual( array( 'foo', 'bar', 'boo', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenNullIndex_moveElementToIndexMovesPropertyIdProviderToEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' ),
			$this->getPropertyIdProvider( 'P123', 'boo' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P123', 'baz' );
		$byPropertyIdMap->moveElementToIndex( $propertyIdProvider, null );

		$this->assertTypesAreEqual( array( 'foo', 'bar', 'boo', 'baz' ), $byPropertyIdMap );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenNonIntegerIndex_moveElementToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->moveElementToIndex( $this->getPropertyIdProvider( 'P42', 'foo' ), false );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenNegativeIndex_moveElementToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->moveElementToIndex( $this->getPropertyIdProvider( 'P42', 'foo' ), -1 );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGivenNotExistingPropertyId_moveElementToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->moveElementToIndex( $this->getPropertyIdProvider( 'P42', 'foo' ), 0 );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGivenNotExistingPropertyIdProvider_moveElementToIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P42', 'foo' )
		) );

		$byPropertyIdMap->moveElementToIndex( $this->getPropertyIdProvider( 'P42', 'bar' ), 0 );
	}

	public function testGivenIntegerIndex_addElementAtIndexAddsPropertyIdProvider() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P123', 'boo' );
		$byPropertyIdMap->addElementAtIndex( $propertyIdProvider, 1 );

		$this->assertTypesAreEqual( array( 'foo', 'bar', 'boo', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenZeroIndex_addElementAtIndexAddsPropertyIdProviderAtBeginning() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P321', 'boo' );
		$byPropertyIdMap->addElementAtIndex( $propertyIdProvider, 0 );

		$this->assertTypesAreEqual( array( 'boo', 'foo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenHugeIndex_addElementAtIndexAddsPropertyIdProviderAtEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P321', 'boo' );
		$byPropertyIdMap->addElementAtIndex( $propertyIdProvider, 999999 );

		$this->assertTypesAreEqual( array( 'foo', 'boo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenNullIndex_addElementAtIndexAddsPropertyIdProviderAtEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P321', 'boo' );
		$byPropertyIdMap->addElementAtIndex( $propertyIdProvider, null );

		$this->assertTypesAreEqual( array( 'foo', 'boo', 'bar', 'baz' ), $byPropertyIdMap );
	}

	public function testGivenNotExistingPropertyId_addElementAtIndexCreatesNewGroupAtEnd() {
		$byPropertyIdMap = new ByPropertyIdMap( array(
			$this->getPropertyIdProvider( 'P321', 'foo' ),
			$this->getPropertyIdProvider( 'P123', 'bar' ),
			$this->getPropertyIdProvider( 'P123', 'baz' )
		) );

		$propertyIdProvider = $this->getPropertyIdProvider( 'P456', 'boo' );
		$byPropertyIdMap->addElementAtIndex( $propertyIdProvider, 1 );

		$this->assertTypesAreEqual( array( 'foo', 'bar', 'baz', 'boo' ), $byPropertyIdMap );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenNonIntegerIndex_addElementAtIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->addElementAtIndex( $this->getPropertyIdProvider( 'P42', 'foo' ), false );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenNegativeIndex_addElementAtIndexThrowsException() {
		$byPropertyIdMap = new ByPropertyIdMap( array() );

		$byPropertyIdMap->addElementAtIndex( $this->getPropertyIdProvider( 'P42', 'foo' ), -1 );
	}

}
