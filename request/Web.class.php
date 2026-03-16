<?php
namespace YoudsFramework\Request;
use YoudsFramework\Request\ICookies;
use YoudsFramework\Request\IFiles;
use YoudsFramework\Request\IHeaders;
use YoudsFramework\Util\ArrayPathDefinition;


// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Web provides methods for retrieving client request 
 * information parameters.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Web extends DataHolder implements ICookies, IFiles, IHeaders
{
	/**
	 * @constant   Constant for source name of cookies.
	 */
	const SOURCE_COOKIES = 'cookies';
	
	/**
	 * @constant   Constant for source name of files.
	 */
	const SOURCE_FILES = 'files';
	
	/**
	 * @constant   Constant for source name of HTTP headers.
	 */
	const SOURCE_HEADERS = 'headers';
	
	/**
	 * @var        array An (proper) array of files uploaded during the request.
	 */
	protected $files = array();
	
	/**
	 * @var        array An array of cookies set in the request.
	 */
	protected $cookies = array();
	
	/**
	 * @var        array An array of headers sent with the request.
	 */
	protected $headers = array();

	/**
	 * Checks if there is a value of a parameter is empty or not set.
	 *
	 * @param      string The field name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function isParameterValueEmpty($field)
	{
		$value = $this->getParameter($field);
		return ($value === null || $value === '');
	}

	/**
	 * Clear all cookies.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function clearCookies()
	{
		$this->cookies = array();
	}

	/**
	 * Indicates whether or not a Cookie exists.
	 *
	 * @param      string A cookie name.
	 *
	 * @return     bool True, if a cookie with that name exists, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasCookie($name)
	{
		if(isset($this->cookies[$name]) || array_key_exists($name, $this->cookies)) {
			return true;
		}
		try {
			return ArrayPathDefinition::hasValue($name, $this->cookies);
		} catch(Exceptions\InvalidArgument $e) {
			return false;
		}
	}

	/**
	 * Checks if there is a value of a cookie is empty or not set.
	 *
	 * @param      string The cookie name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function isCookieValueEmpty($name)
	{
		return ($this->getCookie($name) === null);
	}

	/**
	 * Retrieve a value stored into a cookie.
	 *
	 * @param      string A cookie name.
	 * @param      mixed  A default value.
	 *
	 * @return     mixed The value from the cookie, if such a cookie exists,
	 *                   otherwise null.
	 *
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getCookie($name, $default = null)
	{
		if(isset($this->cookies[$name]) || array_key_exists($name, $this->cookies)) {
			return $this->cookies[$name];
		}
		try {
			return ArrayPathDefinition::getValue($name, $this->cookies, $default);
		} catch(Exceptions\InvalidArgument $e) {
			return $default;
		}
	}

	/**
	 * Set a cookie.
	 *
	 * If a cookie with the name already exists the value will be overridden.
	 *
	 * @param      string A cookie name.
	 * @param      mixed  A cookie value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function setCookie($name, $value)
	{
		$this->cookies[$name] = $value;
	}

	/**
	 * Set an array of cookies.
	 *
	 * If an existing cookie name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array An associative array of cookies and their values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function setCookies(array $cookies)
	{
		$this->cookies = array_merge($this->cookies, $cookies);
	}

	/**
	 * Remove a cookie.
	 *
	 * @param      string The cookie name
	 *
	 * @return     string The value of the removed cookie, if it had been set.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function &removeCookie($name)
	{
		if(isset($this->cookies[$name]) || array_key_exists($name, $this->cookies)) {
			$retVal =& $this->cookies[$name];
			unset($this->cookies[$name]);
			return $retVal;
		}
		try {
			return ArrayPathDefinition::unsetValue($name, $this->cookies);
		} catch(Exceptions\InvalidArgument $e) {
		}
	}

	/**
	 * Retrieve all cookies.
	 *
	 * @return     array The cookies.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function &getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Retrieve an array of cookie names.
	 *
	 * @return     array An indexed array of cookie names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getCookieNames()
	{
		return array_keys($this->cookies);
	}
	
	/**
	 * Retrieve an array of flattened cookie names. This means when a cookie is an
	 * array you wont get the name of the cookie in the result but instead all
	 * child keys appended to the name (like foo[0],foo[1][0], ...).
	 *
	 * @return     array An indexed array of cookie names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getFlatCookieNames()
	{
		return ArrayPathDefinition::getFlatKeyNames($this->cookies);
	}
	
	/**
	 * Clear all headers.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function clearHeaders()
	{
		$this->headers = array();
	}

	/**
	 * Retrieve all HTTP headers.
	 *
	 * @return     array A list of HTTP headers (keys in original PHP format).
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getHeaders()
	{
		return $this->headers;
	}
	
	/**
	 * Get a HTTP header.
	 *
	 * @param      string Case-insensitive name of a header, using either a hyphen
	 *                    or an underscore as a separator.
	 * @param      mixed  A default value.
	 *
	 * @return     string The header value, or null if header wasn't set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getHeader($name, $default = null)
	{
		$name = str_replace('-', '_', strtoupper($name));
		if(isset($this->headers[$name]) || array_key_exists($name, $this->headers)) {
			return $this->headers[$name];
		}

		return $default;
	}
	
	/**
	 * Check if a HTTP header exists.
	 *
	 * @param      string Case-insensitive name of a header, using either a hyphen
	 *                    or an underscore as a separator.
	 *
	 * @return     bool True if the header was sent with the current request.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasHeader($name)
	{
		$name = str_replace('-', '_', strtoupper($name));
		return (isset($this->headers[$name]) || array_key_exists($name, $this->headers));
	}
	
	/**
	 * Checks if there is a value of a header is empty or not set.
	 *
	 * @param      string The header name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function isHeaderValueEmpty($name)
	{
		return ($this->getHeader($name) === null);
	}
	/**
	 * Set a header.
	 *
	 * The header name is normalized before storing it.
	 *
	 * @param      string A header name.
	 * @param      mixed  A header value.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function setHeader($name, $value)
	{
		$this->headers[str_replace('-', '_', strtoupper($name))] = $value;
	}

	/**
	 * Set an array of headers.
	 *
	 * @param      array An associative array of headers and their values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, $headers);
	}

	/**
	 * Remove a HTTP header.
	 *
	 * @param      string Case-insensitive name of a header, using either a hyphen
	 *                    or an underscore as a separator.
	 *
	 * @return     string The value of the removed header, if it had been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &removeHeader($name)
	{
		$retVal = null;
		$name = str_replace('-', '_', strtoupper($name));
		if(isset($this->headers[$name]) || array_key_exists($name, $this->headers)) {
			$retVal =& $this->headers[$name];
			unset($this->headers[$name]);
		}
		return $retVal;
	}
	
	/**
	 * Retrieve an array of header names.
	 *
	 * @return     array An indexed array of header names in original PHP format.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getHeaderNames()
	{
		return array_keys($this->headers);
	}
	
	/**
	 * Retrieve an array of file information.
	 *
	 * @param      string A file name.
	 * @param      mixed  A default return value.
	 *
	 * @return     mixed An UploadedFile object with file information, or an
	 *                   array if the field name has child elements, or null (or
	 *                   the supplied default return value) no such file exists.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getFile($name, $default = null)
	{
		if((isset($this->files[$name]) || array_key_exists($name, $this->files))) {
			$retVal =& $this->files[$name];
		} else {
			try {
				$retVal = &ArrayPathDefinition::getValue($name, $this->files);
			} catch(Exceptions\InvalidArgument $e) {
				$retVal = $default;
			}
		}
		if(is_array($retVal) || $retVal instanceof UploadedFile) {
			return $retVal;
		}
		return $default;
	}

	/**
	 * Retrieve an array of files.
	 *
	 * @param      bool Whether or not to include names of nested elements.
	 *                  Defaults to true.
	 *
	 * @return     array An associative array of files.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getFiles()
	{
		return $this->files;
	}

	/**
	 * Indicates whether or not a file exists.
	 *
	 * @param      string A file name.
	 *
	 * @return     bool true, if the file exists, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasFile($name)
	{
		if((isset($this->files[$name]) || array_key_exists($name, $this->files))) {
			$val = $this->files[$name];
		} else {
			try {
				$val = ArrayPathDefinition::getValue($name, $this->files);
			} catch(Exceptions\InvalidArgument $e) {
				return false;
			}
		}
		return (is_array($val) || $val instanceof UploadedFile);
	}

	/**
	 * Indicates whether or not any files exist.
	 *
	 * @return     bool true, if any files exist, otherwise false.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function hasFiles()
	{
		return count($this->files) > 0;
	}

	/**
	 * Checks if a file is empty, i.e. not set or set, but not actually uploaded.
	 *
	 * @param      string The file name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function isFileValueEmpty($name)
	{
		$file = $this->getFile($name);
		if(!($file instanceof UploadedFile)) {
			return true;
		}
		return ($file->getError() == UPLOAD_ERR_NO_FILE);
	}

	/**
	 * Removes file information for given file.
	 *
	 * @param      string A file name
	 *
	 * @return     mixed The old UploadedFile instance or array of elements.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function &removeFile($name)
	{
		if(isset($this->files[$name]) || array_key_exists($name, $this->files)) {
			$retVal =& $this->files[$name];
			unset($this->files[$name]);
			return $retVal;
		}
		try {
			return ArrayPathDefinition::unsetValue($name, $this->files);
		} catch(Exceptions\InvalidArgument $e) {
		}
	}

	/**
	 * Set a file.
	 *
	 * If a file with the name already exists the value will be overridden.
	 *
	 * @param      string            A file name.
	 * @param      UploadedFile An UploadedFile object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function setFile($name, UploadedFile $file)
	{
		$this->files[$name] = $file;
	}

	/**
	 * Set an array of files.
	 *
	 * @param      array An assoc array of names and UploadedFile objects.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function setFiles(array $files)
	{
		$this->files = array_merge($this->files, $files);
	}

	/**
	 * Clear all files.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function clearFiles()
	{
		$this->files = array();
	}

	/**
	 * Retrieve an array of file names.
	 *
	 * @return     array An indexed array of file names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getFileNames()
	{
		return array_keys($this->files);
	}
	
	/**
	 * Retrieve an array of flattened file names. This means when a file is an
	 * array you wont get the name of the file in the result but instead all child
	 * keys appended to the name (like foo[0],foo[1][0], ...).
	 *
	 * @return     array An indexed array of file names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getFlatFileNames()
	{
		return ArrayPathDefinition::getFlatKeyNames($this->files);
	}
	
	/**
	 * Constructor
	 *
	 * @param      array An associative array of request data source names and
	 *                   data arrays.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct(array $data = array())
	{
		$this->registerSource(self::SOURCE_COOKIES, $this->cookies);
		$this->registerSource(self::SOURCE_FILES, $this->files);
		$this->registerSource(self::SOURCE_HEADERS, $this->headers);
		
		// call the parent ctor which handles the actual loading of the data
		parent::__construct($data);
	}
	
	/**
	 * Merge in Cookies from another request data holder.
	 *
	 * @param      DataHolder The other request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function mergeCookies($other)
	{
		if($other instanceof ICookiesDataHolder) {
			$this->setCookies($other->getCookies());
		}
	}
	
	/**
	 * Merge in Files from another request data holder.
	 *
	 * @param      DataHolder The other request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function mergeFiles($other)
	{
		if($other instanceof IFilesDataHolder) {
			$this->setFiles($other->getFiles());
		}
	}
	
	/**
	 * Merge in Headers from another request data holder.
	 *
	 * @param      DataHolder The other request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function mergeHeaders($other)
	{
		if($other instanceof IHeadersDataHolder) {
			$this->setHeaders($other->getHeaders());
		}
	}

	
}

?>
