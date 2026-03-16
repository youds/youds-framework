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
 * Interface for DataHolders that allow access to Files.
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
interface IFiles
{
	public function hasFile($name);
	
	public function isFileValueEmpty($name);
	
	public function &getFile($name, $default = null);
	
	public function &getFiles();
	
	public function getFileNames();
	
	public function getFlatFileNames();
	
	public function setFile($name, UploadedFile $file);
	
	public function setFiles(array $files);
	
	public function &removeFile($name);
	
	public function clearFiles();
	
	public function mergeFiles($other);
}

?>
