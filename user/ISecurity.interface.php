<?php
namespace YoudsFramework\User;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * SecurityUser provides advanced security manipulation methods.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage user
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface ISecurity
{
	/**
	 * Add a credential to this user.
	 *
	 * @param      mixed Credential data.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function addCredential($credential);

	/**
	 * Clear all credentials associated with this user.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function clearCredentials();

	/**
	 * Indicates whether or not this user has a credential.
	 *
	 * @param      mixed Credential data.
	 *
	 * @return     bool true, if this user has the credential, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function hasCredentials($credential);

	/**
	 * Indicates whether or not this user is authenticated.
	 *
	 * @return     bool true, if this user is authenticated, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function isAuthenticated();

	/**
	 * Remove a credential from this user.
	 *
	 * @param      mixed Credential data.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function removeCredential($credential);

	/**
	 * Set the authenticated status of this user.
	 *
	 * @param      bool A flag indicating the authenticated status of this user.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function setAuthenticated($authenticated);

}

?>
