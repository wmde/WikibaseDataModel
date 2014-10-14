<?php

namespace Wikibase\Test\Snak;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @covers Wikibase\DataModel\Snak\PropertyValueSnak
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseSnak
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class PropertyValueSnakTest extends \PHPUnit_Framework_TestCase {

	public function testGetDataValue() {
		$snak = new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( '~=[,,_,,]:3' ) );
		$dataValue = $snak->getDataValue();
		$this->assertEquals( new StringValue( '~=[,,_,,]:3' ), $dataValue );
	}

}
