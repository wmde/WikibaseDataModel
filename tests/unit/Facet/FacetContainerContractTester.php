<?php

namespace Wikibase\DataModel\Tests\Facet;

use stdClass;
use Wikibase\DataModel\Facet\FacetContainer;
use PHPUnit_Framework_Assert;
use Wikibase\DataModel\Facet\MismatchingFacetException;
use Wikibase\DataModel\Facet\NoSuchFacetException;

/**
 * Helper for testing implementations of FacetContainer
 *
 * @covers Wikibase\DataModel\Facet\FacetContainer
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class FacetContainerContractTester {

	public function testHasFacet( FacetContainer $container ) {
		PHPUnit_Framework_Assert::assertFalse( $container->hasFacet( 'foo' ) );

		$facet = new stdClass();
		$container->addFacet( 'foo', $facet );

		PHPUnit_Framework_Assert::assertTrue( $container->hasFacet( 'foo' ) );
	}

	public function testGetFacet( FacetContainer $container ) {
		$facet = new stdClass();
		$container->addFacet( 'foo', $facet );

		PHPUnit_Framework_Assert::assertSame( $facet, $container->getFacet( 'foo' ) );
		PHPUnit_Framework_Assert::assertSame( $facet, $container->getFacet( 'foo', 'stdClass' ) );

		try {
			$container->getFacet( 'foo', 'Wikibase\DataModel\Snak' );
			PHPUnit_Framework_Assert::fail( 'getFacet() should fail with a MismatchingFacetException' );
		} catch ( MismatchingFacetException $ex ) {
			// ok
		}

		try {
			$container->getFacet( 'xyzzy' );
			PHPUnit_Framework_Assert::fail( 'getFacet() should fail with a NoSuchFacetException' );
		} catch ( NoSuchFacetException $ex ) {
			// ok
		}
	}

	public function testAddFacet( FacetContainer $container ) {
		$facet = new stdClass();
		$facet2 = new stdClass();

		$container->addFacet( 'foo', $facet );
		$container->addFacet( 'foo', $facet2 );

		PHPUnit_Framework_Assert::assertSame( $facet2, $container->getFacet( 'foo' ) );
	}

}
