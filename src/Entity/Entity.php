<?php

namespace Wikibase\DataModel\Entity;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use Diff\Comparer\CallbackComparer;
use Diff\Differ\Differ;
use Diff\Differ\MapDiffer;
use Diff\Patcher\MapPatcher;
use Diff\Patcher\Patcher;
use InvalidArgumentException;
use RuntimeException;
use SebastianBergmann\Exporter\Exception;
use Wikibase\DataModel\Claim\Claim;
use Wikibase\DataModel\Claim\ClaimAggregate;
use Wikibase\DataModel\Claim\Claims;
use Wikibase\DataModel\Internal\LegacyIdInterpreter;
use Wikibase\DataModel\Internal\ObjectComparer;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;
use Wikibase\InternalSerialization\SerializerFactory;

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
	 * @var EntityId|null
	 */
	protected $id;

	/**
	 * @var Fingerprint
	 */
	protected $fingerprint;

	/**
	 * Get an array representing the Entity.
	 * A new Entity can be constructed by passing this array to @see Entity::newFromArray
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->getSerializer()->serialize( $this );
	}

	private function getSerializer() {
		$serializerFactory = new SerializerFactory(
			new DataValueSerializer()
		);

		return $serializerFactory->newEntitySerializer();
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
		$data['v'] = 2;

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

			if ( $this instanceof Item ) {
				return Item::newFromArray( $unserialized );
			}
			else if ( $this instanceof Property ) {
				return Property::newFromArray( $unserialized );
			}
			else {
				throw new RuntimeException(
					'Unserialization of non-Item and non-Property Entities is not supported'
				);
			}
		}

		throw new RuntimeException( 'Invalid serialization passed to Entity unserializer' );
	}

	/**
	 * @since 0.3
	 *
	 * @deprecated Do not rely on this method being present, it will be removed soonish.
	 * // FIXME 8 can we remove this?
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
	 * Returns the id of the entity or null if it does not have one.
	 *
	 * @since 0.1 return type changed in 0.3
	 *
	 * @return EntityId|null
	 */
	public function getId() {
		return $this->id;
	}

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
		$this->fingerprint->getLabels()->setTerm( new Term( $languageCode, $value ) );
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
		$this->fingerprint->getDescriptions()->setTerm( new Term( $languageCode, $value ) );
		return $value;
	}

	/**
	 * Removes the labels in the specified languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 */
	public function removeLabel( $languageCode ) {
		$this->fingerprint->getLabels()->removeByLanguage( $languageCode );
	}

	/**
	 * Removes the descriptions in the specified languages.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 */
	public function removeDescription( $languageCode ) {
		$this->fingerprint->getDescriptions()->removeByLanguage( $languageCode );
	}

	/**
	 * Returns the aliases for the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 *
	 * @return string[]
	 */
	public function getAliases( $languageCode ) {
		$aliases = $this->fingerprint->getAliasGroups();

		if ( $aliases->hasGroupForLanguage( $languageCode ) ) {
			$aliases->getByLanguage( $languageCode )->getAliases();
		}

		return array();
	}

	/**
	 * Returns all the aliases for the item.
	 * The result is an array with language codes pointing to an array of aliases in the language they specify.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string[]|null $languageCodes
	 *
	 * @return array[]
	 */
	public function getAllAliases( array $languageCodes = null ) {
		$aliases = $this->fingerprint->getAliasGroups();

		$textLists = array();

		/**
		 * @var AliasGroup $aliasGroup
		 */
		foreach ( $aliases as $languageCode => $aliasGroup ) {
			if ( $languageCodes === null || in_array( $languageCode, $languageCodes ) ) {
				$textLists[$languageCode] = $aliasGroup->getAliases();
			}
		}

		return $textLists;
	}

	/**
	 * Sets the aliases for the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function setAliases( $languageCode, array $aliases ) {
		$this->fingerprint->getAliasGroups()->setGroup( new AliasGroup( $languageCode, $aliases ) );
	}

	/**
	 * Add the provided aliases to the aliases list of the item in the language with the specified code.
	 *
	 * @deprecated since 0.7.3 - use getFingerprint and setFingerprint
	 *
	 * @param string $languageCode
	 * @param string[] $aliases
	 */
	public function addAliases( $languageCode, array $aliases ) {
		$this->setAliases(
			$languageCode,
			array_merge(
				$this->getAliases( $languageCode ),
				$aliases
			)
		);
	}

	/**
	 * Removed the provided aliases from the aliases list of the item in the language with the specified code.
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
		if ( !$this->fingerprint->getDescriptions()->hasTermForLanguage( $languageCode ) ) {
			return false;
		}

		return $this->fingerprint->getDescriptions()->getByLanguage( $languageCode );
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
		if ( !$this->fingerprint->getLabels()->hasTermForLanguage( $languageCode ) ) {
			return false;
		}

		return $this->fingerprint->getLabels()->getByLanguage( $languageCode );
	}

	/**
	 * Get texts from an item with a field specifier.
	 *
	 * @since 0.1
	 * @deprecated
	 *
	 * @param string $fieldKey
	 * @param string[]|null $languageCodes
	 *
	 * @return string[]
	 */
	private function getMultilangTexts( $fieldKey, array $languageCodes = null ) {
		// FIXME 8
		if (!is_object($this->fingerprint)) {throw new Exception();}

		if ( $fieldKey === 'labels' ) {
			$textList = $this->fingerprint->getLabels()->toTextArray();
		}
		else {
			$textList = $this->fingerprint->getDescriptions()->toTextArray();
		}

		if ( !is_null( $languageCodes ) ) {
			$textList = array_intersect_key( $textList, array_flip( $languageCodes ) );
		}

		return $textList;
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
		foreach ( $labels as $languageCode => $labelText ) {
			$this->setLabel( $languageCode, $labelText );
		}
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
		foreach ( $descriptions as $languageCode => $descriptionText ) {
			$this->setDescription( $languageCode, $descriptionText );
		}
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
		/**
		 * @var AliasGroup $group
		 */
		foreach ( $this->fingerprint->getAliases() as $group ) {
			$this->fingerprint->getAliases()->removeByLanguage( $group->getLanguageCode() );
		}

		foreach( $aliasLists as $languageCode => $aliasList ){
			$this->setAliases( $languageCode, $aliasList );
		}
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

		$copy = static::newFromArray( $array );

		return $copy;
	}

	/**
	 * @see ClaimListAccess::addClaim
	 *
	 * @since 0.3
	 * @deprecated since 0.8
	 *
	 * @param Claim $claim
	 *
	 * @throws InvalidArgumentException
	 */
	public function addClaim( Claim $claim ) {
	}

	/**
	 * @see ClaimAggregate::getClaims
	 *
	 * @since 0.3
	 * @deprecated since 0.8
	 *
	 * @return Claim[]
	 */
	public function getClaims() {
		return array();
	}

	/**
	 * Convenience function to check if the entity contains any claims.
	 *
	 * On top of being a convenience function, this implementation allows for doing
	 * the check without forcing an unstub in contrast to count( $this->getClaims() ).
	 *
	 * @since 0.2
	 * @deprecated since 0.8
	 *
	 * @return bool
	 */
	public function hasClaims() {
		return false;
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
	}

	/**
	 * Patch fields specific to the type of entity.
	 * @see patch
	 *
	 * @since 0.4
	 *
	 * @param EntityDiff $patch
	 * @param MapPatcher $patcher
	 */
	protected function patchSpecificFields( EntityDiff $patch, MapPatcher $patcher ) {
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
			$groups[] = new AliasGroup( $languageCode, $aliases );
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
		$this->setAliasGroupList( $fingerprint->getAliasGroups() );
	}

	private function setAliasGroupList( AliasGroupList $list ) {
		$allAliases = array();

		/**
		 * @var AliasGroup $aliasGroup
		 */
		foreach ( $list as $aliasGroup ) {
			$allAliases[$aliasGroup->getLanguageCode()] = $aliasGroup->getAliases();
		}

		$this->setAllAliases( $allAliases );
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
	 * Returns a type identifier for the entity.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public abstract function getType();

}
