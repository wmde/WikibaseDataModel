<?php

namespace Wikibase\DataModel\Term\Test;

use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;

/**
 * @covers Wikibase\DataModel\Term\AliasGroupList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AliasGroupListTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNoTerms_sizeIsZero() {
		$list = new AliasGroupList( array() );
		$this->assertCount( 0, $list );
	}

	public function testGivenTwoTerms_countReturnsTwo() {
		$list = new AliasGroupList( $this->getTwoGroups() );

		$this->assertCount( 2, $list );
	}

	private function getTwoGroups() {
		return array(
			'en' => new AliasGroup( 'en', array( 'foo' ) ),
			'de' => new AliasGroup( 'de', array( 'bar', 'baz' ) ),
		);
	}

	public function testGivenTwoGroups_listContainsThem() {
		$array = $this->getTwoGroups();

		$list = new AliasGroupList( $array );

		$this->assertEquals( $array, iterator_to_array( $list ) );
	}

	public function testGivenGroupsWithTheSameLanguage_onlyTheLastOnesAreRetained() {
		$array = array(
			new AliasGroup( 'en', array( 'foo' ) ),
			new AliasGroup( 'en', array( 'bar' ) ),

			new AliasGroup( 'de', array( 'baz' ) ),

			new AliasGroup( 'nl', array( 'bah' ) ),
			new AliasGroup( 'nl', array( 'blah' ) ),
			new AliasGroup( 'nl', array( 'spam' ) ),
		);

		$list = new AliasGroupList( $array );

		$this->assertEquals(
			array(
				'en' => new AliasGroup( 'en', array( 'bar' ) ),
				'de' => new AliasGroup( 'de', array( 'baz' ) ),
				'nl' => new AliasGroup( 'nl', array( 'spam' ) ),
			),
			iterator_to_array( $list )
		);
	}

	public function testCanIterateOverList() {
		$group = new AliasGroup( 'en', array( 'foo' ) );

		$list = new AliasGroupList( array( $group ) );

		/**
		 * @var AliasGroup $aliasGroup
		 */
		foreach ( $list as $key => $aliasGroup ) {
			$this->assertEquals( $group, $aliasGroup );
			$this->assertEquals( $aliasGroup->getLanguageCode(), $key );
		}
	}

	public function testGivenNonAliasGroups_constructorThrowsException() {
		$this->setExpectedException( 'InvalidArgumentException' );
		new AliasGroupList( array( null ) );
	}

	public function testGivenSetLanguageCode_getByLanguageReturnsGroup() {
		$enGroup = new AliasGroup( 'en', array( 'foo' ) );

		$list = new AliasGroupList( array(
			new AliasGroup( 'de', array() ),
			$enGroup,
			new AliasGroup( 'nl', array() ),
		) );

		$this->assertEquals( $enGroup, $list->getByLanguage( 'en' ) );
	}

	public function testGivenNonString_getByLanguageThrowsException() {
		$list = new AliasGroupList( array() );

		$this->setExpectedException( 'InvalidArgumentException' );
		$list->getByLanguage( null );
	}

	public function testGivenNonSetLanguageCode_getByLanguageThrowsException() {
		$list = new AliasGroupList( array() );

		$this->setExpectedException( 'OutOfBoundsException' );
		$list->getByLanguage( 'en' );
	}

	public function testGivenGroupForNewLanguage_setGroupAddsGroup() {
		$enGroup = new AliasGroup( 'en', array( 'foo', 'bar' ) );
		$deGroup = new AliasGroup( 'de', array( 'baz', 'bah' ) );

		$list = new AliasGroupList( array( $enGroup ) );
		$expectedList = new AliasGroupList( array( $enGroup, $deGroup ) );

		$list->setGroup( $deGroup );

		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenLabelForExistingLanguage_setLabelReplacesLabel() {
		$enGroup = new AliasGroup( 'en', array( 'foo', 'bar' ) );
		$newEnGroup = new AliasGroup( 'en', array( 'foo', 'bar', 'bah' ) );

		$list = new AliasGroupList( array( $enGroup ) );
		$expectedList = new AliasGroupList( array( $newEnGroup ) );

		$list->setGroup( $newEnGroup );
		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenNotSetLanguage_removeByLanguageIsNoOp() {
		$list = new AliasGroupList( array( new AliasGroup( 'en', array( 'foo', 'bar' ) ) ) );
		$originalList = clone $list;

		$list->removeByLanguage( 'de' );

		$this->assertEquals( $originalList, $list );
	}

	public function testGivenSetLanguage_removeByLanguageRemovesIt() {
		$list = new AliasGroupList( array( new AliasGroup( 'en', array( 'foo', 'bar' ) ) ) );

		$list->removeByLanguage( 'en' );

		$this->assertEquals( new AliasGroupList( array() ), $list );
	}

	public function testGivenEmptyGroups_constructorRemovesThem() {
		$enGroup = new AliasGroup( 'en', array( 'foo' ) );

		$list = new AliasGroupList( array(
			new AliasGroup( 'de', array() ),
			$enGroup,
			new AliasGroup( 'en', array() ),
			new AliasGroup( 'nl', array() ),
		) );

		$expectedList = new AliasGroupList( array(
			new AliasGroup( 'en', array() ),
		) );

		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenEmptyGroup_setGroupRemovesGroup() {
		$list = new AliasGroupList( array(
			new AliasGroup( 'en', array( 'foo' ) ),
		) );

		$expectedList = new AliasGroupList( array() );

		$list->setGroup( new AliasGroup( 'en', array() ) );
		$list->setGroup( new AliasGroup( 'de', array() ) );

		$this->assertEquals( $expectedList, $list );
	}

}
