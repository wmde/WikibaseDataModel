<?php

namespace Wikibase\Test;

use Wikibase\DataModel\ByPropertyIdArrayNew;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\PropertyIdProvider;
use Wikibase\DataModel\Snak\Snak;

/**
 * @covers Wikibase\DataModel\ByPropertyIdArrayNew
 *
 * @author Benedikt
 */
class ByPropertyIdArrayNewTest extends \PHPUnit_Framework_TestCase {

	public function testGetFlatArray() {
		$byPropertyIdArray = $this->getByPropertyIdArray();
		$expectedTypes = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h' );
		$this->assertEquals( $expectedTypes, $this->getTypes( $byPropertyIdArray->toFlatArray() ) );
	}

	public function testGetIndex() {
		$byPropertyIdArray = $this->getByPropertyIdArray();
		$propertyIdProvider = $this->getSnakMock( 'P42', 'foo bar' );
		$byPropertyIdArray->addObjectAtIndex( $propertyIdProvider, 2 );
		$this->assertEquals( 2, $byPropertyIdArray->getIndex( $propertyIdProvider ) );
	}

	public function provideAddObjectAtIndex() {
		$cases = array();

		$cases[] = array(
			$this->getSnakMock( 'P1', 'x' ),
			1,
			array( 'a', 'x', 'b', 'c', 'd', 'e', 'f', 'g', 'h' )
		);

		$cases[] = array(
			$this->getSnakMock( 'P1', 'x' ),
			5,
			array( 'c', 'd', 'e', 'a', 'b', 'x', 'f', 'g', 'h' )
		);

		$cases[] = array(
			$this->getSnakMock( 'P2', 'x' ),
			0,
			array( 'x', 'c', 'd', 'e', 'a', 'b', 'f', 'g', 'h' )
		);

		$cases[] = array(
			$this->getSnakMock( 'P2', 'x' ),
			8,
			array( 'a', 'b', 'f', 'g', 'h', 'c', 'd', 'e', 'x' )
		);

		$cases[] = array(
			$this->getSnakMock( 'P2', 'x' ),
			7,
			array( 'a', 'b', 'f', 'g', 'h', 'c', 'd', 'e', 'x' )
		);

		return $cases;
	}

	/**
	 * @dataProvider provideAddObjectAtIndex
	 */
	public function testAddObjectAtIndex( PropertyIdProvider $propertyIdProvider, $index, $expectedTypes ) {
		$byPropertyIdArray = $this->getByPropertyIdArray();
		$byPropertyIdArray->addObjectAtIndex( $propertyIdProvider, $index );
		$this->assertEquals( $expectedTypes, $this->getTypes( $byPropertyIdArray->toFlatArray() ) );
	}

	private function getByPropertyIdArray() {
		return new ByPropertyIdArrayNew( array(
			$this->getSnakMock( 'P1', 'a' ),
			$this->getSnakMock( 'P1', 'b' ),
			$this->getSnakMock( 'P2', 'c' ),
			$this->getSnakMock( 'P2', 'd' ),
			$this->getSnakMock( 'P2', 'e' ),
			$this->getSnakMock( 'P3', 'f' ),
			$this->getSnakMock( 'P4', 'g' ),
			$this->getSnakMock( 'P4', 'h' )
		) );
	}

	private function getTypes( array $snaks ) {
		return array_map( function( Snak $snak ) {
			return $snak->getType();
		}, $snaks );
	}

	/**
	 * @param string $propertyId
	 * @param string $type
	 * @return Snak
	 */
	private function getSnakMock( $propertyId, $type ) {
		$snak = $this->getMock( 'Wikibase\DataModel\Snak\Snak' );

		$snak->expects( $this->any() )
			->method( 'getPropertyId' )
			->will( $this->returnValue( new PropertyId( $propertyId ) ) );

		$snak->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( $type ) );

		return $snak;
	}

}
