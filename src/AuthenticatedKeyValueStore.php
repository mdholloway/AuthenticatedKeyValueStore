<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\AuthenticatedKeyValueStore;

use MediaWiki\MediaWikiServices;
use RuntimeException;

class AuthenticatedKeyValueStore {

	const PERM_READ = 'viewmyprivateinfo';
	const PERM_WRITE = 'editmyprivateinfo';

	/** @var IDatabase */
	private $dbw;

	/** @var IDatabase */
	private $dbr;

	/** @var bool */
	private $useCentralId;

	/**
	 * @param bool $useCentralId whether to refer to the user by central ID in the DB.
	 * If false, uses the local user ID.
	 */
	public function __construct( $useCentralId = false ) {
		$services = MediaWikiServices::getInstance();
		$this->dbw = Utils::getDB( DB_MASTER, $services );
		$this->dbr = Utils::getDB( DB_REPLICA, $services );
		$this->useCentralId = $useCentralId;
	}

	/**
	 * Get value of key for user.
	 * @param User $user user
	 * @param string $key key
	 * @return any value
	 */
	public function get( $user, $key ) {
		$this->checkAuthenticationAndPermission( $user, self::PERM_READ );
		return $this->dbr->selectField(
			'user_key_value',
			'ukv_value',
			[
				'ukv_user' => $this->getId( $user ),
				'ukv_key' => $key
			],
			__METHOD__
		);
	}

	/**
	 * Set value of key for user.
	 * @param User $user user
	 * @param string $key key
	 * @param any $val value
	 * @return bool true if operation completed successfully
	 */
	public function set( $user, $key, $val ) {
		$this->checkAuthenticationAndPermission( $user, self::PERM_WRITE );
		return $this->dbw->upsert(
			'user_key_value',
			[
				'ukv_user' => $this->getId( $user ),
				'ukv_key' => $key,
				'ukv_value' => $val
			],
			[ 'ukv_user', 'ukv_key' ],
			[
				'ukv_user' => $this->getId( $user ),
				'ukv_key' => $key,
				'ukv_value' => $val
			],
			__METHOD__
		);
	}

	/**
	 * Delete key and value for user.
	 * @param User $user user
	 * @param string $key key
	 * @return bool true if the operation completed successfully
	 */
	public function delete( $user, $key ) {
		$this->checkAuthenticationAndPermission( $user, self::PERM_WRITE );
		return $this->dbw->delete(
			'user_key_value',
			[
				'ukv_user' => $this->getId( $user ),
				'ukv_key' => $key
			],
			__METHOD__
		);
	}

	private function getId( $user ) {
		return $this->useCentralId ? Utils::getCentralId( $user ) : $user->getId();
	}

	private function checkAuthenticationAndPermission( $user, $permission ) {
		if ( !( $user->isLoggedIn() && $user->isAllowed( $permission ) ) ) {
			throw new RuntimeException( 'Permission denied.' );
		}
	}

}
