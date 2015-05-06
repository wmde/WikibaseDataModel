<?php

namespace Wikibase\DataModel\Fixtures;

use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityOfUnknownType implements EntityDocument {

	public function getId() {
		return null;
	}

	public function getType() {
		return 'unknown-entity-type';
	}

	public function setId( EntityId $id = null ) {
	}

}
