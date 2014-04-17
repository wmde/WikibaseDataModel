<?php

namespace Wikibase\DataModel\Snak;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * Interface for objects that represent a single Wikibase snak.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Snaks
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface Snak extends \Serializable, \Hashable, \Immutable, \Comparable {

	/**
	 * Returns the id of the snaks property.
	 *
	 * @since 0.2
	 *
	 * @return PropertyId
	 */
	public function getPropertyId();

	/**
	 * Returns a string that can be used to identify the type of snak.
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	public function getType();

}
