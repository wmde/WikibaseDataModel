<?php

namespace Wikibase\Test\Snak;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers Wikibase\DataModel\Snak\SnakObject
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseSnak
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class SnakObjectTest extends \PHPUnit_Framework_TestCase {

	public function testGetHash() {
		$snak = $this->getSnakObject( 42 );
		$hash = $snak->getHash();

		$this->assertInternalType( 'string', $hash );
		$this->assertEquals( 40, strlen( $hash ) );
	}

	public function testGetPropertyId() {
		$snak = $this->getSnakObject( 42 );
		$propertyId = $snak->getPropertyId();

		$this->assertEquals( new PropertyId( 'P42' ), $propertyId );
	}


	private function getSnakObject( /* ... */ ) {
		$snakObject = $this->getMockForAbstractClass( 'Wikibase\DataModel\Snak\SnakObject', func_get_args() );

		$snakObject->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( 'mock' ) );

		return $snakObject;
	}

}
