<?php

namespace Wikibase\DataModel\Tests\Entity;

use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpRemove;
use Wikibase\DataModel\Entity\Diff\EntityDiff;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @deprecated
 * This test class is to be phased out, and should not be used from outside of the component!
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
abstract class EntityTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @since 0.1
	 *
	 * @return Entity
	 */
	protected abstract function getNewEmpty();

	public function instanceProvider() {
		$entities = array();

		// empty
		$entity = $this->getNewEmpty();
		$entities[] = $entity;

		// ID only
		$entity = clone $entity;
		$entity->setId( 44 );

		$entities[] = $entity;

		// with labels and stuff
		$entity = $this->getNewEmpty();
		$entity->getFingerprint()->setAliasGroup( 'en', array( 'o', 'noez' ) );
		$entity->getFingerprint()->setLabel( 'de', 'spam' );
		$entity->getFingerprint()->setDescription( 'en', 'foo bar baz' );

		$entities[] = $entity;

		// with labels etc and ID
		$entity = clone $entity;
		$entity->setId( 42 );

		$entities[] = $entity;

		$argLists = array();

		foreach ( $entities as $entity ) {
			$argLists[] = array( $entity );
		}

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 * @param Entity $entity
	 */
	public function testCopy( Entity $entity ) {
		$copy = $entity->copy();

		// The equality method alone is not enough since it does not check the IDs.
		$this->assertTrue( $entity->equals( $copy ) );
		$this->assertEquals( $entity->getId(), $copy->getId() );

		$this->assertFalse( $entity === $copy );
	}

	public function testCopyRetainsLabels() {
		$item = new Item();

		$item->getFingerprint()->setLabel( 'en', 'foo' );
		$item->getFingerprint()->setLabel( 'de', 'bar' );

		$newItem = unserialize( serialize( $item ) );

		$this->assertTrue( $newItem->getFingerprint()->getLabels()->hasTermForLanguage( 'en' ) );
		$this->assertTrue( $newItem->getFingerprint()->getLabels()->hasTermForLanguage( 'de' ) );
	}

	/**
	 * @dataProvider instanceProvider
	 * @param Entity $entity
	 */
	public function testSerialize( Entity $entity ) {
		$string = serialize( $entity );

		$this->assertInternalType( 'string', $string );

		$instance = unserialize( $string );

		$this->assertTrue( $entity->equals( $instance ) );
		$this->assertEquals( $entity->getId(), $instance->getId() );
	}

	public function diffProvider() {
		$argLists = array();

		$emptyDiff = EntityDiff::newForType( $this->getNewEmpty()->getType() );

		$entity0 = $this->getNewEmpty();
		$entity1 = $this->getNewEmpty();
		$expected = clone $emptyDiff;

		$argLists[] = array( $entity0, $entity1, $expected );

		$entity0 = $this->getNewEmpty();
		$entity0->getFingerprint()->setAliasGroup( 'nl', array( 'bah' ) );
		$entity0->getFingerprint()->setAliasGroup( 'de', array( 'bah' ) );

		$entity1 = $this->getNewEmpty();
		$entity1->getFingerprint()->setAliasGroup( 'en', array( 'foo', 'bar' ) );
		$entity1->getFingerprint()->setAliasGroup( 'nl', array( 'bah', 'baz' ) );

		$entity1->getFingerprint()->setDescription( 'en', 'onoez' );

		$expected = new EntityDiff( array(
			'aliases' => new Diff( array(
				'en' => new Diff( array(
					new DiffOpAdd( 'foo' ),
					new DiffOpAdd( 'bar' ),
				), false ),
				'de' => new Diff( array(
					new DiffOpRemove( 'bah' ),
				), false ),
				'nl' => new Diff( array(
					new DiffOpAdd( 'baz' ),
				), false )
			) ),
			'description' => new Diff( array(
				'en' => new DiffOpAdd( 'onoez' ),
			) ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );

		$entity0 = clone $entity1;
		$entity1 = clone $entity1;
		$expected = clone $emptyDiff;

		$argLists[] = array( $entity0, $entity1, $expected );

		$entity0 = $this->getNewEmpty();

		$entity1 = $this->getNewEmpty();
		$entity1->getFingerprint()->setLabel( 'en', 'onoez' );

		$expected = new EntityDiff( array(
			'label' => new Diff( array(
				'en' => new DiffOpAdd( 'onoez' ),
			) ),
		) );

		$argLists[] = array( $entity0, $entity1, $expected );

		return $argLists;
	}

	/**
	 * @dataProvider diffProvider
	 * @param Entity $entity0
	 * @param Entity $entity1
	 * @param EntityDiff $expected
	 */
	public function testDiffEntities( Entity $entity0, Entity $entity1, EntityDiff $expected ) {
		$actual = $entity0->getDiff( $entity1 );

		$this->assertInstanceOf( 'Wikibase\DataModel\Entity\Diff\EntityDiff', $actual );
		$this->assertSameSize( $expected, $actual );

		// TODO: equality check
		// (simple serialize does not work, since the order is not relevant, and not only on the top level)
	}

	/**
	 * @dataProvider instanceProvider
	 * @param Entity $entity
	 */
	public function testGetClaims( Entity $entity ) {
		$claims = $entity->getClaims();

		$this->assertInternalType( 'array', $claims );
	}

	public function testWhenNoStuffIsSet_getFingerprintReturnsEmptyFingerprint() {
		$entity = $this->getNewEmpty();

		$this->assertEquals(
			new Fingerprint(),
			$entity->getFingerprint()
		);
	}

	public function testWhenSettingFingerprint_getFingerprintReturnsIt() {
		$fingerprint = new Fingerprint(
			new TermList( array(
				new Term( 'en', 'english label' ),
			) ),
			new TermList( array(
				new Term( 'en', 'english description' )
			) ),
			new AliasGroupList( array(
				new AliasGroup( 'en', array( 'first en alias', 'second en alias' ) )
			) )
		);

		$entity = $this->getNewEmpty();
		$entity->setFingerprint( $fingerprint );
		$newFingerprint = $entity->getFingerprint();

		$this->assertEquals( $fingerprint, $newFingerprint );
	}

}
