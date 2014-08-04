<?php

namespace Wikibase\Test\Snak;

use Exception;
use ReflectionClass;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\Snak;

/**
 * @group Wikibase
 * @group WikibaseDataModel
 * @group WikibaseSnak
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
abstract class SnakObjectTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Returns the name of the concrete class tested by this test.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public abstract function getClass();

	/**
	 * First element can be a boolean indication if the successive values are valid,
	 * or a string indicating the type of exception that should be thrown (ie not valid either).
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function constructorProvider() {
		return array(
			array( true, new PropertyId( 'P1' ) ),
			array( true, new PropertyId( 'P9001' ) ),
		);
	}

	/**
	 * Creates and returns a new instance of the concrete class.
	 *
	 * @since 0.1
	 *
	 * @return mixed
	 */
	public function newInstance() {
		$reflector = new ReflectionClass( $this->getClass() );
		$args = func_get_args();
		$instance = $reflector->newInstanceArgs( $args );
		return $instance;
	}

	/**
	 * @since 0.1
	 *
	 * @return array [instance, constructor args]
	 */
	public function instanceProvider() {
		$callable = array( $this, 'newInstance' );

		return array_filter( array_map(
			function( array $args ) use ( $callable ) {
				$isValid = array_shift( $args ) === true;

				if ( $isValid ) {
					return array( call_user_func_array( $callable, $args ), $args );
				}
				else {
					return false;
				}
			},
			$this->constructorProvider()
		), 'is_array' );
	}

	/**
	 * @dataProvider constructorProvider
	 *
	 * @since 0.1
	 */
	public function testConstructor() {
		$args = func_get_args();

		$valid = array_shift( $args );

		// TODO: use setExpectedException rather then this clutter
		// TODO: separate valid from invalid cases (different test methods)
		try {
			$dataItem = call_user_func_array( array( $this, 'newInstance' ), $args );
			$this->assertInstanceOf( $this->getClass(), $dataItem );
		}
		catch ( Exception $ex ) {
			if ( $valid === true ) {
				throw $ex;
			}

			if ( is_string( $valid ) ) {
				$this->assertEquals( $valid, get_class( $ex ) );
			}
			else {
				$this->assertFalse( $valid );
			}
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetHash( Snak $snak ) {
		$propertiesSerialization = $this->getPropertiesSerialization( $snak );
		$serialization = sprintf(
			'C:%d:"%s":%d:{%s}',
			strlen( $this->getClass() ),
			$this->getClass(),
			strlen( $propertiesSerialization ),
			$propertiesSerialization
		);

		// XXX: This makes sure the new implementation mimics the old serialize() based one.
		// This check must be removed when the Snak object serializes differently.
		$this->assertEquals( serialize( $snak ), $serialization );

		$hash = $snak->getHash();
		$this->assertInternalType( 'string', $hash );
		$this->assertEquals( 40, strlen( $hash ) );
		$this->assertSame( sha1( $serialization ), $hash );
	}

	protected function getPropertiesSerialization( Snak $snak ) {
		return sprintf( 'i:%s;', $snak->getPropertyId()->getNumericId() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetPropertyId( Snak $snak ) {
		$propertyId = $snak->getPropertyId();
		$this->assertInstanceOf( 'Wikibase\DataModel\Entity\PropertyId', $propertyId );
	}

}
