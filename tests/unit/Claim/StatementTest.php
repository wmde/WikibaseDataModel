<?php

namespace Wikibase\Test;

use DataValues\StringValue;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;

/**
 * @covers Wikibase\DataModel\Claim\Statement
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseStatement
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StatementTest extends ClaimTest {

	public function instanceProvider() {
		$instances = array();

		$id42 = new PropertyId( 'P42' );

		$baseInstance = new Statement( new PropertyNoValueSnak( $id42 ) );

		$instances[] = $baseInstance;

		$instance = clone $baseInstance;
		$instance->setRank( Claim::RANK_PREFERRED );

		$instances[] = $instance;

		$newInstance = clone $instance;

		$instances[] = $newInstance;

		$instance = clone $baseInstance;

		$instance->setReferences( new ReferenceList( array(
			new Reference( new SnakList(
				new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'a' ) )
			) )
		) ) );

		$instances[] = $instance;

		$argLists = array();

		foreach ( $instances as $instance ) {
			$argLists[] = array( $instance );
		}

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetReferences( Statement $statement ) {
		$this->assertInstanceOf( '\Wikibase\References', $statement->getReferences() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSetReferences( Statement $statement ) {
		$references = new ReferenceList( array(
			new Reference( new SnakList(
				new PropertyValueSnak(
					new PropertyId( 'P1' ),
					new StringValue( 'a' )
				)
			) ) )
		);


		$statement->setReferences( $references );

		$this->assertEquals( $references, $statement->getReferences() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetRank( Claim $claim ) {
		$rank = $claim->getRank();
		$this->assertInternalType( 'integer', $rank );

		$ranks = array( Claim::RANK_DEPRECATED, Claim::RANK_NORMAL, Claim::RANK_PREFERRED );
		$this->assertTrue( in_array( $rank, $ranks ), true );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSetRank( Statement $statement ) {
		$statement->setRank( Claim::RANK_DEPRECATED );
		$this->assertEquals( Claim::RANK_DEPRECATED, $statement->getRank() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSetInvalidRank( Statement $statement ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		$statement->setRank( 9001 );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSetRankToTruth( Statement $statement ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		$statement->setRank( Claim::RANK_TRUTH );
	}

	public function testStatementRankCompatibility() {
		$this->assertEquals( Claim::RANK_DEPRECATED, Statement::RANK_DEPRECATED );
		$this->assertEquals( Claim::RANK_PREFERRED, Statement::RANK_PREFERRED );
		$this->assertEquals( Claim::RANK_NORMAL, Statement::RANK_NORMAL );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testIsClaim( Statement $statement ) {
		$this->assertInstanceOf( '\Wikibase\Claim', $statement );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetPropertyId( Claim $statement ) {
		$this->assertEquals(
			$statement->getMainSnak()->getPropertyId(),
			$statement->getPropertyId()
		);
	}

	public function testGetHash() {
		$claim0 = new Statement( new PropertyNoValueSnak( 42 ) );
		$claim0->setGuid( 'claim0' );
		$claim0->setRank( Claim::RANK_DEPRECATED );

		$claim1 = new Statement( new PropertyNoValueSnak( 42 ) );
		$claim1->setGuid( 'claim1' );
		$claim1->setRank( Claim::RANK_DEPRECATED );

		$this->assertEquals( $claim0->getHash(), $claim1->getHash() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetAllSnaks( Claim $claim ) {
		/* @var Statement $statement */
		$statement = $claim;
		$snaks = $statement->getAllSnaks();

		$c = count( $statement->getQualifiers() ) + 1;

		/* @var Reference $reference */
		foreach ( $statement->getReferences() as $reference ) {
			$c += count( $reference->getSnaks() );
		}

		$this->assertGreaterThanOrEqual( $c, count( $snaks ), "At least one snak per Qualifier and Reference" );
	}
}
