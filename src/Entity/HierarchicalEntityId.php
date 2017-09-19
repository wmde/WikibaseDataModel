<?php

namespace Wikibase\DataModel\Entity;

use InvalidArgumentException;
use Serializable;

/**
 * Base class for hierarchical entity IDs. Hierarchical entity IDs are addresses that can be
 * used to refer to entities that are parts of other entities, logically or physically. They
 * consist of the base (parent) EntityId and a relative part. The base entity may itself be
 * hierarchical. Only the first (root) entity ID in a chain may be a foreign ID.
 *
 * Hierarchical entity IDs are serialized as the serialization of the base entity IDs, followed
 * by a dash, followed by the child part of the ID.
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
abstract class HierarchicalEntityId extends EntityId {

	/**
	 * @var EntityId
	 */
	private $base;

	/**
	 * @since 6.2
	 *
	 * @param EntityId $base
	 * @param string $relativePart
	 */
	public function __construct( EntityId $base, $relativePart ) {
		$this->base = $base;
		parent::__construct( $relativePart );

		if ( parent::isForeign() ) {
			throw new InvalidArgumentException(
				'The relative part of a hierarchical entity cannot contain a repo prefix!'
			);
		}
	}

	/**
	 * Splits an ID serialization into the relative part, and the base ID.
	 *
	 * @param $serialization
	 * @return array A two element array of strings, [ $basePart, $relativePart ]
	 */
	protected static function splitHierarchicalSerialization( $serialization ) {
		if ( !preg_match( '/^(.+)[-#]([^-#]+)/', $serialization, $m ) ) {
			throw new InvalidArgumentException(
				'Serialization is not a valid heirarchical ID: ' . $serialization
			);
		}

		$basePart = $m[1];
		$relativePart = $m[2];

		return [
			$basePart,
			$relativePart
		];
	}

	/**
	 * Returns the parent EntityId, that is, the ID of the immediate parent of this ID.
	 *
	 * @return EntityId
	 */
	public function getBaseId() {
		return $this->base;
	}

	/**
	 * Returns the root EntityId, that is, the beginning of the chain of base IDs.
	 *
	 * @return EntityId
	 */
	public function getRootId() {
		$id = $this->getBaseId();
		if ( $id instanceof HierarchicalEntityId ) {
			$id = $id->getRootId();
		};

		return $id;
	}

	/**
	 * @return string
	 */
	public function getRelativePart() {
		return parent::getSerialization();
	}

	/**
	 * @return string
	 */
	public function getSerialization() {
		return $this->getBaseId()->getSerialization() . '-' . $this->getRelativePart();
	}

	/**
	 * @return string
	 */
	public function getRepositoryName() {
		return $this->getBaseId()->getRepositoryName();
	}

	/**
	 * @return string
	 */
	public function getLocalPart() {
		return $this->getBaseId()->getLocalPart() . '-' . $this->getRelativePart();
	}

	/**
	 * @return bool
	 */
	public function isForeign() {
		return $this->getBaseId()->isForeign();
	}

}
