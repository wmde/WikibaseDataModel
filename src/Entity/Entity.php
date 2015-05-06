<?php

namespace Wikibase\DataModel\Entity;

use Comparable;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\Diff\EntityDiff;
use Wikibase\DataModel\Entity\Diff\EntityDiffer;
use Wikibase\DataModel\Entity\Diff\EntityPatcher;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;

/**
 * Represents a single Wikibase entity.
 * See https://www.mediawiki.org/wiki/Wikibase/DataModel#Values
 *
 * @deprecated since 1.0 - do not type hint against Entity. See
 * https://lists.wikimedia.org/pipermail/wikidata-tech/2014-June/000489.html
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class Entity implements Comparable, FingerprintProvider, EntityDocument {

	/**
	 * @var EntityId|null
	 */
	protected $id;

	/**
	 * @var Fingerprint
	 */
	protected $fingerprint;

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
	 * Returns a deep copy of the entity.
	 *
	 * @since 0.1
	 *
	 * @return self
	 */
	public function copy() {
		return unserialize( serialize( $this ) );
	}

	/**
	 * @since 0.3
	 * @deprecated since 1.0, use getStatements()->toArray() instead.
	 *
	 * @return Statement[]
	 */
	public function getClaims() {
		return array();
	}

	/**
	 * Returns an EntityDiff between $this and the provided Entity.
	 *
	 * @since 0.1
	 * @deprecated since 1.0 - use EntityDiffer or a more specific differ
	 *
	 * @param Entity $target
	 *
	 * @return EntityDiff
	 * @throws InvalidArgumentException
	 */
	public final function getDiff( Entity $target ) {
		$differ = new EntityDiffer();
		return $differ->diffEntities( $this, $target );
	}

	/**
	 * Apply an EntityDiff to the entity.
	 *
	 * @since 0.4
	 * @deprecated since 1.1 - use EntityPatcher or a more specific patcher
	 *
	 * @param EntityDiff $patch
	 */
	public final function patch( EntityDiff $patch ) {
		$patcher = new EntityPatcher();
		$patcher->patchEntity( $this, $patch );
	}

	/**
	 * @since 0.7.3
	 *
	 * @return Fingerprint
	 */
	public function getFingerprint() {
		return $this->fingerprint;
	}

	/**
	 * @since 0.7.3
	 *
	 * @param Fingerprint $fingerprint
	 */
	public function setFingerprint( Fingerprint $fingerprint ) {
		$this->fingerprint = $fingerprint;
	}

	/**
	 * Returns if the Entity has no content.
	 * Having an id set does not count as having content.
	 *
	 * @since 0.1
	 *
	 * @return bool
	 */
	public abstract function isEmpty();

	/**
	 * Removes all content from the Entity.
	 * The id is not part of the content.
	 *
	 * @since 0.1
	 */
	public abstract function clear();

}
