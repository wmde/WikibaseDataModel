<?php

namespace Wikibase\DataModel\Entity;

use Diff\Comparer\CallbackComparer;
use Diff\Differ;
use Diff\MapDiffer;
use Diff\MapPatcher;
use Diff\Patcher;
use InvalidArgumentException;
use RuntimeException;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\ClaimAggregate;
use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\Internal\LegacyIdInterpreter;
use Wikibase\DataModel\Internal\ObjectComparer;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Term\OrderedTermSet;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * Represents a single Wikibase entity.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Values
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class Entity implements \Comparable, ClaimAggregate, \Serializable, FingerprintProvider {

	/**
	 * @since 0.1
	 * @var array
	 */
	protected $data;

	/**
	 * Id of the entity.
	 *
	 * This field can have several types:
	 *
	 * * EntityId: This means the entity has this id.
	 * * Null: This means the entity does not have an associated id.
	 * * False: This means the entity has an id, but it is stubbed in the $data field. Call getId to get an unstubbed version.
	 *
	 * @since 0.1
	 * @var EntityId|bool|null
	 */
	protected $id = false;

	/**
	 * @since 0.3
	 *
	 * @var Claim[]|null
	 */
	protected $claims;

	/**
	 * Returns a type identifier for the entity.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public abstract function getType();

	/**
	 * Constructor.
	 * Do not use to construct new stuff from outside of this class, use the static newFoobar methods.
	 * In other words: treat as protected (which it was, but now cannot be since we derive from Content).
	 * @protected
	 *
	 * @since 0.1
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
		$this->cleanStructure();
		$this->initializeIdField();
	}

	protected function initializeIdField() {
		if ( !array_key_exists( 'entity', $this->data ) ) {
			$this->id = null;
		}
	}

	/**
	 * Get an array representing the Entity.
	 * A new Entity can be constructed by passing this array to @see Entity::newFromArray
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function toArray() {
		$this->stub();
		return $this->data;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @since 0.3
	 *
	 * @return string
	 */
	public function serialize() {
		$data = $this->toArray();

		// Add an identifier for the serialization version so we can switch behavior in
		// the unserializer to avoid breaking compatibility after certain changes.
		$data['v'] = 1;

		return json_encode( $data );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.3
	 *
	 * @param string $value
	 *
	 * @return Entity
	 * @throws RuntimeException
	 */
	public function unserialize( $value ) {
		$unserialized = json_decode( $value, true );

		if ( is_array( $unserialized ) && array_key_exists( 'v', $unserialized ) ) {
			unset( $unserialized['v'] );

			return $this->__construct( $unserialized );
		}

		throw new RuntimeException( 'Invalid serialization passed to Entity unserializer' );
	}

	/**
	 * @since 0.3
	 *
	 * @deprecated Do not rely on this method being present, it will be removed soonish.
	 */
	public function __wakeup() {
		// Compatibility with 0.1 and 0.2 serializations.
		if ( is_int( $this->id ) ) {
			$this->id = LegacyIdInterpreter::newIdFromTypeAndNumber( $this->getType(), $this->id );
		}

		// Compatibility with 0.2 and 0.3 ItemObjects.
		// (statements key got renamed to claims)
		if ( array_key_exists( 'statements', $this->data ) ) {
			$this->data['claims'] = $this->data['statements'];
			unset( $this->data['statements'] );
		}
	}

	/**
	 * Returns the id of the entity or null if it is not in the datastore yet.
	 *
	 * @since 0.1 return type changed in 0.3
	 *
	 * @return EntityId|null
	 */
	public function getId() {
		if ( $this->id === false ) {
			$this->unstubId();
		}

		return $this->id;
	}

	private function unstubId() {
		if ( array_key_exists( 'entity', $this->data ) ) {
			$this->id = $this->getUnstubbedId( $this->data['entity'] );
		}
		else {
			$this->id = null;
		}
	}

	private function getUnstubbedId( $stubbedId ) {
		if ( is_string( $stubbedId ) ) {
			// This is unstubbing of the current stubbing format
			return $this->idFromSerialization( $stubbedId );
		}
		else {
			// This is unstubbing of the legacy stubbing format
			return LegacyIdInterpreter::newIdFromTypeAndNumber( $stubbedId[0], $stubbedId[1] );
		}
	}

	/**
	 * @since 0.5
	 *
	 * @param string $idSerialization
	 *
	 * @return EntityId
	 */
	protected abstract function idFromSerialization( $idSerialization );

	/**
	 * Can be EntityId since 0.3.
	 * The support for setting an integer here is deprecated since 0.5.
	 * New deriving classes are allowed to reject anything that is not an EntityId of the correct type.
	 *
	 * @since 0.1
	 *
	 * @param EntityId $id
	 *
	 * @throws InvalidArgumentException
	 */
	public function setId( $id ) {
		if ( $id instanceof EntityId ) {
			if ( $id->getEntityType() !== $this->getType() ) {
				throw new InvalidArgumentException( 'Attempt to set an EntityId with mismatching entity type' );
			}

			$this->id = $id;
		}
		else if ( is_integer( $id ) ) {
			$this->id = LegacyIdInterpreter::newIdFromTypeAndNumber( $this->getType(), $id );
		}
		else {
			throw new InvalidArgumentException( __METHOD__ . ' only accepts EntityId and integer' );
		}

		// This ensures the id is an instance of the correct derivative of EntityId.
		// EntityId (non-derivative) instances are thus converted.
		$this->id = $this->idFromSerialization( $this->id->getSerialization() );
	}

	/**
	 * Sets the value for the label in a certain value.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string $value
	 *
	 * @return string
	 */
	public function setLabel( $languageCode, $value ) {
		$this->data['label'][$languageCode] = $value;
		return $value;
	}

	/**
	 * Sets the value for the description in a certain value.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string $value
	 *
	 * @return string
	 */
	public function setDescription( $languageCode, $value ) {
		$this->data['description'][$languageCode] = $value;
		return $value;
	}

	/**
	 * Removes the labels in the specified languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string|string[] $languageCodes Note that an empty array removes labels for no languages while a null pointer removes all
	 */
	public function removeLabel( $languageCodes = array() ) {
		$this->removeMultilangTexts( 'label', (array)$languageCodes );
	}

	/**
	 * Removes the descriptions in the specified languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string|string[] $languageCodes Note that an empty array removes descriptions for no languages while a null pointer removes all
	 */
	public function removeDescription( $languageCodes = array() ) {
		$this->removeMultilangTexts( 'description', (array)$languageCodes );
	}

	/**
	 * Remove the value with a field specifier
	 *
	 * @since 0.1
	 *
	 * @param string $fieldKey
	 * @param string[]|null languageCodes
	 */
	protected function removeMultilangTexts( $fieldKey, array $languageCodes = null ) {
		if ( is_null( $languageCodes ) ) {
			$this->data[$fieldKey] = array();
		}
		else {
			foreach ( $languageCodes as $languageCode ) {
				unset( $this->data[$fieldKey][$languageCode] );
			}
		}
	}

	/**
	 * Returns the aliases for the item in the language with the specified code.
	 * TODO: decide on how to deal with duplicates, it is assumed all duplicates should be removed
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string[]
	 */
	public function getAliases( $languageCode ) {
		return array_key_exists( $languageCode, $this->data['aliases'] ) ?
			array_unique( $this->data['aliases'][$languageCode] ) : array();
	}

	/**
	 * Returns all the aliases for the item.
	 * The result is an array with language codes pointing to an array of aliases in the language they specify.
	 * TODO: decide on how to deal with duplicates, it is assumed all duplicates should be removed
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[]|null $languageCodes
	 *
	 * @return string[]
	 */
	public function getAllAliases( array $languageCodes = null ) {
		$textList = $this->data['aliases'];

		if ( !is_null( $languageCodes ) ) {
			$textList = array_intersect_key( $textList, array_flip( $languageCodes ) );
		}

		$textList = array_map(
			'array_unique',
			$textList
		);

		return $textList;
	}

	/**
	 * Sets the aliases for the item in the language with the specified code.
	 * TODO: decide on how to deal with duplicates, it is assumed all duplicates should be removed
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function setAliases( $languageCode, array $aliases ) {
		$aliases = array_diff( $aliases, array( '' ) );
		$aliases = array_values( array_unique( $aliases ) );
		if( count( $aliases ) > 0 ) {
			$this->data['aliases'][$languageCode] = $aliases;
		} else {
			unset( $this->data['aliases'][$languageCode] );
		}
	}

	/**
	 * Add the provided aliases to the aliases list of the item in the language with the specified code.
	 * TODO: decide on how to deal with duplicates, it is assumed all duplicates should be removed
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function addAliases( $languageCode, array $aliases ) {
		$this->setAliases(
			$languageCode,
			array_unique( array_merge(
				$this->getAliases( $languageCode ),
				$aliases
			) )
		);
	}

	/**
	 * Removed the provided aliases from the aliases list of the item in the language with the specified code.
	 * TODO: decide on how to deal with duplicates, it is assumed all duplicates should be removed
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function removeAliases( $languageCode, array $aliases ) {
		$this->setAliases(
			$languageCode,
			array_diff(
				$this->getAliases( $languageCode ),
				$aliases
			)
		);
	}

	/**
	 * Returns the descriptions of the entity in the provided languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[]|null $languageCodes Note that an empty array gives descriptions for no languages while a null pointer gives all
	 *
	 * @return string[] Found descriptions in given languages
	 */
	public function getDescriptions( array $languageCodes = null ) {
		return $this->getMultilangTexts( 'description', $languageCodes );
	}

	/**
	 * Returns the labels of the entity in the provided languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[]|null $languageCodes Note that an empty array gives labels for no languages while a null pointer gives all
	 *
	 * @return string[] Found labels in given languages
	 */
	public function getLabels( array $languageCodes = null ) {
		return $this->getMultilangTexts( 'label', $languageCodes );
	}

	/**
	 * Returns the description of the entity in the language with the provided code,
	 * or false in cases there is none in this language.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string|bool
	 */
	public function getDescription( $languageCode ) {
		return array_key_exists( $languageCode, $this->data['description'] )
			? $this->data['description'][$languageCode] : false;
	}

	/**
	 * Returns the label of the entity in the language with the provided code,
	 * or false in cases there is none in this language.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string|bool
	 */
	public function getLabel( $languageCode ) {
		return array_key_exists( $languageCode, $this->data['label'] )
			? $this->data['label'][$languageCode] : false;
	}

	/**
	 * Get texts from an item with a field specifier.
	 *
	 * @since 0.1
	 *
	 * @param string $fieldKey
	 * @param string[]|null $languageCodes
	 *
	 * @return string[]
	 */
	protected function getMultilangTexts( $fieldKey, array $languageCodes = null ) {
		$textList = $this->data[$fieldKey];

		if ( !is_null( $languageCodes ) ) {
			$textList = array_intersect_key( $textList, array_flip( $languageCodes ) );
		}

		return $textList;
	}

	/**
	 * Cleans the internal array structure.
	 * This consists of adding elements the code expects to be present later on
	 * and migrating or removing elements after changes to the structure are made.
	 * Should typically be called before using any of the other methods.
	 *
	 * @param bool $wipeExisting Unconditionally wipe out all data
	 *
	 * @since 0.1
	 */
	protected function cleanStructure( $wipeExisting = false ) {
		foreach ( array( 'label', 'description', 'aliases', 'claims' ) as $field ) {
			if ( $wipeExisting || !array_key_exists( $field, $this->data ) ) {
				$this->data[$field] = array();
			}
		}
	}

	/**
	 * Replaces the currently set labels with the provided ones.
	 * The labels are provided as an associative array where the keys are
	 * language codes pointing to the label in that language.
	 *
	 * @since 0.4
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[] $labels
	 */
	public function setLabels( array $labels ) {
		$this->data['label'] = $labels;
	}

	/**
	 * Replaces the currently set descriptions with the provided ones.
	 * The descriptions are provided as an associative array where the keys are
	 * language codes pointing to the description in that language.
	 *
	 * @since 0.4
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[] $descriptions
	 */
	public function setDescriptions( array $descriptions ) {
		$this->data['description'] = $descriptions;
	}

	/**
	 * Replaces the currently set aliases with the provided ones.
	 * The aliases are provided as an associative array where the keys are
	 * language codes pointing to an array value that holds the aliases
	 * in that language.
	 *
	 * @since 0.4
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param array[] $aliasLists
	 */
	public function setAllAliases( array $aliasLists ) {
		$this->data['aliases'] = array();
		foreach( $aliasLists as $languageCode => $aliasList ){
			$this->setAliases( $languageCode, $aliasList );
		}
	}

	/**
	 * TODO: change to take Claim[]
	 *
	 * @since 0.4
	 *
	 * @param Claims $claims
	 */
	public function setClaims( Claims $claims ) {
		$this->claims = iterator_to_array( $claims );
	}

	/**
	 * Clears the structure.
	 *
	 * @since 0.1
	 */
	public function clear() {
		$this->cleanStructure( true );
	}

	/**
	 * Returns if the entity is empty.
	 *
	 * @since 0.1
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		$fields = array( 'label', 'description', 'aliases' );

		foreach ( $fields as $field ) {
			if ( $this->data[$field] !== array() ) {
				return false;
			}
		}

		if ( $this->hasClaims() ) {
			return false;
		}

		return true;
	}

	/**
	 * @see Comparable::equals
	 *
	 * Two entities are considered equal if they are of the same
	 * type and have the same value. The value does not include
	 * the id, so entities with the same value but different id
	 * are considered equal.
	 *
	 * @since 0.1
	 *
	 * @param mixed $that
	 *
	 * @return boolean
	 */
	public function equals( $that ) {
		if ( $that === $this ) {
			return true;
		}

		if ( !is_object( $that ) || ( get_class( $this ) !== get_class( $that ) ) ) {
			return false;
		}

		//@todo: ignore the order of aliases
		$thisData = $this->toArray();
		$thatData = $that->toArray();

		$comparer = new ObjectComparer();
		$equals = $comparer->dataEquals( $thisData, $thatData, array( 'entity' ) );

		return $equals;
	}

	/**
	 * Returns a deep copy of the entity.
	 *
	 * @since 0.1
	 *
	 * @return Entity
	 */
	public function copy() {
		$array = array();

		foreach ( $this->toArray() as $key => $value ) {
			$array[$key] = is_object( $value ) ? clone $value : $value;
		}

		$copy = new static( $array );

		return $copy;
	}

	/**
	 * Stubs the entity as far as possible.
	 * This is useful when one wants to conserve memory.
	 *
	 * @since 0.2
	 */
	public function stub() {
		if ( is_null( $this->getId() ) ) {
			if ( array_key_exists( 'entity', $this->data ) ) {
				unset( $this->data['entity'] );
			}
		}
		else {
			$this->data['entity'] = $this->getStubbedId();
		}

		$this->data['claims'] = $this->getStubbedClaims( empty( $this->data['claims'] ) ? array() : $this->data['claims'] );
		$this->claims = null;
	}

	private function getStubbedId() {
		$id = $this->getId();

		if ( $id === null ) {
			return $id;
		}
		else {
			// FIXME: this only works for Item and Property
			/** @var ItemId|PropertyId $id */
			return array( $id->getEntityType(), $id->getNumericId() );
		}
	}

	/**
	 * @see ClaimListAccess::addClaim
	 *
	 * @since 0.3
	 *
	 * @param Claim $claim
	 *
	 * @throws InvalidArgumentException
	 */
	public function addClaim( Claim $claim ) {
		if ( $claim->getGuid() === null ) {
			throw new InvalidArgumentException( 'Can\'t add a Claim without a GUID.' );
		}

		// TODO: ensure guid is valid for entity

		$this->unstubClaims();
		$this->claims[] = $claim;
	}

	/**
	 * @see ClaimAggregate::getClaims
	 *
	 * @since 0.3
	 *
	 * @return Claim[]
	 */
	public function getClaims() {
		$this->unstubClaims();
		return $this->claims;
	}

	/**
	 * Unsturbs the statements from the JSON into the $statements field
	 * if this field is not already set.
	 *
	 * @since 0.3
	 *
	 * @return Claims
	 */
	protected function unstubClaims() {
		if ( $this->claims === null ) {
			$this->claims = array();

			foreach ( $this->data['claims'] as $claimSerialization ) {
				$this->claims[] = Claim::newFromArray( $claimSerialization );
			}
		}
	}

	/**
	 * Takes the claims element of the $data array of an item and writes the claims to it as stubs.
	 *
	 * @since 0.3
	 *
	 * @param Claim[] $claims
	 *
	 * @return Claim[]
	 */
	protected function getStubbedClaims( array $claims ) {
		if ( $this->claims !== null ) {
			$claims = array();

			/**
			 * @var Claim $claim
			 */
			foreach ( $this->claims as $claim ) {
				$claims[] = $claim->toArray();
			}
		}

		return $claims;
	}

	/**
	 * Convenience function to check if the entity contains any claims.
	 *
	 * On top of being a convenience function, this implementation allows for doing
	 * the check without forcing an unstub in contrast to count( $this->getClaims() ).
	 *
	 * @since 0.2
	 *
	 * @return bool
	 */
	public function hasClaims() {
		if ( $this->claims === null ) {
			return $this->data['claims'] !== array();
		}
		else {
			return count( $this->claims ) > 0;
		}
	}

	/**
	 * @since 0.3
	 *
	 * @param Snak $mainSnak
	 *
	 * @return Claim
	 */
	public function newClaim( Snak $mainSnak ) {
		return new Claim( $mainSnak );
	}

	/**
	 * Returns an EntityDiff between $this and the provided Entity.
	 *
	 * @since 0.1
	 *
	 * @param Entity $target
	 * @param Differ|null $differ Since 0.4
	 *
	 * @return EntityDiff
	 * @throws InvalidArgumentException
	 */
	public final function getDiff( Entity $target, Differ $differ = null ) {
		if ( $this->getType() !== $target->getType() ) {
			throw new InvalidArgumentException( 'Can only diff between entities of the same type' );
		}

		if ( $differ === null ) {
			$differ = new MapDiffer( true );
		}

		$oldEntity = $this->entityToDiffArray( $this );
		$newEntity = $this->entityToDiffArray( $target );

		$diffOps = $differ->doDiff( $oldEntity, $newEntity );

		$claims = new Claims( $this->getClaims() );
		$diffOps['claim'] = $claims->getDiff( new Claims( $target->getClaims() ) );

		return EntityDiff::newForType( $this->getType(), $diffOps );
	}

	/**
	 * Create and returns an array based serialization suitable for EntityDiff.
	 *
	 * @since 0.4
	 *
	 * @param Entity $entity
	 *
	 * @return array[]
	 */
	protected function entityToDiffArray( Entity $entity ) {
		$array = array();

		$array['aliases'] = $entity->getAllAliases();
		$array['label'] = $entity->getLabels();
		$array['description'] = $entity->getDescriptions();

		return $array;
	}

	/**
	 * Apply an EntityDiff to the entity.
	 *
	 * @since 0.4
	 *
	 * @param EntityDiff $patch
	 */
	public final function patch( EntityDiff $patch ) {
		$patcher = new MapPatcher();

		$this->setLabels( $patcher->patch( $this->getLabels(), $patch->getLabelsDiff() ) );
		$this->setDescriptions( $patcher->patch( $this->getDescriptions(), $patch->getDescriptionsDiff() ) );
		$this->setAllAliases( $patcher->patch( $this->getAllAliases(), $patch->getAliasesDiff() ) );

		$this->patchSpecificFields( $patch, $patcher );

		$patcher->setValueComparer( new CallbackComparer(
			function( Claim $firstClaim, Claim $secondClaim ) {
				return $firstClaim->getHash() === $secondClaim->getHash();
			}
		) );

		$claims = array();

		foreach ( $this->getClaims() as $claim ) {
			$claims[$claim->getGuid()] = $claim;
		}

		$claims = $patcher->patch( $claims, $patch->getClaimsDiff() );

		$this->setClaims( new Claims( $claims ) );
	}

	/**
	 * Patch fields specific to the type of entity.
	 * @see patch
	 *
	 * @since 0.4
	 *
	 * @param EntityDiff $patch
	 * @param Patcher $patcher
	 */
	protected function patchSpecificFields( EntityDiff $patch, Patcher $patcher ) {
		// No-op, meant to be overridden in deriving classes to add specific behavior
	}

	/**
	 * Parses the claim GUID and returns the prefixed entity ID it contains.
	 *
	 * @since 0.3
	 * @deprecated since 0.4
	 *
	 * @param string $claimKey
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public static function getIdFromClaimGuid( $claimKey ) {
		$keyParts = explode( '$', $claimKey );

		if ( count( $keyParts ) !== 2 ) {
			throw new InvalidArgumentException( 'A claim key should have a single $ in it' );
		}

		return $keyParts[0];
	}

	/**
	 * Returns a list of all Snaks on this Entity. This includes at least the main snaks of
	 * Claims, the snaks from Claim qualifiers, and the snaks from Statement References.
	 *
	 * This is a convenience method for use in code that needs to operate on all snaks, e.g.
	 * to find all referenced Entities.
	 *
	 * @return Snak[]
	 */
	public function getAllSnaks() {
		$claims = $this->getClaims();
		$snaks = array();

		foreach ( $claims as $claim ) {
			$snaks = array_merge( $snaks, $claim->getAllSnaks() );
		}

		return $snaks;
	}

	/**
	 * @since 0.7.3
	 *
	 * @return Fingerprint
	 */
	public function getFingerprint() {
		return new Fingerprint(
			$this->getLabelList(),
			$this->getDescriptionList(),
			$this->getAliasGroupList()
		);
	}

	private function getLabelList() {
		$labels = array();

		foreach ( $this->getLabels() as $languageCode => $label ) {
			$labels[] = new Term( $languageCode, $label );
		}

		return new TermList( $labels );
	}

	private function getDescriptionList() {
		$descriptions = array();

		foreach ( $this->getDescriptions() as $languageCode => $description ) {
			$descriptions[] = new Term( $languageCode, $description );
		}

		return new TermList( $descriptions );
	}

	private function getAliasGroupList() {
		$groups = array();

		foreach ( $this->getAllAliases() as $languageCode => $aliases ) {
			$groups[] = new OrderedTermSet( $languageCode, $aliases );
		}

		return new AliasGroupList( $groups );
	}

	/**
	 * @since 0.7.3
	 *
	 * @param Fingerprint $fingerprint
	 */
	public function setFingerprint( Fingerprint $fingerprint ) {
		$this->setLabels( $fingerprint->getLabels()->toTextArray() );
		$this->setDescriptions( $fingerprint->getDescriptions()->toTextArray() );
		$this->setAliasGroupList( $fingerprint->getAliases() );

	}

	private function setAliasGroupList( AliasGroupList $list ) {
		$allAliases = array();

		/**
		 * @var OrderedTermSet $aliasGroup
		 */
		foreach ( $list as $aliasGroup ) {
			$allAliases[$aliasGroup->getLanguageCode()] = $aliasGroup->getTermTexts();
		}

		$this->setAllAliases( $allAliases );
	}

}
