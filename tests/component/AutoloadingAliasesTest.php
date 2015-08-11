<?php

namespace Wikibase\DataModel\Tests;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AutoloadingAliasesTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider oldNameProvider
	 */
	public function testAliasExists( $className ) {
		$this->assertTrue(
			class_exists( $className ) || interface_exists( $className ),
			'Class name "' . $className . '" should still exist as alias'
		);
	}

	public function oldNameProvider() {
		return array_map(
			function( $className ) {
				return array( $className );
			},
			array(
				// Full qualified aliases go here.
				'Wikibase\DataModel\Claim\Claim',
				'Wikibase\DataModel\Claim\ClaimGuid',
				'Wikibase\DataModel\Entity\Entity',
				'Wikibase\DataModel\StatementListProvider'
			)
		);
	}

}
