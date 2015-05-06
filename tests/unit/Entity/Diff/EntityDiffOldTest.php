<?php

namespace Wikibase\DataModel\Tests\Entity\Diff;

use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;

/**
 * @covers Wikibase\DataModel\Entity\Diff\EntityDiff
 *
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseDiff
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 * @author Jens Ohlig <jens.ohlig@wikimedia.de>
 */
abstract class EntityDiffOldTest extends \PHPUnit_Framework_TestCase {

	private static function newEntity( $entityType ) {
		switch ( $entityType ) {
			case Item::ENTITY_TYPE:
				return new Item();
			case Property::ENTITY_TYPE:
				return Property::newFromType( 'string' );
			default:
				throw new \RuntimeException( "unknown entity type: $entityType" );
		}
	}

	public static function generateApplyData( $entityType ) {
		$tests = array();

		// #0: add label
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setLabel( 'en', 'Test' );

		$b = $a->copy();
		$b->getFingerprint()->setLabel( 'de', 'Test' );

		$tests[] = array( $a, $b );

		// #1: remove label
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setLabel( 'en', 'Test' );
		$a->getFingerprint()->setLabel( 'de', 'Test' );

		$b = self::newEntity( $entityType );
		$b->getFingerprint()->setLabel( 'de', 'Test' );

		$tests[] = array( $a, $b );

		// #2: change label
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setLabel( 'en', 'Test' );

		$b = $a->copy();
		$b->getFingerprint()->setLabel( 'en', 'Test!!!' );

		// #3: add description ------------------------------
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setDescription( 'en', 'Test' );

		$b = $a->copy();
		$b->getFingerprint()->setDescription( 'de', 'Test' );

		$tests[] = array( $a, $b );

		// #4: remove description
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setDescription( 'en', 'Test' );
		$a->getFingerprint()->setDescription( 'de', 'Test' );

		$b = $a->copy();
		$b->getFingerprint()->removeDescription( 'en' );

		$tests[] = array( $a, $b );

		// #5: change description
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setDescription( 'en', 'Test' );

		$b = $a->copy();
		$b->getFingerprint()->setDescription( 'en', 'Test!!!' );

		$tests[] = array( $a, $b );

		// #6: add alias ------------------------------
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setAliasGroup( 'en', array( 'Foo', 'Bar' ) );

		$b = $a->copy();
		$b->getFingerprint()->setAliasGroup( 'en', array( 'Foo', 'Bar', 'Quux' ) );

		$tests[] = array( $a, $b );

		// #7: add alias language
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setAliasGroup( 'en', array( 'Foo', 'Bar' ) );

		$b = $a->copy();
		$b->getFingerprint()->setAliasGroup( 'de', array( 'Quux' ) );

		$tests[] = array( $a, $b );

		// #8: remove alias
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setAliasGroup( 'en', array( 'Foo', 'Bar' ) );

		$b = $a->copy();
		$b->getFingerprint()->setAliasGroup( 'en', array( 'Bar' ) );

		$tests[] = array( $a, $b );

		// #9: remove alias language
		$a = self::newEntity( $entityType );
		$a->getFingerprint()->setAliasGroup( 'en', array( 'Foo', 'Bar' ) );

		$b = $a->copy();
		$b->getFingerprint()->setAliasGroup( 'en', array() );

		$tests[] = array( $a, $b );
		return $tests;
	}

	/**
	 *
	 * @dataProvider provideApplyData
	 */
	public function testApply( Entity $a, Entity $b ) {
		$a->patch( $a->getDiff( $b ) );
		$this->assertTrue( $a->getFingerprint()->equals( $b->getFingerprint() ) );
	}

	public function provideConflictDetection() {
		$cases = array();

		// #0: adding a label where there was none before
		$base = self::newEntity( Item::ENTITY_TYPE );
		$current = $base->copy();

		$new = $base->copy();
		$new->getFingerprint()->setLabel( 'en', 'TEST' );

		$cases[] = array(
			$base,
			$current,
			$new,
			0 // there should eb no conflicts.
		);

		// #1: adding an alias where there was none before
		$base = self::newEntity( Item::ENTITY_TYPE );
		$current = $base;

		$new = $base->copy();
		$new->getFingerprint()->setAliasGroup( 'en', array( 'TEST' ) );

		$cases[] = array(
			$base,
			$current,
			$new,
			0 // there should eb no conflicts.
		);

		// #2: adding an alias where there already was one before
		$base = self::newEntity( Item::ENTITY_TYPE );
		$base->getFingerprint()->setAliasGroup( 'en', array( 'Foo' ) );
		$current = $base;

		$new = $base->copy();
		$new->getFingerprint()->setAliasGroup( 'en', array( 'Foo', 'Bar' ) );

		$cases[] = array(
			$base,
			$current,
			$new,
			0 // there should be no conflicts.
		);

		// #3: adding an alias where there already was one in another language
		$base = self::newEntity( Item::ENTITY_TYPE );
		$base->getFingerprint()->setAliasGroup( 'en', array( 'Foo' ) );
		$current = $base;

		$new = $base->copy();
		$new->getFingerprint()->setAliasGroup( 'de', array( 'Bar' ) );

		$cases[] = array(
			$base,
			$current,
			$new,
			0 // there should be no conflicts.
		);

		return $cases;
	}

	/**
	 * @dataProvider provideConflictDetection
	 */
	public function testConflictDetection( Entity $base, Entity $current, Entity $new, $expectedConflicts ) {
		$patch = $base->getDiff( $new );

		$patchedCurrent = $current->copy();
		$patchedCurrent->patch( $patch );

		$cleanPatch = $base->getDiff( $patchedCurrent );

		$conflicts = $patch->count() - $cleanPatch->count();

		$this->assertEquals( $expectedConflicts, $conflicts, "check number of conflicts detected" );
	}

}
