<?php
namespace YoudsFramework\Testing;
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
 * UnitTestCase is the base class for all unit testcases and provides
 * the necessary assertions
 * 
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class UnitTestCase extends PhpUnitTestCase implements IUnitTestCase
{
	/**
	 * @var        string the name of the context to use, null for default context
	 */
	protected $contextName = null;
	
	/**
	 * Return the context defined for this test (or the default one).
	 *
	 * @return     Context The context instance defined for this test.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function getContext()
	{
		return Context::getInstance($this->contextName);
	}
}
