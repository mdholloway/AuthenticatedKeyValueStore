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

namespace MediaWiki\Extension\AuthenticatedKeyValueStore\Test;

use MediaWiki\Extension\AuthenticatedKeyValueStore\AuthenticatedKeyValueStore;
use MediaWikiTestCase;
use User;

/**
 * @group Database
 * @covers \MediaWiki\Extension\AuthenticatedKeyValueStore\AuthenticatedKeyValueStore
 */
class AuthenticatedKeyValueStoreTest extends MediaWikiTestCase {

	/** @var AuthenticatedKeyValueStore */
	private $kv;

	/** @var User */
	private $user;

	/** @var User */
	private $anon;

	public function setUp() {
		parent::setUp();
		$this->tablesUsed = array_merge( $this->tablesUsed, [ 'user_key_value' ] );
		$this->kv = new AuthenticatedKeyValueStore();
		$this->user = $this->getTestUser()->getUser();
		$this->anon = User::newFromId( 0 );
	}

	public function testString() {
		$this->getSetDelete( 'foo' );
	}

	public function testInteger() {
		$this->getSetDelete( 1 );
	}

	public function testBoolean() {
		$this->getSetDelete( true );
	}

	public function testFloat() {
		$this->getSetDelete( 0.5 );
	}

	public function testNull() {
		$this->getSetDelete( null );
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Permission denied.
	 */
	public function testAnonGet() {
		$this->assertEquals( $this->kv->get( $this->anon, 'test' ), null );
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Permission denied.
	 */
	public function testAnonSet() {
		$this->assertTrue( $this->kv->set( $this->anon, 'test', 'foo' ) );
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Permission denied.
	 */
	public function testAnonDelete() {
		$this->assertTrue( $this->kv->delete( $this->anon, 'test' ) );
	}

	private function getSetDelete( $val ) {
		$this->assertEquals( $this->kv->get( $this->user, 'test' ), null );
		$this->kv->set( $this->user, 'test', $val );
		$this->assertEquals( $this->kv->get( $this->user, 'test' ), $val );
		$this->kv->delete( $this->user, 'test' );
		$this->assertEquals( $this->kv->get( $this->user, 'test' ), null );
	}

}
