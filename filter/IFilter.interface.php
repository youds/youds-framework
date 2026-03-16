<?php
namespace YoudsFramework\Filter;
use YoudsFramework\Filter;
use YoudsFramework\ExecutionContainer;
use YoudsFramework\Context;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Filter provides a way for you to intercept incoming requests or outgoing
 * responses.
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
interface IFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      Chain A Chain instance.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container);

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getContext();

	/**
	 * Initialize this Filter.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Filter.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function initialize(Context $context, array $parameters = array());
}

?>
