<?php

namespace Wikibase\DataModel\Entity;

use Comparable;
use InvalidArgumentException;
use Serializable;

/**
 * Base class for value objects representing the IDs of entities.
 * Multiple "equivalent" EntityIds may refer to the same Entity without
 * being equal.
 *
 * The EntityId class is abstract, since any concrete entity ID should be
 * represented by a subclass specific to the respective type of entity.
 * This leaves each entity type free to define its own identifier syntax.
 * EntityId objects are tightly bound to their string representation
 * as returned by the getSerialization method.
 *
 * Entity IDs can specify the name of a context (a Wikibase repository) in
 * which they can be resolved. IDs that do not provide a repository name
 * (or, technically, have the repository name set to the empty string, '')
 * are referred to as "local". IDs that do provide a repository name are
 * referred to as "foreign" (even if they, by some detour, are equivalent to
 * a local ID).
 *
 * The intended interpretation of "local" IDs is that they can be resolved
 * in the default context - that is, they belong to the Wikibase repository
 * the EntityId object was instantiated in, or, for Wikibase  clients,
 * the default repo of the client the EntityId object was instantiated in.
 *
 * A repository can be specified in the string representation of an EntityId
 * as a prefix, separated by a colon (":") from the local part of the ID:
 * "<repo>:<local-id>". Multiple such prefixes can be "chained", as in
 * "foo:bar:rudd:X123"; in that case, only the first prefix ("foo") is
 *  considered the repository name, the remaining prefixes are part of the
 * "local part" of the ID ("bar:rudd:X123"), since they are to be resolved
 * "locally" in the context of the context given as the first prefix.
 *
 * For more information on foreign IDs, @see docs/foreign-entity-ids.wiki
 *
 * @since 0.5
 * Abstract since 2.0
 *
 * @license GPL-2.0+
 */
abstract class EntityId implements Comparable, Serializable {

	protected $serialization;

	/**
	 * @since 7.3
	 *
	 * @var string
	 */
	protected $repositoryName;

	/**
	 * @since 7.3
	 *
	 * @var string
	 */
	protected $localPart;

	const PATTERN = '/^:?(\w+:)*[^:]+\z/';

	/**
	 * Constructs an EntityId from the given serialized form.
	 * The serialized form of the ID is specific to the concrete entity type.
	 * Any EntityId can however be preceded by one or more repository names,
	 * separated by a colon. If any such repository names are given, the
	 * EntityId is considered a "foreign" ID; if no repository is given, the
	 * EntityId is considered "local". Local IDs must be resolvable within
	 * the default context of the application that instantiated the EntityId.
	 *
	 * See the class level documentation for more information.
	 *
	 * @since 6.2
	 *
	 * @param string $serialization
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $serialization ) {
		self::assertValidSerialization( $serialization );
		$this->serialization = self::normalizeIdSerialization( $serialization );

		list( $this->repositoryName, $this->localPart ) = self::extractRepositoryNameAndLocalPart( $serialization );
	}

	private static function assertValidSerialization( $serialization ) {
		if ( !is_string( $serialization ) ) {
			throw new InvalidArgumentException( '$serialization must be a string' );
		}

		if ( $serialization === '' ) {
			throw new InvalidArgumentException( '$serialization must not be an empty string' );
		}

		if ( !preg_match( self::PATTERN, $serialization ) ) {
			throw new InvalidArgumentException( '$serialization must match ' . self::PATTERN );
		}
	}

	/**
	 * @return string The type of the designated Entity.
	 */
	abstract public function getEntityType();

	/**
	 * Returns the canonical string representation of the EntityId. This string representation
	 * can be used to represent an Entity in structured data as well as to humans.
	 *
	 * @return string The ID's string representation, as provided to the constructor, including
	 * any repository prefixes.
	 */
	public function getSerialization() {
		return $this->serialization;
	}

	/**
	 * Returns an array with 3 elements: the foreign repository name as the first element, the local ID as the last
	 * element and everything that is in between as the second element.
	 *
	 * EntityId::joinSerialization can be used to restore the original serialization from the parts returned.
	 *
	 * @since 6.2
	 *
	 * @param string $serialization
	 *
	 * @throws InvalidArgumentException
	 * @return string[] Array containing the serialization split into 3 parts.
	 */
	public static function splitSerialization( $serialization ) {
		self::assertValidSerialization( $serialization );

		return self::extractSerializationParts( self::normalizeIdSerialization( $serialization ) );
	}

	/**
	 * Splits the given ID serialization into an array with the following three elements:
	 *  - the repository name as the first element (empty string for local repository)
	 *  - any parts of the ID serialization but the repository name and the local ID (if any, empty string
	 *    if nothing else present)
	 *  - the local ID
	 * Note: this method does not perform any validation of the given input. Calling code should take
	 * care of this!
	 *
	 * @param string $serialization
	 *
	 * @return string[]
	 */
	private static function extractSerializationParts( $serialization ) {
		$parts = explode( ':', $serialization );
		$localPart = array_pop( $parts );
		$repoName = array_shift( $parts );
		$prefixRemainder = implode( ':', $parts );

		return [
			is_string( $repoName ) ? $repoName : '',
			$prefixRemainder,
			$localPart
		];
	}

	/**
	 * Builds an ID serialization from the parts returned by EntityId::splitSerialization.
	 *
	 * @since 6.2
	 *
	 * @param string[] $parts
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	public static function joinSerialization( array $parts ) {
		if ( end( $parts ) === '' ) {
			throw new InvalidArgumentException( 'The last element of $parts must not be empty.' );
		}

		return implode(
			':',
			array_filter( $parts, function( $part ) {
				return $part !== '';
			} )
		);
	}

	/**
	 * Returns the name of the repository which serves as the context for resolving this EntityId.
	 * This method is the complement of getLocalPart().
	 *
	 * For local IDs with no repository name attached, getRespoitoryName() will return the empty
	 * string (''). Such local EntityIds should be interpreted as belonging to the application's
	 * default context: If the application is a Wikibase repository, the ID can be resolved locally
	 * in that repository; if the application is a Wikibase client, the ID can be resolved in the
	 * client's default repository.
	 *
	 * For chained IDs (e.g. foo:bar:Q42) it will return only the first part.
	 *
	 * @see docs/foreign-entity-ids.wiki
	 *
	 * @since 6.2
	 *
	 * @return string
	 */
	public function getRepositoryName() {
		return $this->repositoryName;
	}

	/**
	 * Returns the serialization without the first repository prefix. This method is the complement
	 * of getRepositoryName().
	 *
	 * The intended interpretation of the "local" part of the ID is the ID that can be resolved in
	 * the context of the repository specified by the first prefix.
	 *
	 * In case the ID is a "chained" ID with multiple prefixes, all but the first prefix are
	 * included in the value returned by getLocalPart().
	 *
	 * If isForeign() returned false, getLocalPart() should contain no prefixes. In other words,
	 * for a non-foreign ("local") entity ID, getSerialization() and getLocalPart() are the same.
	 *
	 * @since 6.2
	 *
	 * @return string
	 */
	public function getLocalPart() {
		return $this->localPart;
	}

	/**
	 * Returns true if the EntityId has to be resolved in the context of a Wikibase repository
	 * other than the application's default. This is the case iff EntityId::getRepoName returns
	 * a non-empty string.
	 *
	 * Note that isForeign() returning true does not guarantee that the denoted entity is not
	 * part of the local repository. isForeign() returning true merely reflects the fact that the
	 * EntityId is specified in a way that requires a "foreign" repository in order to be resolved.
	 *
	 * isForeign() returning false indicates that the EntityId can be resolved in the context
	 * of the application that instantiated the EntityId, using that application's default
	 * repository.
	 *
	 * @see docs/foreign-entity-ids.wiki
	 *
	 * @since 6.2
	 *
	 * @return bool
	 */
	public function isForeign() {
		// not actually using EntityId::getRepoName for performance reasons
		return strpos( $this->serialization, ':' ) > 0;
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	private static function normalizeIdSerialization( $id ) {
		return ltrim( $id, ':' );
	}

	/**
	 * This is a human readable representation of the EntityId.
	 * This format is allowed to change and should therefore not
	 * be relied upon to be stable.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->serialization;
	}

	/**
	 * Returns true if and only if the serializations of this and $target are both EntityIds,
	 * and have the exact same serialization.
	 *
	 * Note that two EntityIds can be equivalent (denoting the same Entity) without being equal.
	 *
	 * @see Comparable::equals
	 *
	 * @since 0.5
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $target->serialization === $this->serialization;
	}

	/**
	 * Returns a pair (repository name, local part of ID) from the given ID serialization.
	 * Note: this does not perform any validation of the given input. Calling code should take
	 * care of this!
	 *
	 * @since 7.3
	 *
	 * @param string $serialization
	 *
	 * @return string[] Array of form [ string $repositoryName, string $localPart ]
	 */
	protected static function extractRepositoryNameAndLocalPart( $serialization ) {
		$parts = explode( ':', $serialization, 2 );
		return isset( $parts[1] ) ? $parts : [ '', $parts[0] ];
	}

}
