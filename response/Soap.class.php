<?php
namespace YoudsFramework\Response;
use YoudsFramework\Response as IResponse;
use YoudsFramework\Response;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * SoapResponse handles SOAP Web Service responses using the PHP SOAP ext.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage response
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Soap extends Response implements IResponse
{
	/**
	 * @var        mixed The content to send back with this response.
	 */
	protected $content = null;
	
	/**
	 * @var        array An array of SOAP headers to send with the response.
	 */
	protected $soapHeaders = array();
	
	/**
	 * Import response metadata (SOAP headers) from another response.
	 *
	 * @param      Response The other response to import information from.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function merge(Response $otherResponse)
	{
		parent::merge($otherResponse);
		
		if($otherResponse instanceof SoapResponse) {
			foreach($otherResponse->getSoapHeaders() as $soapHeader) {
				if(!$this->hasSoapHeader($soapHeader->namespace, $soapHeader->name)) {
					$this->addSoapHeader($soapHeader);
				}
			}
		}
	}
	
	/**
	 * Redirect externally. Not implemented here.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @throws     Exceptions\BadMethodCall
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setRedirect($to)
	{
		throw new BadMethodCall('Redirects are not implemented for SOAP.');
	}
	
	/**
	 * Get info about the set redirect. Not implemented here.
	 *
	 * @return     array An assoc array of redirect info, or null if none set.
	 *
	 * @throws     Exceptions\BadMethodCall
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getRedirect()
	{
		throw new BadMethodCall('Redirects are not implemented for SOAP.');
	}

	/**
	 * Check if a redirect is set. Not implemented here.
	 *
	 * @return     bool true, if a redirect is set, otherwise false
	 *
	 * @throws     Exceptions\BadMethodCall
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasRedirect()
	{
		throw new BadMethodCall('Redirects are not implemented for SOAP.');
	}

	/**
	 * Clear any set redirect information. Not implemented here.
	 *
	 * @throws     Exceptions\BadMethodCall
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearRedirect()
	{
		throw new BadMethodCall('Redirects are not implemented for SOAP.');
	}
	
	/**
	 * @see        Response::isMutable()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function isContentMutable()
	{
		return false;
	}
	
	/**
	 * Clear the content for this Response
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearContent()
	{
		$this->content = null;
		return true;
	}
	
	/**
	 * Send all response data to the client.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function send(?OutputType $outputType = null)
	{
		$this->sendSoapHeaders();
		// don't send content, that's done by returning it from Controller::dispatch(), so SoapServer::handle() deals with the rest
		// $this->sendContent();
	}
	
	/**
	 * Clear all response data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clear()
	{
		$this->clearContent();
		$this->clearSoapHeaders();
	}
	
	/**
	 * Clear all SOAP headers from the response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearSoapHeaders()
	{
		$this->soapHeaders = array();
	}
	
	/**
	 * Send SOAP Headers.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function sendSoapHeaders()
	{
		$server = $this->context->getController()->getSoapServer();
		
		foreach($this->soapHeaders as $soapHeader) {
			$server->addSoapHeader($soapHeader);
		}
	}
	
	/**
	 * Get an array of all SOAP headers set on this response.
	 *
	 * @return     array An array of SoapHeader objects.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getSoapHeaders()
	{
		return $this->soapHeaders;
	}
	
	/**
	 * Get a SOAP Header from this response based on its namespace and name.
	 *
	 * @param      string The namespace of the SOAP header element.
	 * @param      string The name of the SOAP header element.
	 *
	 * @return     SoapHeader A SoapHeader, if found, otherwise null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getSoapHeader($namespace, $name)
	{
		if(($key = $this->searchSoapHeader($namespace, $name)) !== false) {
			return $this->soapHeaders[$key];
		}
	}
	
	/**
	 * Add a SOAP Header to this response.
	 *
	 * @param      SoapHeader The SOAP header to set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function addSoapHeader(SoapHeader $soapHeader)
	{
		$this->removeSoapHeader($soapHeader->namespace, $soapHeader->name);
		$this->soapHeaders[] = $soapHeader;
	}
	
	/**
	 * Set a SOAP header into this response.
	 *
	 * This method has the same signature as PHP's SoapHeader->__construct().
	 *
	 * @param      string The namespace of the SOAP header element.
	 * @param      string The name of the SOAP header element.
	 * @param      mixed  A SOAP header's content. It can be a PHP value or a
	 *                    SoapVar object.
	 * @param      bool   Value of the mustUnderstand attribute of the SOAP header
	 *                    element.
	 * @param      mixed  Value of the actor attribute of the SOAP header element.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setSoapHeader($namespace, $name, $data = null, $mustUnderstand = false, $actor = null)
	{
		if($actor === null) {
			$h = new SoapHeader($namespace, $name, $data, $mustUnderstand);
		} else {
			$h = new SoapHeader($namespace, $name, $data, $mustUnderstand, $actor);
		}
		$this->addSoapHeader($h);
	}
	
	/**
	 * Remove a SOAP Header from this response based on its namespace and name.
	 *
	 * @param      string The namespace of the SOAP header element.
	 * @param      string The name of the SOAP header element.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function removeSoapHeader($namespace, $name)
	{
		if(($key = $this->searchSoapHeader($namespace, $name)) !== false) {
			unset($this->soapHeaders[$key]);
		}
	}
	
	/**
	 * Check if a SOAP Header has been set based on its namespace and name.
	 *
	 * @param      string The namespace of the SOAP header element.
	 * @param      string The name of the SOAP header element.
	 *
	 * @return     bool true, if this SOAP header has been set, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasSoapHeader($namespace, $name)
	{
		return $this->searchSoapHeader($namespace, $name) !== false;
	}
	
	/**
	 * Find the key of a SOAP Header based on its namespace and name.
	 *
	 * @param      string The namespace of the SOAP header element.
	 * @param      string The name of the SOAP header element.
	 *
	 * @return     int The key of the SOAP header in the array, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function searchSoapHeader($namespace, $name)
	{
		foreach($this->soapHeaders as $key => $soapHeader) {
			if($soapHeader->namespace == $namespace && $soapHeader->name == $name) {
				return $key;
			}
		}
		return false;
	}
}

?>
