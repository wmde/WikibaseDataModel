<?php

namespace Wikibase\DataModel\Term\Test;

use Wikibase\DataModel\Term\OrderedTermSet;
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
			'en' => new OrderedTermSet( 'en', array( 'foo' ) ),
			'de' => new OrderedTermSet( 'de', array( 'bar', 'baz' ) ),
		);
	}

	public function testGivenTwoGroups_listContainsThem() {
		$array = $this->getTwoGroups();

		$list = new AliasGroupList( $array );

		$this->assertEquals( $array, iterator_to_array( $list ) );
	}

	public function testGivenGroupsWithTheSameLanguage_onlyTheLastOnesAreRetained() {
		$array = array(
			new OrderedTermSet( 'en', array( 'foo' ) ),
			new OrderedTermSet( 'en', array( 'bar' ) ),

			new OrderedTermSet( 'de', array( 'baz' ) ),

			new OrderedTermSet( 'nl', array( 'bah' ) ),
			new OrderedTermSet( 'nl', array( 'blah' ) ),
			new OrderedTermSet( 'nl', array( 'spam' ) ),
		);

		$list = new AliasGroupList( $array );

		$this->assertEquals(
			array(
				'en' => new OrderedTermSet( 'en', array( 'bar' ) ),
				'de' => new OrderedTermSet( 'de', array( 'baz' ) ),
				'nl' => new OrderedTermSet( 'nl', array( 'spam' ) ),
			),
			iterator_to_array( $list )
		);
	}

	public function testCanIterateOverList() {
		$group = new OrderedTermSet( 'en', array( 'foo' ) );

		$list = new AliasGroupList( array( $group ) );

		/**
		 * @var OrderedTermSet $aliasGroup
		 */
		foreach ( $list as $key => $aliasGroup ) {
			$this->assertEquals( $group, $aliasGroup );
			$this->assertEquals( $aliasGroup->getLanguageCode(), $key );
		}
	}

	public function testGivenNonAliasGroups_constructorThrowsException() {
		$this->setExpectedException( 'InvalidArgumentException' );
		new AliasGroupList( array( $this->getMock( 'Wikibase\DataModel\Term\Term' ) ) );
	}

	public function testGivenSetLanguageCode_getByLanguageReturnsGroup() {
		$enGroup = new OrderedTermSet( 'en', array( 'foo' ) );

		$list = new AliasGroupList( array(
			new OrderedTermSet( 'de', array() ),
			$enGroup,
			new OrderedTermSet( 'nl', array() ),
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
		$enGroup = new OrderedTermSet( 'en', array( 'foo', 'bar' ) );
		$deGroup = new OrderedTermSet( 'de', array( 'baz', 'bah' ) );

		$list = new AliasGroupList( array( $enGroup ) );
		$expectedList = new AliasGroupList( array( $enGroup, $deGroup ) );

		$list->setGroup( $deGroup );

		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenLabelForExistingLanguage_setLabelReplacesLabel() {
		$enGroup = new OrderedTermSet( 'en', array( 'foo', 'bar' ) );
		$newEnGroup = new OrderedTermSet( 'en', array( 'foo', 'bar', 'bah' ) );

		$list = new AliasGroupList( array( $enGroup ) );
		$expectedList = new AliasGroupList( array( $newEnGroup ) );

		$list->setGroup( $newEnGroup );
		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenNotSetLanguage_removeByLanguageIsNoOp() {
		$list = new AliasGroupList( array( new OrderedTermSet( 'en', array( 'foo', 'bar' ) ) ) );
		$originalList = clone $list;

		$list->removeByLanguage( 'de' );

		$this->assertEquals( $originalList, $list );
	}

	public function testGivenSetLanguage_removeByLanguageRemovesIt() {
		$list = new AliasGroupList( array( new OrderedTermSet( 'en', array( 'foo', 'bar' ) ) ) );

		$list->removeByLanguage( 'en' );

		$this->assertEquals( new AliasGroupList( array() ), $list );
	}

	public function testGivenEmptyGroups_constructorRemovesThem() {
		$enGroup = new OrderedTermSet( 'en', array( 'foo' ) );

		$list = new AliasGroupList( array(
			new OrderedTermSet( 'de', array() ),
			$enGroup,
			new OrderedTermSet( 'en', array() ),
			new OrderedTermSet( 'nl', array() ),
		) );

		$expectedList = new AliasGroupList( array(
			new OrderedTermSet( 'en', array() ),
		) );

		$this->assertEquals( $expectedList, $list );
	}

	public function testGivenEmptyGroup_setGroupRemovesGroup() {
		$list = new AliasGroupList( array(
			new OrderedTermSet( 'en', array( 'foo' ) ),
		) );

		$expectedList = new AliasGroupList( array() );

		$list->setGroup( new OrderedTermSet( 'en', array() ) );
		$list->setGroup( new OrderedTermSet( 'de', array() ) );

		$this->assertEquals( $expectedList, $list );
	}

}
