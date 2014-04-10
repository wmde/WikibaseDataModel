<?php

namespace Wikibase\DataModel\Term\Test;

use Wikibase\DataModel\Term\OrderedLanguageTextsSet;

/**
 * @covers Wikibase\DataModel\Term\OrderedLanguageTextsSet
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class OrderedLanguageTextsSetTest extends \PHPUnit_Framework_TestCase {

	public function testConstructorSetsValues() {
		$language = 'en';
		$aliases = array( 'foo', 'bar', 'baz' );

		$group = new OrderedLanguageTextsSet( $language, $aliases );

		$this->assertEquals( $language, $group->getLanguageCode() );
		$this->assertEquals( $aliases, $group->getTexts() );
	}

	public function testIsEmpty() {
		$emptyGroup = new OrderedLanguageTextsSet( 'en', array() );
		$this->assertTrue( $emptyGroup->isEmpty() );

		$filledGroup = new OrderedLanguageTextsSet( 'en', array( 'foo' ) );
		$this->assertFalse( $filledGroup->isEmpty() );
	}

	public function testEquality() {
		$group = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) );

		$this->assertTrue( $group->equals( $group ) );
		$this->assertTrue( $group->equals( clone $group ) );

		$this->assertFalse( $group->equals( new OrderedLanguageTextsSet( 'en', array( 'foo' ) ) ) );
		$this->assertFalse( $group->equals( new OrderedLanguageTextsSet( 'de', array( 'foo' ) ) ) );
		$this->assertFalse( $group->equals( new OrderedLanguageTextsSet( 'de', array() ) ) );
	}

	public function testDuplicatesAreRemoved() {
		$group = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar', 'spam', 'spam', 'spam', 'foo' ) );

		$expectedGroup = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar', 'spam' ) );

		$this->assertEquals( $expectedGroup, $group );
	}

	public function testIsCountable() {
		$this->assertCount( 0, new OrderedLanguageTextsSet( 'en', array() ) );
		$this->assertCount( 1, new OrderedLanguageTextsSet( 'en', array( 'foo' ) ) );
		$this->assertCount( 2, new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) ) );
	}

	public function testGivenEmptyStringAlias_aliasIsRemoved() {
		$group = new OrderedLanguageTextsSet( 'en', array( 'foo', '', 'bar', '  ' ) );

		$expectedGroup = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) );

		$this->assertEquals( $expectedGroup, $group );
	}

	public function testAliasesAreTrimmed() {
		$group = new OrderedLanguageTextsSet( 'en', array( ' foo', 'bar ', '   baz   ' ) );

		$expectedGroup = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar', 'baz' ) );

		$this->assertEquals( $expectedGroup, $group );
	}

}
