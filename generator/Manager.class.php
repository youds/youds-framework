<?php
namespace YoudsFramework\Generator;
use YoudsFramework\Context;

/**
 * GeneratorManager provides access to generator facilities.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage generator
 *
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Manager {

	public $generator;
	
	function initialize (Context $context)
	{
	}
	
	/**
	 * Setup object
	 *
	 * @param     array $params
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	function __construct($params = array()) {
	
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function startup()
	{
		// grab a pointer to the request data
		$this->generator = new Generator\Generator();

		
	}
	

	
	
}
?>