<?php

namespace Wikibase\DataModel\Tests\Entity;

use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Entity\DispatchingEntityIdParser;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @covers Wikibase\DataModel\Entity\DispatchingEntityIdParser
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
class DispatchingEntityIdParserTest extends \PHPUnit_Framework_TestCase {

	private function getBasicParser() {
		return new DispatchingEntityIdParser( BasicEntityIdParser::getBuilders() );
	}

	/**
	 * @dataProvider entityIdProvider
	 */
	public function testCanParseEntityId( $idString, EntityId $expected ) {
		$parser = $this->getBasicParser();
		$actual = $parser->parse( $idString );

		$this->assertEquals( $actual, $expected );
	}

	public function entityIdProvider() {
		return [
			[ 'q42', new ItemId( 'q42' ) ],
			[ 'Q1337', new ItemId( 'Q1337' ) ],
			[ 'p1', new PropertyId( 'p1' ) ],
			[ 'P100000', new PropertyId( 'P100000' ) ],
		];
	}

	/**
	 * @dataProvider invalidIdSerializationProvider
	 */
	public function testCannotParseInvalidId( $invalidIdSerialization ) {
		$parser = $this->getBasicParser();

		$this->setExpectedException( 'Wikibase\DataModel\Entity\EntityIdParsingException' );
		$parser->parse( $invalidIdSerialization );
	}

	public function invalidIdSerializationProvider() {
		return [
			[ 'FOO' ],
			[ null ],
			[ 42 ],
			[ [] ],
			[ '' ],
			[ 'q0' ],
			[ '1p' ],
		];
	}

	public function testCannotParseWithoutBuilders() {
		$parser = new DispatchingEntityIdParser( [] );

		$this->setExpectedException( 'Wikibase\DataModel\Entity\EntityIdParsingException' );
		$parser->parse( 'Q1' );
	}

}
