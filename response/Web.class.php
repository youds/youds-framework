<?php
namespace YoudsFramework\Response;
use YoudsFramework\Context;
use YoudsFramework\Config;
use YoudsFramework\Response\Response as IResponse;
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
 * Response\Web handles HTTP responses.
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
class Web extends Response implements IResponse
{
	/**
	 * @var        array An array of all HTTP 1.0 status codes and their message.
	 */
	protected $http10StatusCodes = array(
		'200' => "HTTP/1.0 200 OK",
		'201' => "HTTP/1.0 201 Created",
		'202' => "HTTP/1.0 202 Accepted",
		'204' => "HTTP/1.0 204 No Content",
		'205' => "HTTP/1.0 205 Reset Content",
		'206' => "HTTP/1.0 206 Partial Content",
		'300' => "HTTP/1.0 300 Multiple Choices",
		'301' => "HTTP/1.0 301 Moved Permanently",
		'302' => "HTTP/1.0 302 Found",
		'304' => "HTTP/1.0 304 Not Modified",
		'400' => "HTTP/1.0 400 Bad Request",
		'401' => "HTTP/1.0 401 Unauthorized",
		'402' => "HTTP/1.0 402 Payment Required",
		'403' => "HTTP/1.0 403 Forbidden",
		'404' => "HTTP/1.0 404 Not Found",
		'405' => "HTTP/1.0 405 Method Not Allowed",
		'406' => "HTTP/1.0 406 Not Acceptable",
		'407' => "HTTP/1.0 407 Proxy Authentication Required",
		'408' => "HTTP/1.0 408 Request Timeout",
		'409' => "HTTP/1.0 409 Conflict",
		'410' => "HTTP/1.0 410 Gone",
		'411' => "HTTP/1.0 411 Length Required",
		'412' => "HTTP/1.0 412 Precondition Failed",
		'413' => "HTTP/1.0 413 Request Entity Too Large",
		'414' => "HTTP/1.0 414 Request-URI Too Long",
		'415' => "HTTP/1.0 415 Unsupported Media Type",
		'416' => "HTTP/1.0 416 Requested Range Not Satisfiable",
		'417' => "HTTP/1.0 417 Expectation Failed",
		'500' => "HTTP/1.0 500 Internal Server Error",
		'501' => "HTTP/1.0 501 Not Implemented",
		'502' => "HTTP/1.0 502 Bad Gateway",
		'503' => "HTTP/1.0 503 Service Unavailable",
		'504' => "HTTP/1.0 504 Gateway Timeout",
		'505' => "HTTP/1.0 505 HTTP Version Not Supported",
	);
	
	/**
	 * @var        array An array of all HTTP 1.1 status codes and their message.
	 */
	protected $http11StatusCodes = array(
		'100' => "HTTP 100 Continue",
		'101' => "HTTP 101 Switching Protocols",
		'200' => "HTTP 200 OK",
		'201' => "HTTP 201 Created",
		'202' => "HTTP 202 Accepted",
		'203' => "HTTP 203 Non-Authoritative Information",
		'204' => "HTTP 204 No Content",
		'205' => "HTTP 205 Reset Content",
		'206' => "HTTP 206 Partial Content",
		'300' => "HTTP 300 Multiple Choices",
		'301' => "HTTP 301 Moved Permanently",
		'302' => "HTTP 302 Found",
		'303' => "HTTP 303 See Other",
		'304' => "HTTP 304 Not Modified",
		'305' => "HTTP 305 Use Proxy",
		'307' => "HTTP 307 Temporary Redirect",
		'400' => "HTTP 400 Bad Request",
		'401' => "HTTP 401 Unauthorized",
		'402' => "HTTP 402 Payment Required",
		'403' => "HTTP 403 Forbidden",
		'404' => "HTTP 404 Not Found",
		'405' => "HTTP 405 Method Not Allowed",
		'406' => "HTTP 406 Not Acceptable",
		'407' => "HTTP 407 Proxy Authentication Required",
		'408' => "HTTP 408 Request Timeout",
		'409' => "HTTP 409 Conflict",
		'410' => "HTTP 410 Gone",
		'411' => "HTTP 411 Length Required",
		'412' => "HTTP 412 Precondition Failed",
		'413' => "HTTP 413 Request Entity Too Large",
		'414' => "HTTP 414 Request-URI Too Long",
		'415' => "HTTP 415 Unsupported Media Type",
		'416' => "HTTP 416 Requested Range Not Satisfiable",
		'417' => "HTTP 417 Expectation Failed",
		'500' => "HTTP 500 Internal Server Error",
		'501' => "HTTP 501 Not Implemented",
		'502' => "HTTP 502 Bad Gateway",
		'503' => "HTTP 503 Service Unavailable",
		'504' => "HTTP 504 Gateway Timeout",
		'505' => "HTTP 505 HTTP Version Not Supported",
	);
	
	/**
		* @var        array The array with the HTTP status codes to be used here.
		*/
	protected $httpStatusCodes = null;
	
	/**
	 * @var        string The HTTP status code to send for the response.
	 */
	protected $httpStatusCode = '200';
	
	/**
	 * @var        array The HTTP headers scheduled to be sent with the response.
	 */
	protected $httpHeaders = array();
	
	/**
	 * @var        array The Cookies scheduled to be sent with the response.
	 */
	protected $cookies = array();
	
	/**
	 * @var        array An array of redirect information, or null if no redirect.
	 */
	protected $redirect = null;
	
	/**
	 * Initialize this Response.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$request = $context->getRequest();
		
		// if 'cookie_secure' is set, and null, then we need to set whatever WebRequest::isHttps() returns
		if(array_key_exists('cookie_secure', $parameters) && $parameters['cookie_secure'] === null) {
			$parameters['cookie_secure'] = $request->isHttps();
		}
		
		$this->setParameters(array(
			'cookie_lifetime' => isset($parameters['cookie_lifetime']) ? $parameters['cookie_lifetime'] : 0,
			'cookie_path'     => isset($parameters['cookie_path'])     ? $parameters['cookie_path']     : null,
			'cookie_domain'   => isset($parameters['cookie_domain'])   ? $parameters['cookie_domain']   : "",
			'cookie_secure'   => isset($parameters['cookie_secure'])   ? $parameters['cookie_secure']   : false,
			'cookie_httponly' => isset($parameters['cookie_httponly']) ? $parameters['cookie_httponly'] : false,
			// For historical reasons, PHP's setcookie() encodes cookies with urlencode(), which
			// is not compliant with RFC 6265 as it encodes spaces as a "+" sign instead of "%20".
			// This makes most client-side Javascript cookie libraries decode it not as a space
			// but as an actual plus sign. We sadly cannot change the default encoding of cookies
			// as it would be a breaking change, but introduced a setting instead, which we
			// recommend to set to "rawurlencode" for new projects.
			'cookie_encode_callback' => isset($parameters['cookie_encode_callback']) ? $parameters['cookie_encode_callback'] : 'urlencode',
		));
		
		switch($request->getProtocol()) {
			case 'HTTP':
				$this->httpStatusCodes = $this->http11StatusCodes;
				break;
			default:
				$this->httpStatusCodes = $this->http10StatusCodes;
		}
	}
	
	/**
	 * Send all response data to the client.
	 *
	 * @param      OutputType An optional Output Type object with information
	 *                             the response can use to send additional data,
	 *                             such as HTTP headers
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function send(?\YoudsFramework\OutputType $outputType = null)
	{
		
		// test
		/*
		$this->setHttpHeader('Location', 'https://www.terminusapp.com/blog/short-urls-vs-long-urls/');
		$this->setHttpStatusCode(302);
		$this->setHttpHeader('Content-Length', 0);
		$this->sendHttpResponseHeaders($outputType);
		*/
		
		if(is_array($this->redirect)) {
			$location = $this->redirect['location'];
			if(!preg_match('#^[^:]+://#', $location)) {
				if(isset($location[0]) && $location[0] == '/') {
					$rq = $this->getContext()->getRequest();
					$location = $rq->getUrlScheme() . '://' . $rq->getUrlAuthority() . $location;
				} else {
					$location = $this->context->getRouting()->getBaseHref() . $location;
				}
			}
			$this->setHttpHeader('Location', $location);
			$this->setHttpStatusCode($this->redirect['code']);
			if($this->getParameter('send_content_length', true) && !$this->hasHttpHeader('Content-Length') && !$this->getParameter('send_redirect_content', false)) {
				$this->setHttpHeader('Content-Length', 0);
			}
		} 
		$this->sendHttpResponseHeaders($outputType);  
		if(!is_array($this->redirect) || $this->getParameter('send_redirect_content', false)) {
			$this->sendContent();
		}
		if (is_array($this->redirect)) 
			exit;
	}
	
	/**
	 * Send the content for this response
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function sendContent()
	{
		if(is_resource($this->content) && $this->getParameter('use_sendfile_header', false)) {
			$info = stream_get_meta_data($this->content);
			if($info['wrapper_type'] == 'plainfile') {
				header($this->getParameter('sendfile_header_name', 'X-Sendfile') . ': ' . $info['uri']);
				return;
			}
		}
		return parent::sendContent();
	}
	
	/**
	 * Clear all response data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clear()
	{
		$this->clearContent();
		$this->httpStatusCode = '200';
		$this->httpHeaders = array();
		$this->cookies = array();
		$this->redirect = null;
	}
	
	/**
	 * Check whether or not some content is set.
	 *
	 * @return     bool If any content is set, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function hasContent()
	{
		return $this->content !== null && $this->content !== '';
	}
	
	/**
	 * Set the content type for the response.
	 *
	 * @param      string A content type.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setContentType($type)
	{
		$this->setHttpHeader('Content-Type', $type);
	}
	
	/**
	 * Retrieve the content type set for the response.
	 *
	 * @return     string A content type, or null if none is set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getContentType()
	{
		$retVal = $this->getHttpHeader('Content-Type');
		if(is_array($retVal) && count($retVal)) {
			return $retVal[0];
		} else {
			return null;
		}
	}
	
	/**
	 * Import response metadata (headers, cookies) from another response.
	 *
	 * @param      Response The other response to import information from.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function merge(Response $otherResponse)
	{
		parent::merge($otherResponse);
		
		if($otherResponse instanceof Response\Web) {
			foreach($otherResponse->getHttpHeaders() as $name => $value) {
				if(!$this->hasHttpHeader($name)) {
					$this->setHttpHeader($name, $value);
				}
			}
			foreach($otherResponse->getCookies() as $name => $cookie) {
				if(!$this->hasCookie($name)) {
					$this->setCookie($name, $cookie['value'], $cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly'], $cookie['encode_callback']);
				}
			}
			if($otherResponse->hasRedirect() && !$this->hasRedirect()) {
				$redirect = $otherResponse->getRedirect();
				$this->setRedirect($redirect['location'], $redirect['code']);
			}
		}
	}
	
	/**
	 * Check if the given HTTP status code is valid.
	 *
	 * @param      string A numeric HTTP status code.
	 *
	 * @return     bool True, if the code is valid, or false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function validateHttpStatusCode($code)
	{
		$code = (string)$code;
		return isset($this->httpStatusCodes[$code]);
	}
	
	/**
	 * Sets a HTTP status code for the response.
	 *
	 * @param      string A numeric HTTP status code.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setHttpStatusCode($code)
	{
		$code = (string)$code;
		if($this->validateHttpStatusCode($code)) {
			$this->httpStatusCode = $code;
		} else {
			throw new Exception(sprintf('Invalid %s Status code: %s', $this->context->getRequest()->getProtocol(), $code));
		}
	}
	
	/**
	 * Gets the HTTP status code set for the response.
	 *
	 * @return     string A numeric HTTP status code between 100 and 505, or null
	 *                    if no status code has been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getHttpStatusCode()
	{
		return $this->httpStatusCode;
	}

	/**
	 * Normalizes a HTTP header names
	 *
	 * @param      string A HTTP header name
	 *
	 * @return     string A normalized HTTP header name
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function normalizeHttpHeaderName($name)
	{
		if(strtolower($name) == "etag") {
			return "ETag";
		} elseif(strtolower($name) == "www-authenticate") {
			return "WWW-Authenticate";
		} else {
			return str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($name))));
		}
	}

	/**
	 * Retrieve the HTTP header values set for the response.
	 *
	 * @param      string A HTTP header field name.
	 *
	 * @return     array All values set for that header, or null if no headers set
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getHttpHeader($name)
	{
		$name = $this->normalizeHttpHeaderName($name);
		$retVal = null;
		if(isset($this->httpHeaders[$name])) {
			$retVal = $this->httpHeaders[$name];
		}
		return $retVal;
	}

	/**
	 * Retrieve the HTTP headers set for the response.
	 *
	 * @return     array An associative array of HTTP header names and values.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getHttpHeaders()
	{
		return $this->httpHeaders;
	}

	/**
	 * Check if an HTTP header has been set for the response.
	 *
	 * @param      string A HTTP header field name.
	 *
	 * @return     bool true if the header exists, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasHttpHeader($name)
	{
		$name = $this->normalizeHttpHeaderName($name);
		$retVal = false;
		if(isset($this->httpHeaders[$name])) {
			$retVal = true;
		}
		return $retVal;
	}

	/**
	 * Set a HTTP header for the response
	 *
	 * @param      string A HTTP header field name.
	 * @param      mixed  A HTTP header field value, of an array of values.
	 * @param      bool   If true, a header with that name will be overwritten,
	 *                    otherwise, the value will be appended.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setHttpHeader($name, $value, $replace = true)
	{
		$name = $this->normalizeHttpHeaderName($name);
		if(!isset($this->httpHeaders[$name]) || $replace) {
			$this->httpHeaders[$name] = array();
		}
		if(is_array($value)) {
			$this->httpHeaders[$name] = array_merge($this->httpHeaders[$name], $value);
		} else {
			$this->httpHeaders[$name][] = $value;
		}
	}

	/**
	 * Send a cookie.
	 *
	 * @param      string         A cookie name.
	 * @param      mixed          Data to store into a cookie. If null or empty cookie
	 *                            will be tried to be removed.
	 * @param      mixed          The lifetime of the cookie in seconds. When you pass 0 
	 *                            the cookie will be valid until the browser is closed.
	 *                            You can also use a strtotime() string instead of an int.
	 * @param      string         The path on the server the cookie will be available on.
	 * @param      string         The domain the cookie is available on.
	 * @param      bool           Indicates that the cookie should only be transmitted 
	 *                            over a secure HTTPS connection.
	 * @param      bool           Whether the cookie will be made accessible only through
	 *                            the HTTP protocol, and not to client-side scripts.
	 * @param      callable|bool  Callback to encode the cookie value. Set to false
	 *                            if you did already encode the value on your own.
	 *
	 * @throws     Exception If $encodeCallback is neither false nor callable.
	 * 
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setCookie($name, $value, $lifetime = null, $path = null, $domain = null, $secure = null, $httponly = null, $encodeCallback = null)
	{
		$lifetime       =         $lifetime       !== null ? $lifetime       : $this->getParameter('cookie_lifetime');
		$path           =         $path           !== null ? $path           : $this->getParameter('cookie_path');
		$domain         =         $domain         !== null ? $domain         : $this->getParameter('cookie_domain');
		$secure         = (bool) ($secure         !== null ? $secure         : $this->getParameter('cookie_secure'));
		$httponly       = (bool) ($httponly       !== null ? $httponly       : $this->getParameter('cookie_httponly'));
		$encodeCallback =         $encodeCallback !== null ? $encodeCallback : $this->getParameter('cookie_encode_callback');
		
		if($encodeCallback !== false && !is_callable($encodeCallback)) {
			throw new Exception(sprintf('setCookie() $encodeCallback argument is not callable: %s', $encodeCallback));
		}
		
		$this->cookies[$name] = array(
			'value' => $value,
			'lifetime' => $lifetime,
			'path' => $path,
			'domain' => $domain,
			'secure' => $secure,
			'httponly' => $httponly,
			'encode_callback' => $encodeCallback
		);
	}
	
	/**
	 * Unset an existing cookie.
	 * All arguments must reflect the values of the cookie that is already set.
	 *
	 * @param      string A cookie name.
	 * @param      string The path on the server the cookie will be available on.
	 * @param      string The domain the cookie is available on.
	 * @param      bool   Indicates that the cookie should only be transmitted 
	 *                    over a secure HTTPS connection.
	 * @param      bool   Whether the cookie will be made accessible only through
	 *                    the HTTP protocol, and not to client-side scripts.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function unsetCookie($name, $path = null, $domain = null, $secure = null, $httponly = null)
	{
		// false as the value, triggers deletion
		// null for the lifetime, since Youds Framework automatically sets that when the value is false or null
		$this->setCookie($name, false, null, $path, $domain, $secure, $httponly);
	}
	
	/**
	 * Get a cookie set for later sending.
	 *
	 * @param      string The name of the cookie.
	 *
	 * @return     array An associative array containing the cookie data or null
	 *                   if no cookie with that name has been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getCookie($name)
	{
		if(isset($this->cookies[$name])) {
			return $this->cookies[$name];
		}
	}
	
	/**
	 * Check if a cookie has been set for later sending.
	 *
	 * @param      string The name of the cookie.
	 *
	 * @return     bool True if a cookie with that name has been set, else false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasCookie($name)
	{
		return isset($this->cookies[$name]);
	}
	
	/**
	 * Remove a cookie previously set for later sending.
	 *
	 * This method cannot be used to unset a cookie. It's purpose is to remove a
	 * cookie from the list of cookies to be sent along with the response. If you
	 * wish to remove an existing cookie, use the setCookie method and supply null
	 * as the value.
	 *
	 * @param      string The name of the cookie.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function removeCookie($name)
	{
		if(isset($this->cookies[$name])) {
			unset($this->cookies[$name]);
		}
	}
	
	/**
	 * Get a list of cookies set for later sending.
	 *
	 * @return     array An associative array of cookie names (key) and cookie
	 *                   information (value, associative array).
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getCookies()
	{
		return $this->cookies;
	}
	
	/**
	 * Remove the HTTP header set for the response
	 *
	 * @param      string A HTTP header field name.
	 *
	 * @return     mixed The removed header's value or null if header was not set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function removeHttpHeader($name)
	{
		$name = $this->normalizeHttpHeaderName($name);
		$retVal = null;
		if(isset($this->httpHeaders[$name])) {
			$retVal = $this->httpHeaders[$name];
			unset($this->httpHeaders[$name]);
		}
		return $retVal;
	}

	/**
	 * Clears the HTTP headers set for this response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearHttpHeaders()
	{
		$this->httpHeaders = array();
	}
	
	/**
	 * Sends HTTP Status code, headers and cookies
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function sendHttpResponseHeaders(?OutputType $outputType = null)
	{
		if($outputType === null) {
			$outputType = $this->getOutputType();
		}
		
		// send HTTP status code
		if(isset($this->httpStatusCode) && isset($this->httpStatusCodes[$this->httpStatusCode])) {
			header($this->httpStatusCodes[$this->httpStatusCode]);
		}
		
		if($outputType !== null) {
			$httpHeaders = $outputType->getParameter('http_headers');
			if(!is_array($httpHeaders)) {
				$httpHeaders = array();
			}
			foreach($httpHeaders as $name => $value) {
				if(!$this->hasHttpHeader($name)) {
					$this->setHttpHeader($name, $value);
				}
			}
		}
		
		if($this->getParameter('send_content_length', true) && !$this->hasHttpHeader('Content-Length') && ($contentSize = $this->getContentSize()) !== false) {
			$this->setHttpHeader('Content-Length', $contentSize);
		}
		
		if($this->getParameter('expose_youds', true) && !$this->hasHttpHeader('X-Powered-By')) {
			if(Config::get('expose_framework_version', $expose_php = ini_get('expose_php'))) {
				$xpbh = Config::get('framework.release');
			} else {
				$xpbh = Config::get('framework.name');
			}
			if($expose_php) {
				$xpbh .= ' on PHP/' . PHP_VERSION;
			}
			$this->setHttpHeader('X-Powered-By', $xpbh);
		}
		
		$routing = $this->context->getRouting(); 
		if($routing instanceof Web) {
			$basePath = $routing->getBasePath();
		} else {
			$basePath = '/';
		}
		
		// send cookies
		foreach($this->cookies as $name => $values) {
			if(is_string($values['lifetime'])) {
				// a string, so we pass it to strtotime()
				$expire = strtotime($values['lifetime']);
			} else {
				// do we want to set expiration time or not?
				$expire = ($values['lifetime'] != 0) ? time() + $values['lifetime'] : 0;
			}

			if($values['value'] === false || $values['value'] === null || $values['value'] === '') {
				$expire = time() - 3600 * 24;
			}

			if($values['encode_callback']) {
				$values['value'] = call_user_func($values['encode_callback'], $values['value']);
			}
			
			if($values['path'] === null) {
				$values['path'] = $basePath;
			}
			
			setrawcookie($name, $values['value'], $expire, $values['path'], $values['domain'], $values['secure'], $values['httponly']);
		}
		
		// send headers
		foreach($this->httpHeaders as $name => $values) {
			foreach($values as $key => $value) {
				if($key == 0) {
					header($name . ': ' . $value);
				} else {
					header($name . ': ' . $value, false);
				}
			}
		}
	}

	/**
	 * Redirect externally.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setRedirect($location, $code = 302)
	{
		if(!$this->validateHttpStatusCode($code)) {
			throw new Exception(sprintf('Invalid %s Redirect Status code: %s', $this->context->getRequest()->getProtocol(), $code));
		}
		$this->redirect = array('location' => $location, 'code' => $code);
		
		return $this;
	}


	/**
	 * Get info about the set redirect.
	 *
	 * @return     array An assoc array of redirect info, or null if none set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getRedirect()
	{
		return $this->redirect;
	}

	/**
	 * Check if a redirect is set.
	 *
	 * @return     bool true, if a redirect is set, otherwise false
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasRedirect()
	{
		return $this->redirect !== null;
	}

	/**
	 * Clear any set redirect information.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearRedirect()
	{
		$this->redirect = null;
	}
}

?>
