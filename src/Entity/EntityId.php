<?php

namespace Wikibase\DataModel\Entity;

use Comparable;
use Serializable;

/**
 * @since 0.5
 * Constructor non-public since 1.0
 * Abstract since 2.0
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EntityId implements Comparable, Serializable {

	protected $serialization;
	protected $repositoryName;

	/**
	 * @return string
	 */
	public abstract function getEntityType();

	/**
	 * @return string
	 */
	public function getSerialization() {
		if ( $this->isForeign() ) {
			return $this->repositoryName . ':' . $this->serialization;
		}

		return $this->serialization;
	}

	/**
	 * Returns the serialization without the first repository prefix.
	 *
	 * @return string
	 */
	public function getLocalPart() {
		return $this->serialization;
	}

	/**
	 * Returns '' for local IDs and the foreign repository name for foreign IDs. For chained IDs (e.g. foo:bar:Q42) it
	 * will return only the first part.
	 *
	 * @return string
	 */
	public function getRepoName() {
		return $this->repositoryName;
	}

	/**
	 * @return bool
	 */
	public function isForeign() {
		return $this->repositoryName !== '';
	}

	/**
	 * This is a human readable representation of the EntityId.
	 * This format is allowed to change and should therefore not
	 * be relied upon to be stable.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getSerialization();
	}

	/**
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
			&& $target->serialization === $this->serialization
			&& $target->repositoryName === $this->repositoryName;
	}

}
