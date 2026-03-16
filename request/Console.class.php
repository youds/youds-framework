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
 * Console provides methods for retrieving client request
 * information parameters.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Console extends DataHolder implements IFiles
{
	/**
	 * @constant   Constant for source name of files.
	 */
	const SOURCE_FILES = 'files';
	
	/**
	 * @var        array An array of files uploaded during the request.
	 */
	protected $files = array();

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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function &getFile($name, $default = null)
	{
		if((isset($this->files[$name]) || array_key_exists($name, $this->files))) {
			$retVal =& $this->files[$name];
		} else {
			try {
				$retVal =& ArrayPathDefinition::getValue($name, $this->files);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	 */
	public function hasFiles()
	{
		return count($this->files) > 0;
	}

	/**
	 * Checks if a file is empty, i.e. not set or set, but not uploaded.
	 *
	 * @param      string The file name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function setFiles(array $files)
	{
		$this->files = array_merge($this->files, $files);
	}

	/**
	 * Clear all files.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
	public function __construct(array $data = array())
	{
		$this->registerSource(self::SOURCE_FILES, $this->files);

        // auto accept parameters
        $params = [];
        foreach ($data as $key => $arg):

            if (is_string($arg) && str_starts_with($arg, '--')):
                $name = substr($arg, 2, strpos($arg, '=') - 2);
                $params[$name] = str_replace('"', '', substr($arg, strlen($name) + 3));
            endif;
        endforeach;

		// call the parent constructor which handles the actual loading of the data
		parent::__construct($data);

        $this->parameters = array_merge($this->parameters, $params);

	}
	
	/**
	 * Merge in Files from another request data holder.
	 *
	 * @param      DataHolder The other request data holder.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function mergeFiles($other)
	{
		if($other instanceof IFilesDataHolder) {
			$this->setFiles($other->getFiles());
		}
	}
}

?>
