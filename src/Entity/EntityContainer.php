<?php

namespace Wikibase\DataModel\Entity;

use OutOfBoundsException;

/**
 * Represents a container of EntityDocuments. This interface is typically implemented by
 * entities that contain sub-entities, using a hierarchical addressing scheme.
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
interface EntityContainer {

	/**
	 * Returns the entity with the given ID, if that entity can be found in this container.
	 *
	 * @param EntityId $id
	 *
	 * @throws OutOfBoundsException If the given entity is not known.
	 * @return EntityDocument
	 */
	public function getEntity( EntityId $id );

	/**
	 * Assigns a fresh ID to the given entity.
	 * Implementations that do not support automatic IDs should throw a RuntimeException.
	 *
	 * @param EntityDocument $entity
	 */
	public function assignFreshId( EntityDocument $entity );

	/**
	 * Whether an entity with the given custom ID can be added to this container.
	 * Containers that do not allow custom IDs will always return false from this method.
	 *
	 * FIXME: do we really want/need this?
	 *
	 * @param EntityId $id
	 *
	 * @return bool
	 */
	public function canAddWithCustomId( EntityId $id );

	/**
	 * Puts an entity into this container.
	 *
	 * If $entity does not have an ID yet, implementations may assign a fresh unique ID,
	 * as per assignFreshId().
	 *
	 * If entity already has an ID set, and EntityDocument with the same ID is already
	 * contained, it will be replaced.
	 *
	 * @param EntityDocument $entity
	 */
	public function putEntity( EntityDocument $entity );

	/**
	 * Removes any entity with the given ID from this container.
	 *
	 * @param EntityId $id
	 */
	public function removeEntity( EntityId $id );

}
