<?php

namespace Wikibase\DataModel\Term\Test;

use Wikibase\DataModel\Term\OrderedTermSet;

/**
 * @covers Wikibase\DataModel\Term\OrderedTermSet
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrderedTermSetTest extends \PHPUnit_Framework_TestCase {

	public function testConstructorSetsValues() {
		$language = 'en';
		$aliases = array( 'foo', 'bar', 'baz' );

		$group = new OrderedTermSet( $language, $aliases );

		$this->assertEquals( $language, $group->getLanguageCode() );
		$this->assertEquals( $aliases, $group->getTermTexts() );
	}

	public function testIsEmpty() {
		$emptyGroup = new OrderedTermSet( 'en', array() );
		$this->assertTrue( $emptyGroup->isEmpty() );

		$filledGroup = new OrderedTermSet( 'en', array( 'foo' ) );
		$this->assertFalse( $filledGroup->isEmpty() );
	}

	public function testEquality() {
		$group = new OrderedTermSet( 'en', array( 'foo', 'bar' ) );

		$this->assertTrue( $group->equals( $group ) );
		$this->assertTrue( $group->equals( clone $group ) );

		$this->assertFalse( $group->equals( new OrderedTermSet( 'en', array( 'foo' ) ) ) );
		$this->assertFalse( $group->equals( new OrderedTermSet( 'de', array( 'foo' ) ) ) );
		$this->assertFalse( $group->equals( new OrderedTermSet( 'de', array() ) ) );
	}

	public function testDuplicatesAreRemoved() {
		$group = new OrderedTermSet( 'en', array( 'foo', 'bar', 'spam', 'spam', 'spam', 'foo' ) );

		$expectedGroup = new OrderedTermSet( 'en', array( 'foo', 'bar', 'spam' ) );

		$this->assertEquals( $expectedGroup, $group );
	}

	public function testIsCountable() {
		$this->assertCount( 0, new OrderedTermSet( 'en', array() ) );
		$this->assertCount( 1, new OrderedTermSet( 'en', array( 'foo' ) ) );
		$this->assertCount( 2, new OrderedTermSet( 'en', array( 'foo', 'bar' ) ) );
	}

	public function testGivenEmptyStringAlias_aliasIsRemoved() {
		$group = new OrderedTermSet( 'en', array( 'foo', '', 'bar', '  ' ) );

		$expectedGroup = new OrderedTermSet( 'en', array( 'foo', 'bar' ) );

		$this->assertEquals( $expectedGroup, $group );
	}

	public function testAliasesAreTrimmed() {
		$group = new OrderedTermSet( 'en', array( ' foo', 'bar ', '   baz   ' ) );

		$expectedGroup = new OrderedTermSet( 'en', array( 'foo', 'bar', 'baz' ) );

		$this->assertEquals( $expectedGroup, $group );
	}

}
