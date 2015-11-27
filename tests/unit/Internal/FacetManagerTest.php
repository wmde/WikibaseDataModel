<?php

namespace Wikibase\DataModel\Tests\Internal;

use Wikibase\DataModel\Tests\Facet\FacetContainerContractTester;
use Wikibase\DataModel\Internal\FacetManager;

/**
 * @covers Wikibase\DataModel\Internal\FacetManager
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class FacetManagerTest extends \PHPUnit_Framework_TestCase {

	public function testHasFacet() {
		$tester = new FacetContainerContractTester();
		$container = new FacetManager();

		$tester->testHasFacet( $container );
	}

	public function testGetFacet() {
		$tester = new FacetContainerContractTester();
		$container = new FacetManager();

		$tester->testGetFacet( $container );
	}

	public function testAddFacet() {
		$tester = new FacetContainerContractTester();
		$container = new FacetManager();

		$tester->testAddFacet( $container );
	}

}
