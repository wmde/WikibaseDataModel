<?php

namespace Wikibase\DataModel\Term\Test;

use Wikibase\DataModel\Term\OrderedLanguageTextsSet;
use Wikibase\DataModel\Term\LanguageTextsList;

/**
 * @covers Wikibase\DataModel\Term\AliasGroupList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AliasGroupListTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNoTerms_sizeIsZero() {
		$list = new LanguageTextsList( array() );
		$this->assertCount( 0, $list );
	}

	public function testGivenTwoTerms_countReturnsTwo() {
		$list = new LanguageTextsList( $this->getTwoGroups() );

		$this->assertCount( 2, $list );
	}

	private function getTwoGroups() {
		return array(
			'en' => new OrderedLanguageTextsSet( 'en', array( 'foo' ) ),
			'de' => new OrderedLanguageTextsSet( 'de', array( 'bar', 'baz' ) ),
		);
	}

	public function testGivenTwoGroups_listContainsThem() {
		$array = $this->getTwoGroups();

		$list = new LanguageTextsList( $array );

		$this->assertEquals( $array, iterator_to_array( $list ) );
	}

	public function testGivenGroupsWithTheSameLanguage_onlyTheLastOnesAreRetained() {
		$array = array(
			new OrderedLanguageTextsSet( 'en', array( 'foo' ) ),
			new OrderedLanguageTextsSet( 'en', array( 'bar' ) ),

			new OrderedLanguageTextsSet( 'de', array( 'baz' ) ),

			new OrderedLanguageTextsSet( 'nl', array( 'bah' ) ),
			new OrderedLanguageTextsSet( 'nl', array( 'blah' ) ),
			new OrderedLanguageTextsSet( 'nl', array( 'spam' ) ),
		);

		$list = new LanguageTextsList( $array );

		$this->assertEquals(
			array(
				'en' => new OrderedLanguageTextsSet( 'en', array( 'bar' ) ),
				'de' => new OrderedLanguageTextsSet( 'de', array( 'baz' ) ),
				'nl' => new OrderedLanguageTextsSet( 'nl', array( 'spam' ) ),
			),
			iterator_to_array( $list )
		);
	}

	public function testCanIterateOverList() {
		$group = new OrderedLanguageTextsSet( 'en', array( 'foo' ) );

		$list = new LanguageTextsList( array( $group ) );

		/**
		 * @var OrderedLanguageTextsSet $aliasGroup
		 */
		foreach ( $list as $key => $aliasGroup ) {
			$this->assertEquals( $group, $aliasGroup );
			$this->assertEquals( $aliasGroup->getLanguageCode(), $key );
		}
	}

	public function testGivenNonAliasGroups_constructorThrowsException() {
		$this->setExpectedException( 'InvalidArgumentException' );
		new LanguageTextsList( array( $this->getMock( 'Wikibase\DataModel\Term\Term' ) ) );
	}

	public function testGivenSetLanguageCode_getByLanguageReturnsGroup() {
		$enGroup = new OrderedLanguageTextsSet( 'en', array( 'foo' ) );

		$list = new LanguageTextsList( array(
			new OrderedLanguageTextsSet( 'de', array() ),
			$enGroup,
			new OrderedLanguageTextsSet( 'nl', array() ),
		) );

		$this->assertEquals( $enGroup, $list->getByLanguage( 'en' ) );
	}

	public function testGivenNonString_getByLanguageThrowsException() {
		$list = new LanguageTextsList( array() );

		$this->setExpectedException( 'InvalidArgumentException' );
		$list->getByLanguage( null );
	}

	public function testGivenNonSetLanguageCode_getByLanguageThrowsException() {
		$list = new LanguageTextsList( array() );

		$this->setExpectedException( 'OutOfBoundsException' );
		$list->getByLanguage( 'en' );
	}

	public function testGivenGroupForNewLanguage_setGroupAddsGroup() {
		$enGroup = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) );
		$deGroup = new OrderedLanguageTextsSet( 'de', array( 'baz', 'bah' ) );

		$list = new LanguageTextsList( array( $enGroup ) );
		$expectedList = new LanguageTextsList( array( $enGroup, $deGroup ) );

		$list->setTexts( $deGroup );

		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenLabelForExistingLanguage_setLabelReplacesLabel() {
		$enGroup = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) );
		$newEnGroup = new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar', 'bah' ) );

		$list = new LanguageTextsList( array( $enGroup ) );
		$expectedList = new LanguageTextsList( array( $newEnGroup ) );

		$list->setTexts( $newEnGroup );
		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenNotSetLanguage_removeByLanguageIsNoOp() {
		$list = new LanguageTextsList( array( new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) ) ) );
		$originalList = clone $list;

		$list->removeByLanguage( 'de' );

		$this->assertEquals( $originalList, $list );
	}

	public function testGivenSetLanguage_removeByLanguageRemovesIt() {
		$list = new LanguageTextsList( array( new OrderedLanguageTextsSet( 'en', array( 'foo', 'bar' ) ) ) );

		$list->removeByLanguage( 'en' );

		$this->assertEquals( new LanguageTextsList( array() ), $list );
	}

	public function testGivenEmptyGroups_constructorRemovesThem() {
		$enGroup = new OrderedLanguageTextsSet( 'en', array( 'foo' ) );

		$list = new LanguageTextsList( array(
			new OrderedLanguageTextsSet( 'de', array() ),
			$enGroup,
			new OrderedLanguageTextsSet( 'en', array() ),
			new OrderedLanguageTextsSet( 'nl', array() ),
		) );

		$expectedList = new LanguageTextsList( array(
			new OrderedLanguageTextsSet( 'en', array() ),
		) );

		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenEmptyGroup_setGroupRemovesGroup() {
		$list = new LanguageTextsList( array(
			new OrderedLanguageTextsSet( 'en', array( 'foo' ) ),
		) );

		$expectedList = new LanguageTextsList( array() );

		$list->setTexts( new OrderedLanguageTextsSet( 'en', array() ) );
		$list->setTexts( new OrderedLanguageTextsSet( 'de', array() ) );

		$this->assertEquals( $expectedList, $list );
	}

}
