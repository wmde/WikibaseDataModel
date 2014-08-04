<?php

namespace Wikibase\Test\Snak;

/**
 * @covers Wikibase\DataModel\Snak\PropertySomeValueSnak
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseSnak
 *
 * @group Database
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class PropertySomeValueSnakTest extends SnakObjectTest {

	public function getClass() {
		return 'Wikibase\DataModel\Snak\PropertySomeValueSnak';
	}

}
