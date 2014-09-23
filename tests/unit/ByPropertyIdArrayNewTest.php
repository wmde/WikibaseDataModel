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

	public function provideAddAtIndex() {
		$cases = array();

		$cases[] = array(
			$this->getSnakMock( 'P1', 'x' ),
			1,
			array( 'a', 'x', 'b', 'c', 'd', 'e', 'f', 'g', 'h' )
		);

		$cases[] = array(
			$this->getSnakMock( 'P1', 'x' ),
			5,
			array( 'c', 'd', 'e', 'f', 'a', 'b', 'x', 'g', 'h' )
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
	 * @dataProvider provideAddAtIndex
	 */
	public function testAddAtIndex( PropertyIdProvider $propertyIdProvider, $index, $expectedTypes ) {
		$byPropertyIdArray = $this->getByPropertyIdArray();
		$byPropertyIdArray->addAtIndex( $propertyIdProvider, $index );
		$types = array_map( function( Snak $snak ) {
			return $snak->getType();
		}, $byPropertyIdArray->getFlatArray() );
		$this->assertEquals( $expectedTypes, $types );
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
