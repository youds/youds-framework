<?php
namespace YoudsFramework\Request;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Interface for DataHolders that allow access to Cookies.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface ICookies
{
	public function hasCookie($name);
	
	public function isCookieValueEmpty($name);
	
	public function &getCookie($name, $default = null);
	
	public function &getCookies();
	
	public function getCookieNames();
	
	public function getFlatCookieNames();
	
	public function setCookie($name, $value);
	
	public function setCookies(array $cookies);
	
	public function &removeCookie($name);
	
	public function clearCookies();
	
	public function mergeCookies($other);
}

?>
