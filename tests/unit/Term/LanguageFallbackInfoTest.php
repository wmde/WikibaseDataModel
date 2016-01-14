<?php

namespace Wikibase\DataModel\Tests\Term;

use InvalidArgumentException;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\LanguageFallbackInfo;

/**
 * @covers Wikibase\DataModel\Term\LanguageFallbackInfo
 *
 * @licence GNU GPL v2+
 * @author Jan Zerebecki < jan.wikimedia@zerebecki.de >
 */
class LanguageFallbackInfoTest extends \PHPUnit_Framework_TestCase {

	public function testConstructorSetsFields() {
		$term = new LanguageFallbackInfo( 'fooa', 'foos' );
		$this->assertEquals( 'fooa', $term->getActualLanguageCode() );
		$this->assertEquals( 'foos', $term->getSourceLanguageCode() );
	}

	public function testConstructorWithNullAsSource() {
		$term = new LanguageFallbackInfo( 'fooa', null );
		$this->assertNull( $term->getSourceLanguageCode() );
	}

	/**
	 * @dataProvider invalidLanguageCodeProvider
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenInvalidActualLanguageCode_constructorThrowsException( $languageCode ) {
		new LanguageFallbackInfo( $languageCode, 'foos' );
	}

	public function invalidLanguageCodeProvider() {
		return array(
			array( null ),
			array( 21 ),
			array( '' ),
		);
	}

	/**
	 * @dataProvider invalidSourceLanguageCodeProvider
	 * @expectedException InvalidArgumentException
	 */
	public function testGivenInvalidSourceLanguageCode_constructorThrowsException( $languageCode ) {
		new LanguageFallbackInfo( 'fooa', $languageCode );
	}

	public function invalidSourceLanguageCodeProvider() {
		return array(
			array( 21 ),
			array( '' ),
		);
	}

}
