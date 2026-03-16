<?php
namespace YoudsFramework\Filter;
use YoudsFramework\Filter;
use YoudsFramework\ExecutionContainer;
 
// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Dispatch is the last in the chain of global filters and executes
 * the execution container, also re-setting the container's response to the
 * return value of the execution, so responses from forwards are passed along
 * properly.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage filter
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Dispatch extends Filter implements IGlobal
{
	/**
	 * Execute this filter.
	 *
	 * The Dispatch executes the execution container.
	 *
	 * @param      Chain        The filter chain.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @throws     Exceptions\Filter If an error occurs during execution.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container)
	{
		$container->setResponse($container->execute());
	}
}

?>
