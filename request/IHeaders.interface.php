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
 * Interface for DataHolders that allow access to Headers.
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
interface IHeaders
{
	public function hasHeader($name);
	
	public function isHeaderValueEmpty($name);
	
	public function &getHeader($name, $default = null);
	
	public function &getHeaders();
	
	public function getHeaderNames();
	
	public function setHeader($name, $value);
	
	public function setHeaders(array $headers);
	
	public function &removeHeader($name);
	
	public function clearHeaders();
	
	public function mergeHeaders($other);
}

?>
