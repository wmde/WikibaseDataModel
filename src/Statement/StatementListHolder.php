<?php

namespace Wikibase\DataModel\Statement;

/**
 * Interface for classes that contain a StatementList.
 *
 * @since 3.0
 * @deprecated since 5.1, will be removed in favor of StatementListProvider, which
 *  gives the guarantee to return an object by reference. Changes to that object change the entity.
 *
 * @license GPL-2.0+
 * @author Thiemo Mättig
 */
interface StatementListHolder extends StatementListProvider {

	/**
	 * @param StatementList $statements
	 */
	public function setStatements( StatementList $statements );

}
