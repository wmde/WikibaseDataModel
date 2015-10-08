<?php

namespace Wikibase\DataModel\Tests\Statement;

use PHPUnit_Framework_TestCase;
use Wikibase\DataModel\Statement\StatementRank;

/**
 * @covers Wikibase\DataModel\Statement\StatementRank
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseStatement
 *
 * @license GNU GPL v2+
 * @author Thiemo Kreuz
 */
class StatementRankTest extends PHPUnit_Framework_TestCase {

	public function testConstants() {
		$this->assertSame( 0, StatementRank::DEPRECATED );
		$this->assertSame( 1, StatementRank::NORMAL );
		$this->assertSame( 2, StatementRank::PREFERRED );
	}

	public function testGetNames() {
		$this->assertSame( [
			'deprecated',
			'normal',
			'preferred',
		], StatementRank::getNames() );
	}

	public function testGetAllRanks() {
		$this->assertSame( [
			'deprecated' => 0,
			'normal' => 1,
			'preferred' => 2,
		], StatementRank::getAllRanks() );
	}

	/**
	 * @dataProvider notInIntegerRangeProvider
	 */
	public function testGivenInvalidRank_assertIsValidThrowsException( $rank ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::assertIsValid( $rank );
	}

	/**
	 * @dataProvider integerRangeProvider
	 */
	public function testGivenValidRank_assertIsValidSucceeds( $rank ) {
		StatementRank::assertIsValid( $rank );
		$this->assertTrue( true );
	}

	/**
	 * @dataProvider notInIntegerRangeProvider
	 */
	public function testGivenInvalidRank_isValidFails( $rank ) {
		$this->assertFalse( StatementRank::isValid( $rank ) );
	}

	/**
	 * @dataProvider integerRangeProvider
	 */
	public function testGivenInvalidRank_isValidSucceeds( $rank ) {
		$this->assertTrue( StatementRank::isValid( $rank ) );
	}

	/**
	 * @dataProvider notInIntegerRangeProvider
	 */
	public function testGivenInvalidRank_isFalseThrowsException( $rank ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::isFalse( $rank );
	}

	/**
	 * @dataProvider isFalseProvider
	 */
	public function testIsFalse( $rank, $expected ) {
		$this->assertSame( $expected, StatementRank::isFalse( $rank ) );
	}

	public function isFalseProvider() {
		return [
			[ 0, true ],
			[ 1, false ],
			[ 2, false ],
		];
	}

	/**
	 * @dataProvider invalidComparisonPairProvider
	 */
	public function testGivenInvalidRank_isEqualThrowsException( $rank1, $rank2 ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::isEqual( $rank1, $rank2 );
	}

	/**
	 * @dataProvider isEqualProvider
	 */
	public function testIsEqual( $rank1, $rank2, $expected ) {
		$this->assertSame( $expected, StatementRank::isEqual( $rank1, $rank2 ) );
	}

	public function isEqualProvider() {
		return [
			[ null, null, true ],
			[ null, 0, false ],
			[ null, 1, false ],
			[ null, 2, false ],
			[ 0, null, false ],
			[ 0, 0, true ],
			[ 0, 1, false ],
			[ 0, 2, false ],
			[ 1, null, false ],
			[ 1, 0, false ],
			[ 1, 1, true ],
			[ 1, 2, false ],
			[ 2, null, false ],
			[ 2, 0, false ],
			[ 2, 1, false ],
			[ 2, 2, true ],
		];
	}

	/**
	 * @dataProvider invalidComparisonPairProvider
	 */
	public function testGivenInvalidRank_isLowerThrowsException( $rank1, $rank2 ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::isLower( $rank1, $rank2 );
	}

	/**
	 * @dataProvider isLowerProvider
	 */
	public function testIsLower( $rank1, $rank2, $expected ) {
		$this->assertSame( $expected, StatementRank::isLower( $rank1, $rank2 ) );
	}

	public function isLowerProvider() {
		return [
			[ null, null, false ],
			[ null, 0, true ],
			[ null, 1, true ],
			[ null, 2, true ],
			[ 0, null, false ],
			[ 0, 0, false ],
			[ 0, 1, true ],
			[ 0, 2, true ],
			[ 1, null, false ],
			[ 1, 0, false ],
			[ 1, 1, false ],
			[ 1, 2, true ],
			[ 2, null, false ],
			[ 2, 0, false ],
			[ 2, 1, false ],
			[ 2, 2, false ],
		];
	}

	/**
	 * @dataProvider invalidComparisonPairProvider
	 */
	public function testGivenInvalidRank_isHigherThrowsException( $rank1, $rank2 ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::isHigher( $rank1, $rank2 );
	}

	/**
	 * @dataProvider isHigherProvider
	 */
	public function testIsHigher( $rank1, $rank2, $expected ) {
		$this->assertSame( $expected, StatementRank::isHigher( $rank1, $rank2 ) );
	}

	public function isHigherProvider() {
		return [
			[ null, null, false ],
			[ null, 0, false ],
			[ null, 1, false ],
			[ null, 2, false ],
			[ 0, null, true ],
			[ 0, 0, false ],
			[ 0, 1, false ],
			[ 0, 2, false ],
			[ 1, null, true ],
			[ 1, 0, true ],
			[ 1, 1, false ],
			[ 1, 2, false ],
			[ 2, null, true ],
			[ 2, 0, true ],
			[ 2, 1, true ],
			[ 2, 2, false ],
		];
	}

	/**
	 * @dataProvider invalidComparisonPairProvider
	 */
	public function testGivenInvalidRank_compareThrowsException( $rank1, $rank2 ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::compare( $rank1, $rank2 );
	}

	/**
	 * @dataProvider compareProvider
	 */
	public function testCompare( $rank1, $rank2, $expected ) {
		$this->assertSame( $expected, StatementRank::compare( $rank1, $rank2 ) );
	}

	public function compareProvider() {
		return [
			[ null, null, 0 ],
			[ null, 0, -1 ],
			[ null, 1, -1 ],
			[ null, 2, -1 ],
			[ 0, null, 1 ],
			[ 0, 0, 0 ],
			[ 0, 1, -1 ],
			[ 0, 2, -1 ],
			[ 1, null, 1 ],
			[ 1, 0, 1 ],
			[ 1, 1, 0 ],
			[ 1, 2, -1 ],
			[ 2, null, 1 ],
			[ 2, 0, 1 ],
			[ 2, 1, 1 ],
			[ 2, 2, 0 ],
		];
	}

	/**
	 * @dataProvider neitherIntegerRangeNorNullProvider
	 */
	public function testGivenInvalidRank_findBestRankThrowsException( $rank ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::findBestRank( $rank );
	}

	/**
	 * @dataProvider invalidComparisonPairProvider
	 */
	public function testGivenInvalidArray_findBestRankThrowsException( $rank1, $rank2 ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		StatementRank::findBestRank( [ $rank1, $rank2 ] );
	}

	/**
	 * @dataProvider findBestRankProvider
	 */
	public function testFindBestRank( $ranks, $expected ) {
		$this->assertSame( $expected, StatementRank::findBestRank( $ranks ) );
	}

	public function findBestRankProvider() {
		return [
			[ null, null ],
			[ 0, 0 ],
			[ 1, 1 ],
			[ [], null ],
			[ [ null ], null ],
			[ [ 0 ], 0 ],
			[ [ 1 ], 1 ],
			[ [ null, 0 ], 0 ],
			[ [ 0, null ], 0 ],
			[ [ 0, 1 ], 1 ],
			[ [ null, 0, 1, 2 ], 2 ],
			[ [ 2, 1, 0, null ], 2 ],
		];
	}

	public function integerRangeProvider() {
		return [
			[ 0 ],
			[ 1 ],
			[ 2 ],
		];
	}

	public function neitherIntegerRangeNorNullProvider() {
		return [
			[ false ],
			[ true ],
			[ NAN ],
			[ INF ],
			[ '0' ],
			[ '1' ],
			[ 0.0 ],
			[ 1.0 ],
			[ -1 ],
			[ 3 ],
		];
	}

	public function notInIntegerRangeProvider() {
		$invalid = $this->neitherIntegerRangeNorNullProvider();
		$invalid[] = [ null ];
		return $invalid;
	}

	public function invalidComparisonPairProvider() {
		$invalid = $this->neitherIntegerRangeNorNullProvider();

		foreach ( $invalid as $args ) {
			yield [ 1, $args[0] ];
			yield [ $args[0], 1 ];
		}
	}

}
