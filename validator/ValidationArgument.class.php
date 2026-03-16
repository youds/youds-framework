<?php
namespace YoudsFramework\Validator;
use YoudsFramework\Validator;
use YoudsFramework\Request\DataHolder;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * ValidationArgument is a tuple of argument name and source that specifies 
 * the argument to validate.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class ValidationArgument
{
	/**
	 * @var        string the name of the argument.
	 */
	protected $name;
	
	/**
	 * @var        string the name of the source.
	 */
	protected $source;
	
	/**
	 * Create a new ValidationArgument instance.
	 * 
	 * @param      string the name of the argument.
	 * @param      string the name of the source, if null, DataHolder::SOURCE_PARAMETERS is used.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function __construct($name, $source = null)
	{
		if($source === null) {
			$source = DataHolder::SOURCE_PARAMETERS;
		}
		$this->name = $name;
		$this->source = $source;
	}
	
	/**
	 * Retrieve the name of the argument for this instance.
	 * 
	 * @return     string the name of the argument
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Retrieve the name of the source for this instance.
	 * 
	 * @return     string the name of the source.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * Get a unique hash value for this ValidationArgument.
	 * 
	 * @return     string the hash value
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getHash()
	{
		return sprintf('%s/%s', $this->source, $this->name);
	}
}

?>
