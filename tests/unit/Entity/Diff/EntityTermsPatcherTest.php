<?php

namespace Wikibase\DataModel\Tests\Entity\Diff;

use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpChange;
use Wikibase\DataModel\Entity\Diff\EntityDiff;
use Wikibase\DataModel\Entity\Diff\EntityTermsPatcher;
use Wikibase\DataModel\Term\EntityTerms;

/**
 * @covers Wikibase\DataModel\Entity\Diff\EntityTermsPatcher
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityTermsPatcherTest extends \PHPUnit_Framework_TestCase {

	public function testGivenEmptyDiff_entityTermsIsReturnedAsIs() {
		$entityTerms = $this->newSimpleEntityTerms();

		$this->assertEntityTermsResultsFromPatch( $entityTerms, $entityTerms, new EntityDiff() );
	}

	private function newSimpleEntityTerms() {
		$entityTerms = new EntityTerms();

		$entityTerms->setLabel( 'en', 'foo' );
		$entityTerms->setDescription( 'de', 'bar' );
		$entityTerms->setAliasGroup( 'nl', array( 'baz' ) );

		return $entityTerms;
	}

	private function assertEntityTermsResultsFromPatch( EntityTerms $expected, EntityTerms $original, EntityDiff $patch ) {
		$this->assertTrue( $expected->equals( $this->getPatchedEntityTerms( $original, $patch ) ) );
	}

	private function getPatchedEntityTerms( EntityTerms $entityTerms, EntityDiff $patch ) {
		$patched = unserialize( serialize( $entityTerms ) );

		$patcher = new EntityTermsPatcher();
		$patcher->patchEntityTerms( $patched, $patch );

		return $patched;
	}

	public function testLabelDiffOnlyAffectsLabels() {
		$entityTerms = $this->newSimpleEntityTerms();

		$patch = new EntityDiff( array(
			'label' => new Diff( array(
				'en' => new DiffOpChange( 'foo', 'bar' ),
				'de' => new DiffOpAdd( 'baz' ),
			), true )
		) );

		$expectedEntityTerms = $this->newSimpleEntityTerms();
		$expectedEntityTerms->setLabel( 'en', 'bar' );
		$expectedEntityTerms->setLabel( 'de', 'baz' );

		$this->assertEntityTermsResultsFromPatch( $expectedEntityTerms, $entityTerms, $patch );
	}

	public function testDescriptionDiffOnlyAffectsDescriptions() {
		$entityTerms = $this->newSimpleEntityTerms();

		$patch = new EntityDiff( array(
			'description' => new Diff( array(
				'de' => new DiffOpChange( 'bar', 'foo' ),
				'en' => new DiffOpAdd( 'baz' ),
			), true )
		) );

		$expectedEntityTerms = $this->newSimpleEntityTerms();
		$expectedEntityTerms->setDescription( 'de', 'foo' );
		$expectedEntityTerms->setDescription( 'en', 'baz' );

		$this->assertEntityTermsResultsFromPatch( $expectedEntityTerms, $entityTerms, $patch );
	}

	public function testAliasDiffOnlyAffectsAliases() {
		$entityTerms = $this->newSimpleEntityTerms();

		$patch = new EntityDiff( array(
			'aliases' => new Diff( array(
				'de' => new Diff( array( new DiffOpAdd( 'foo' ) ), true ),
			), true )
		) );

		$expectedEntityTerms = $this->newSimpleEntityTerms();
		$expectedEntityTerms->setAliasGroup( 'de', array( 'foo' ) );

		$this->assertEntityTermsResultsFromPatch( $expectedEntityTerms, $entityTerms, $patch );
	}

}
