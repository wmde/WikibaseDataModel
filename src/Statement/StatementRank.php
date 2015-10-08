<?php

namespace Wikibase\DataModel\Statement;

use InvalidArgumentException;

/**
 * Everything we know about statement's ranks and what they are supposed to mean.
 *
 * @see https://www.mediawiki.org/wiki/Wikibase/DataModel#Ranks_of_Statements
 * @see https://meta.wikimedia.org/wiki/Wikidata/Data_model_update#Ranks_and_order
 *
 * @since 4.4
 *
 * @license GNU GPL v2+
 * @author Thiemo Kreuz
 */
class StatementRank {

	/**
	 * Higher values are more preferred.
	 * TODO: Link to discussion/documentation that guarantees increasing order.
	 */
	const DEPRECATED = 0;
	const NORMAL = 1;
	const PREFERRED = 2;

	private static $names = [
		self::DEPRECATED => 'deprecated',
		self::NORMAL => 'normal',
		self::PREFERRED => 'preferred',
	];

	/**
	 * @return string[] Array mapping all known self::... constants (integers) to string names.
	 */
	public static function getNames() {
		return self::$names;
	}

	/**
	 * @return int[] Array mapping string names to all known self::... constants (integers).
	 */
	public static function getAllRanks() {
		return array_flip( self::$names );
	}

	/**
	 * @param int $rank
	 *
	 * @throws InvalidArgumentException
	 */
	public static function assertIsValid( $rank ) {
		if ( !self::isValid( $rank ) ) {
			throw new InvalidArgumentException( 'Invalid rank' );
		}
	}

	/**
	 * @param int $rank
	 *
	 * @return bool
	 */
	public static function isValid( $rank ) {
		return is_int( $rank ) && array_key_exists( $rank, self::$names );
	}

	/**
	 * @param int $rank
	 *
	 * @throws InvalidArgumentException
	 * @return bool Statements with a deprecated (or lower) rank are known to be false. But don't be
	 *  fooled, this does not mean higher ranks are known to be true!
	 */
	public static function isFalse( $rank ) {
		self::assertIsValid( $rank );

		return $rank === self::DEPRECATED;
	}

	/**
	 * @param int|null $rank1
	 * @param int|null $rank2
	 *
	 * @throws InvalidArgumentException
	 * @return bool True if the given ranks are equal.
	 */
	public static function isEqual( $rank1, $rank2 ) {
		return self::compare( $rank1, $rank2 ) === 0;
	}

	/**
	 * @param int|null $rank1
	 * @param int|null $rank2
	 *
	 * @throws InvalidArgumentException
	 * @return bool True if the first rank is less preferred than the second.
	 */
	public static function isLower( $rank1, $rank2 ) {
		return self::compare( $rank1, $rank2 ) === -1;
	}

	/**
	 * @param int|null $rank1
	 * @param int|null $rank2
	 *
	 * @throws InvalidArgumentException
	 * @return bool True if the first rank is more preferred than the second.
	 */
	public static function isHigher( $rank1, $rank2 ) {
		return self::compare( $rank1, $rank2 ) === 1;
	}

	/**
	 * @param int|null $rank1
	 * @param int|null $rank2
	 *
	 * @throws InvalidArgumentException
	 * @return int 0 if the ranks are equal, -1 if the first rank is less preferred than the second,
	 *  or +1 if the first rank is more preferred than the second.
	 */
	public static function compare( $rank1, $rank2 ) {
		if ( $rank1 !== null ) {
			self::assertIsValid( $rank1 );
		}
		if ( $rank2 !== null ) {
			self::assertIsValid( $rank2 );
		}

		if ( $rank1 === $rank2 ) {
			return 0;
		} elseif ( $rank1 === null || $rank1 < $rank2 ) {
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * @param int[]|int $ranks
	 * @param int [$rank2,...]
	 *
	 * @return int|null Best rank in the array or list of arguments, or null if none given.
	 */
	public static function findBestRank( $ranks = [] /*...*/ ) {
		if ( !is_array( $ranks ) ) {
			$ranks = func_get_args();
		}

		$best = null;

		foreach ( $ranks as $rank ) {
			if ( self::isHigher( $rank, $best ) ) {
				$best = $rank;

				if ( $best === self::PREFERRED ) {
					break;
				}
			}
		}

		return $best;
	}

}
