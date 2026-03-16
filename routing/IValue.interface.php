<?php
namespace YoudsFramework\Routing;
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
 *  values are used internally and, optionally, by users in gen() calls
 * and callbacks to have more control over encoding behavior and values in pre-
 * and postfixes
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage routing
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface IValue extends \ArrayAccess
{
	/**
	 * Constructor.
	 *
	 * @param      mixed The value.
	 * @param      bool  Whether or not the value needs encoding.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function __construct($value, $valueNeedsEncoding = true);
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context instead of the instance, which will later
	 * be restored by __wakeup().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function __sleep();

	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context instance based on their names set by __sleep().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function __wakeup();
	
	/**
	 * Initialize the routing value.
	 *
	 * @param      Context The Context.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function initialize(Context $context, array $parameters = array());
	
	/**
	 * Set the value.
	 * 
	 * @param      mixed The value.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function setValue($value);
	
	/**
	 * Retrieve the value.
	 * 
	 * @param      mixed The value.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getValue();
	
	/**
	 * Set the prefix.
	 * 
	 * @param      string The prefix.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function setPrefix($value);
	
	/**
	 * Retrieve the prefix.
	 * 
	 * @return     string The prefix.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getPrefix();
	
	/**
	 * Check if a prefix is set.
	 * 
	 * @return     bool True, if a prefix is set, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function hasPrefix();
	
	/**
	 * Set the postfix.
	 * 
	 * @param      string The postfix.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function setPostfix($value);
	
	/**
	 * Retrieve the postfix.
	 * 
	 * @return     string The postfix.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getPostfix();
	
	/**
	 * Check if a postfix is set.
	 * 
	 * @return     bool True, if a postfix is set, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function hasPostfix();
	
	/**
	 * Set whether or not the value needs to be encoded.
	 * 
	 * @param      bool True, if the postfix needs encoding, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function setValueNeedsEncoding($needsEncoding);
	
	/**
	 * Retrieve whether or not the value needs to be encoded.
	 * 
	 * @return     bool True, if the value needs encoding, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getValueNeedsEncoding();
	
	/**
	 * Set whether or not the prefix needs to be encoded.
	 * 
	 * @param      bool True, if the prefix needs encoding, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function setPrefixNeedsEncoding($needsEncoding);
	
	/**
	 * Retrieve whether or not the prefix needs to be encoded.
	 * 
	 * @return     bool True, if the prefix needs encoding, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getPrefixNeedsEncoding();
	
	/**
	 * Set whether or not the postfix needs to be encoded.
	 * 
	 * @param      bool True, if the postfix needs encoding, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function setPostfixNeedsEncoding($needsEncoding);
	
	/**
	 * Retrieve whether or not the postfix needs to be encoded.
	 * 
	 * @return     bool True, if the postfix needs encoding, false otherwise.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function getPostfixNeedsEncoding();
	
	/**
	 * Check if this routing value is equal to the given parameter.
	 * 
	 * @param      mixed The value to compare $this against.
	 * 
	 * @return     bool Whether the value matches $this.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function equals($other);
	
	/**
	 * Return the encoded value (without pre- or postfix) for BC.
	 * 
	 * @return     string The encoded value.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function __toString();
}

?>
