<?php

namespace Wikibase\DataModel\Tests\Term;

use OutOfBoundsException;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\EntityTerms;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers Wikibase\DataModel\Term\EntityTerms
 * @uses Wikibase\DataModel\Term\AliasGroup
 * @uses Wikibase\DataModel\Term\AliasGroupList
 * @uses Wikibase\DataModel\Term\EntityTerms
 * @uses Wikibase\DataModel\Term\Term
 * @uses Wikibase\DataModel\Term\TermList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
class EntityTermsTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TermList
	 */
	private $labels;

	/**
	 * @var TermList
	 */
	private $descriptions;

	/**
	 * @var AliasGroupList
	 */
	private $aliasGroups;

	/**
	 * @var EntityTerms
	 */
	private $entityTerms;

	protected function setUp() {
		$this->labels = $this->getMockBuilder( 'Wikibase\DataModel\Term\TermList' )
			->disableOriginalConstructor()->getMock();

		$this->descriptions = $this->getMockBuilder( 'Wikibase\DataModel\Term\TermList' )
			->disableOriginalConstructor()->getMock();

		$this->aliasGroups = $this->getMockBuilder( 'Wikibase\DataModel\Term\AliasGroupList' )
			->disableOriginalConstructor()->getMock();

		$this->entityTerms = new EntityTerms(
			new TermList( array(
				new Term( 'en', 'enlabel' ),
				new Term( 'de', 'delabel' ),
			) ),
			new TermList( array(
				new Term( 'en', 'endescription' ),
				new Term( 'de', 'dedescription' ),
			) ),
			new AliasGroupList( array(
				new AliasGroup( 'en', array( 'enalias' ) ),
				new AliasGroup( 'de', array( 'dealias' ) ),
			) )
		);
	}

	public function testEmptyConstructor() {
		$entityTerms = new EntityTerms();

		$this->assertTrue( $entityTerms->getLabels()->isEmpty() );
		$this->assertTrue( $entityTerms->getDescriptions()->isEmpty() );
		$this->assertTrue( $entityTerms->getAliasGroups()->isEmpty() );
	}

	public function testConstructorSetsValues() {
		$entityTerms = new EntityTerms( $this->labels, $this->descriptions, $this->aliasGroups );

		$this->assertEquals( $this->labels, $entityTerms->getLabels() );
		$this->assertEquals( $this->descriptions, $entityTerms->getDescriptions() );
		$this->assertEquals( $this->aliasGroups, $entityTerms->getAliasGroups() );
	}

	public function testGetLabel() {
		$term = new Term( 'en', 'enlabel' );
		$this->assertEquals( $term, $this->entityTerms->getLabel( 'en' ) );
	}

	public function testSetLabel() {
		$term = new Term( 'en', 'changed' );
		$this->entityTerms->setLabel( 'en', 'changed' );
		$this->assertEquals( $term, $this->entityTerms->getLabel( 'en' ) );
	}

	public function testRemoveLabel() {
		$labels = new TermList( array(
			new Term( 'de', 'delabel' ),
		) );
		$this->entityTerms->removeLabel( 'en' );
		$this->assertEquals( $labels, $this->entityTerms->getLabels() );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testRemoveLabelMakesGetterThrowException() {
		$this->entityTerms->removeLabel( 'en' );
		$this->entityTerms->getLabel( 'en' );
	}

	public function testGetDescription() {
		$term = new Term( 'en', 'endescription' );
		$this->assertEquals( $term, $this->entityTerms->getDescription( 'en' ) );
	}

	public function testSetDescription() {
		$description = new Term( 'en', 'changed' );
		$this->entityTerms->setDescription( 'en', 'changed' );
		$this->assertEquals( $description, $this->entityTerms->getDescription( 'en' ) );
	}

	public function testRemoveDescription() {
		$descriptions = new TermList( array(
			new Term( 'de', 'dedescription' ),
		) );
		$this->entityTerms->removeDescription( 'en' );
		$this->assertEquals( $descriptions, $this->entityTerms->getDescriptions() );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testRemoveDescriptionMakesGetterThrowException() {
		$this->entityTerms->removeDescription( 'en' );
		$this->entityTerms->getDescription( 'en' );
	}

	public function testGetAliasGroup() {
		$aliasGroup = new AliasGroup( 'en', array( 'enalias' ) );
		$this->assertEquals( $aliasGroup, $this->entityTerms->getAliasGroup( 'en' ) );
	}

	public function testSetAliasGroup() {
		$aliasGroup = new AliasGroup( 'en', array( 'changed' ) );
		$this->entityTerms->setAliasGroup( 'en', array( 'changed' ) );
		$this->assertEquals( $aliasGroup, $this->entityTerms->getAliasGroup( 'en' ) );
	}

	public function testRemoveAliasGroup() {
		$aliasGroups = new AliasGroupList( array(
			new AliasGroup( 'de', array( 'dealias' ) ),
		) );
		$this->entityTerms->removeAliasGroup( 'en' );
		$this->assertEquals( $aliasGroups, $this->entityTerms->getAliasGroups() );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testRemoveAliasGroupMakesGetterThrowException() {
		$this->entityTerms->removeAliasGroup( 'en' );
		$this->entityTerms->getAliasGroup( 'en' );
	}

	/**
	 * @dataProvider entityTermsProvider
	 */
	public function testEntityTermsEqualThemselves( EntityTerms $entityTerms ) {
		$this->assertTrue( $entityTerms->equals( $entityTerms ) );
		$this->assertTrue( $entityTerms->equals( clone $entityTerms ) );
	}

	public function entityTermsProvider() {
		return array(
			array(
				new EntityTerms()
			),
			array(
				new EntityTerms(
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),
			array(
				new EntityTerms(
					new TermList(),
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),
			array(
				new EntityTerms(
					new TermList(),
					new TermList(),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				)
			),
			array(
				new EntityTerms(
					new TermList( array( new Term( 'nl', 'bar' ), new Term( 'fr', 'le' ) ) ),
					new TermList( array( new Term( 'de', 'baz' ) ) ),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				)
			),
		);
	}

	/**
	 * @dataProvider differentEntityTermsProvider
	 */
	public function testDifferentEntityTermsDoNotEqual( EntityTerms $one, EntityTerms $two ) {
		$this->assertFalse( $one->equals( $two ) );
	}

	public function differentEntityTermsProvider() {
		return array(
			array(
				new EntityTerms(),
				new EntityTerms(
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),
			array(
				new EntityTerms(
					new TermList( array( new Term( 'en', 'foo' ), new Term( 'de', 'bar' ) ) )
				),
				new EntityTerms(
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),
			array(
				new EntityTerms(),
				new EntityTerms(
					new TermList(),
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),
			array(
				new EntityTerms(),
				new EntityTerms(
					new TermList(),
					new TermList(),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				)
			),
			array(
				new EntityTerms(
					new TermList( array( new Term( 'nl', 'bar' ), new Term( 'fr', 'le' ) ) ),
					new TermList( array( new Term( 'de', 'HAX' ) ) ),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				),
				new EntityTerms(
					new TermList( array( new Term( 'nl', 'bar' ), new Term( 'fr', 'le' ) ) ),
					new TermList( array( new Term( 'de', 'baz' ) ) ),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				)
			),
		);
	}

	public function testEmptyEntityTermsIsEmpty() {
		$entityTerms = new EntityTerms();
		$this->assertTrue( $entityTerms->isEmpty() );
	}

	/**
	 * @dataProvider nonEmptyEntityTermsProvider
	 */
	public function testNonEmptyEntityTermsIsNotEmpty( EntityTerms $nonEmptyEntityTerms ) {
		$this->assertFalse( $nonEmptyEntityTerms->isEmpty() );
	}

	public function nonEmptyEntityTermsProvider() {
		return array(
			array(
				new EntityTerms(
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),

			array(
				new EntityTerms(
					new TermList(),
					new TermList( array( new Term( 'en', 'foo' ) ) )
				)
			),

			array(
				new EntityTerms(
					new TermList(),
					new TermList(),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				)
			),

			array(
				new EntityTerms(
					new TermList( array( new Term( 'nl', 'bar' ), new Term( 'fr', 'le' ) ) ),
					new TermList( array( new Term( 'de', 'baz' ) ) ),
					new AliasGroupList( array( new AliasGroup( 'en', array( 'foo' ) ) ) )
				)
			),
		);
	}

	public function testSetLabels() {
		$entityTerms = new EntityTerms();
		$entityTerms->setLabel( 'en', 'foo' );

		$labels = new TermList( array(
			new Term( 'de', 'bar' )
		) );

		$entityTerms->setLabels( $labels );

		$this->assertEquals( $labels, $entityTerms->getLabels() );
	}

	public function testSetDescriptions() {
		$entityTerms = new EntityTerms();
		$entityTerms->setDescription( 'en', 'foo' );

		$descriptions = new TermList( array(
			new Term( 'de', 'bar' )
		) );

		$entityTerms->setDescriptions( $descriptions );

		$this->assertEquals( $descriptions, $entityTerms->getDescriptions() );
	}

	public function testSetAliasGroups() {
		$entityTerms = new EntityTerms();
		$entityTerms->setAliasGroup( 'en', array( 'foo' ) );

		$groups = new AliasGroupList( array(
			new AliasGroup( 'de', array( 'bar' ) )
		) );

		$entityTerms->setAliasGroups( $groups );

		$this->assertEquals( $groups, $entityTerms->getAliasGroups() );
	}

	public function testEmptyEntityTermsDoesNotHaveLabel() {
		$entityTerms = new EntityTerms();
		$this->assertFalse( $entityTerms->hasLabel( 'en' ) );
	}

	public function testEmptyEntityTermsDoesNotHaveDescription() {
		$entityTerms = new EntityTerms();
		$this->assertFalse( $entityTerms->hasDescription( 'en' ) );
	}

	public function testEmptyEntityTermsDoesNotHaveAliasGroup() {
		$entityTerms = new EntityTerms();
		$this->assertFalse( $entityTerms->hasAliasGroup( 'en' ) );
	}

	public function testHasLabelReturnsTrueOnlyWhenLabelExists() {
		$entityTerms = new EntityTerms();
		$entityTerms->setLabel( 'en', 'foo' );

		$this->assertTrue( $entityTerms->hasLabel( 'en' ) );
		$this->assertFalse( $entityTerms->hasLabel( 'de' ) );
	}

	public function testHasDescriptionReturnsTrueOnlyWhenDescriptionExists() {
		$entityTerms = new EntityTerms();
		$entityTerms->setDescription( 'en', 'foo' );

		$this->assertTrue( $entityTerms->hasDescription( 'en' ) );
		$this->assertFalse( $entityTerms->hasDescription( 'de' ) );
	}

	public function testHasAliasGroupReturnsTrueOnlyWhenAliasGroupExists() {
		$entityTerms = new EntityTerms();
		$entityTerms->setAliasGroup( 'en', array( 'foo' ) );

		$this->assertTrue( $entityTerms->hasAliasGroup( 'en' ) );
		$this->assertFalse( $entityTerms->hasAliasGroup( 'de' ) );
	}

}
