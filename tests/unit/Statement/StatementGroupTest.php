<?php


namespace Wikibase\DataModel\Tests\Statement;

use DataValues\StringValue;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementGroup;

/**
 * @covers Wikibase\DataModel\Statement\StatementGroup
 *
 * @licence GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class StatementGroupTest extends \PHPUnit_Framework_TestCase {

	public function testConstructor_numericId() {
		$statementGroup = new StatementGroup( 42 );
		$this->assertEquals( new PropertyId( 'P42' ), $statementGroup->getPropertyId() );
	}

	public function testConstructor_propertyId() {
		$statementGroup = new StatementGroup( new PropertyId( 'P42' ) );
		$this->assertEquals( new PropertyId( 'P42' ), $statementGroup->getPropertyId() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructor_invalidArgument() {
		new StatementGroup( 'foo' );
	}

	public function testAddStatement_validPropertyId() {
		$statementGroup = new StatementGroup( 42 );
		$statement = new Statement( new PropertyNoValueSnak( 42 ) );
		$statementGroup->addStatement( $statement );

		$this->assertEquals( array( $statement ), $statementGroup->getByRank( Statement::RANK_NORMAL ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddStatement_invalidPropertyId() {
		$statementGroup = new StatementGroup( 42 );
		$statement = new Statement( new PropertyNoValueSnak( 12 ) );
		$statementGroup->addStatement( $statement );
	}

	public function testAddStatements_validPropertyIds() {
		$statementGroup = new StatementGroup( 42 );
		$foo = new Statement( new PropertyValueSnak( 42, new StringValue( 'foo' ) ) );
		$bar = new Statement( new PropertyValueSnak( 42, new StringValue( 'bar' ) ) );
		$baz = new Statement( new PropertyValueSnak( 42, new StringValue( 'baz' ) ) );
		$baz->setRank( Statement::RANK_PREFERRED );
		$statementGroup->addStatements( array( $foo, $bar, $baz ) );

		$this->assertEquals( array(), $statementGroup->getByRank( Statement::RANK_DEPRECATED ) );
		$this->assertEquals( array( $foo, $bar ), $statementGroup->getByRank( Statement::RANK_NORMAL ) );
		$this->assertEquals( array( $baz ), $statementGroup->getByRank( Statement::RANK_PREFERRED ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddStatements_invalidPropertyIds() {
		$statementGroup = new StatementGroup( 42 );
		$foo = new Statement( new PropertyValueSnak( 42, new StringValue( 'foo' ) ) );
		$bar = new Statement( new PropertyValueSnak( 42, new StringValue( 'bar' ) ) );
		$baz = new Statement( new PropertyValueSnak( 12, new StringValue( 'baz' ) ) );
		$statementGroup->addStatements( array( $foo, $bar, $baz ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddStatements_noStatement() {
		$statementGroup = new StatementGroup( 42 );
		$statement = new Statement( new PropertyNoValueSnak( 12 ) );
		$statementGroup->addStatements( array( $statement, 'foo' ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddStatements_noArray() {
		$statementGroup = new StatementGroup( 42 );
		$statementGroup->addStatements( 'foo' );
	}

	public function testToArray() {
		$statementGroup = new StatementGroup( 42 );
		$foo = new Statement( new PropertyValueSnak( 42, new StringValue( 'foo' ) ) );
		$bar = new Statement( new PropertyValueSnak( 42, new StringValue( 'bar' ) ) );
		$baz = new Statement( new PropertyValueSnak( 42, new StringValue( 'baz' ) ) );
		$statementGroup->addStatements( array( $foo, $bar, $baz ) );

		$this->assertEquals( array( $foo, $bar, $baz ), $statementGroup->toArray() );
	}

	public function testCount_emptyGroup() {
		$statementGroup = new StatementGroup( 42 );

		$this->assertEquals( 0, $statementGroup->count() );
	}

	public function testCount_filledGroup() {
		$statementGroup = new StatementGroup( 42 );
		$foo = new Statement( new PropertyValueSnak( 42, new StringValue( 'foo' ) ) );
		$bar = new Statement( new PropertyValueSnak( 42, new StringValue( 'bar' ) ) );
		$baz = new Statement( new PropertyValueSnak( 42, new StringValue( 'baz' ) ) );
		$statementGroup->addStatements( array( $foo, $bar, $baz ) );

		$this->assertEquals( 3, $statementGroup->count() );
	}

	public function testEmpty_emptyGroup() {
		$statementGroup = new StatementGroup( 42 );

		$this->assertTrue( $statementGroup->isEmpty() );
	}

	public function testEmpty_filledGroup() {
		$statementGroup = new StatementGroup( 42 );
		$foo = new Statement( new PropertyValueSnak( 42, new StringValue( 'foo' ) ) );
		$bar = new Statement( new PropertyValueSnak( 42, new StringValue( 'bar' ) ) );
		$baz = new Statement( new PropertyValueSnak( 42, new StringValue( 'baz' ) ) );
		$statementGroup->addStatements( array( $foo, $bar, $baz ) );

		$this->assertFalse( $statementGroup->isEmpty() );
	}

}
