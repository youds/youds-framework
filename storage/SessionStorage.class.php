<?php

namespace YoudsFramework;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * SessionStorage is the interface used by Youds Framework to store session data from
 * the User object in a PHP session.
 *
 * Optional parameters:
 *
 * # auto_start              - [true]  - Should session_start() be called
 *                                              automatically?
 * # session_cache_limiter   - []      - The session cache limiter value.
 * # session_cache_expire    - []      - The expire value for the cache
 *                                              limiter header.
 * # session_content_name     - []      - The name of the session content.
 * # session_save_path       - []      - The filesystem location where
 *                                              session data is stored
 * # session_name            - [] - The name of the session.
 * # session_id              - []      - Static session ID value to set.
 * # session_cookie_lifetime - []      - The session cookie lifetime (in
 *                                              seconds, or strtotime() string).
 * # session_cookie_path     - [?????] - Session cookie path (defaults to
 *                                              base href for web requests).
 * # session_cookie_domain   - []      - Session cookie domain.
 * # session_cookie_secure   - []      - Whether or not session cookies
 *                                              should be limited to HTTPS.
 * # session_cookie_httponly - []      - Session cookie "HTTP-only" flag.
 *
 * All parameters default to whatever PHP would otherwise use, i.e. what's set
 * in php.ini, .htaccess or elsewhere (see {@link http://www.php.net/session}).
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class SessionStorage extends Storage
{
	/**
	 * Starts the session.
	 * The method must be called after initialize().
	 * This code cannot be run in initialize(), because initialization has to
	 * finish completely, for all instances, before a session can be created:
	 * A Database Session Storage must initialize the parent, then itself, and
	 * may only then call startup() to auto-start the session.
	 * Also, the routing must be fully initialized, too.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
	public function startup()
	{
		if($this->hasParameter('session_cache_expire')) {
			session_cache_expire($this->getParameter('session_cache_expire'));
		}

		if($this->hasParameter('session_cache_limiter')) {
			session_cache_limiter($this->getParameter('session_cache_limiter'));
		}

		if($this->hasParameter('session_content_name')) {
			session_content_name($this->getParameter('session_content_name'));
		}

		if($this->hasParameter('session_save_path')) {
			session_save_path($this->getParameter('session_save_path'));
		}

		//session_name($sessionName = $this->getParameter('session_name', '')?$sessionName:uniqid());


		$sessionId = session_id();
		$staticSessionId = $this->getParameter('session_id');
		if($sessionId === '' || ($staticSessionId && $sessionId !== $staticSessionId)) {
			if($staticSessionId) {
				session_id($staticSessionId);
			}

			$cookieDefaults = session_get_cookie_params();

			$routing = $this->context->getRouting();
			if($routing instanceof Web) {
				// set path to true if the default path from php.ini is "/". this will, in startup(), trigger the base href as the path.
				if($cookieDefaults['path'] == '/') {
					$cookieDefaults['path'] = true;
				}
			}

			$lifetime = $this->getParameter('session_cookie_lifetime', $cookieDefaults['lifetime']);
			if(is_numeric($lifetime)) {
				$lifetime = (int) $lifetime;
			} else {
				$lifetime = strtotime($lifetime, 0);
			}
			$path = $this->getParameter('session_cookie_path', $cookieDefaults['path']);
			if($path === true) {
				$path = $this->context->getRouting()->getBasePath();
			}
			$domain = $this->getParameter('session_cookie_domain', $cookieDefaults['domain']);

			$secure = $this->getParameter('session_cookie_secure', $cookieDefaults['secure']);
			$request = $this->context->getRequest();
			if($secure === null && $request instanceof WebRequest) {
				$secure = $request->isHttps();
			} else {
				$secure = (bool) $secure;
			}

			$httpOnly = (bool) $this->getParameter('session_cookie_httponly', $cookieDefaults['httponly']);

            // check for session integrity
            if (!headers_sent()):
                session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
				}
            endif;

			if($lifetime !== 0) {
				setcookie(session_name(), session_id(), time() + $lifetime, $path, $domain, $secure, $httpOnly);
			}
		}
	}

	/**
	 * Read data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     mixed Data associated with the key.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function read($key)
	{
		if(isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return null;
	}

	/**
	 * Remove data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     mixed Data associated with the key.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function remove($key)
	{
		$retVal = null;

		if(isset($_SESSION[$key])) {
			$retVal = $_SESSION[$key];
			unset($_SESSION[$key]);
		}

		return $retVal;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function shutdown()
	{
		session_write_close();
	}

	/**
	 * Write data to this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 * @param      mixed  Data associated with your key.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function write($key, $data)
	{
		$_SESSION[$key] = $data;
		
	}
}

?>
