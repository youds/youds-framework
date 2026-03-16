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
 * BasicSecurityFilter checks security by calling the getCredentials() 
 * method of the chain. Once the credential has been acquired, 
 * BasicSecurityFilter verifies the user has the same credential 
 * by calling the hasCredentials() method of SecurityUser.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage filter
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Security extends Filter implements IAction, ISecurity
{
	/**
	 * Execute this filter.
	 *
	 * @param      Chain        A Chain instance.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container)
	{
		// get the stuff
		$context = $this->getContext();
		$user = $context->getUser();

		// get the current chain instance
		$chainInstance = $container->getChainInstance();

		// get matched routes / content / chain
		$routingArray = array();
		$matchedRoutes = $this->getContext()->getRequest()->getAttribute('matched_routes', 'org.framework.routing');
		
		if (is_array($matchedRoutes) && count($matchedRoutes) > 0):
			$routing = $this->getContext()->getRouting();
			$routingArray = (array) $routing;
			$matchedRoute = $routing->getRoute($matchedRoutes[0]);
			$matchedChainName = $routing->getRoute($matchedRoutes[0])['opt']['chain'];
			$matchedContentName = $routing->getRoute($matchedRoutes[0])['opt']['content'];
		endif;

		// determine credentials
		if (isset($routingArray['opt']['credentials']))
			$routingCredentials = explode(',', $routingArray['opt']['credentials']);
		else
			$routingCredentials = array();
		
		
		// check for non-secure or default secure content / chain
		if(!$chainInstance->isSecure() && (count($routingCredentials) == 0 || (($matchedContentName != Config::get('chains.login_content') && $matchedContentName != Config::get('chains.secure_content')) && ($matchedChainName != Config::get('chains.secure_chain') && $matchedChainName != Config::get('chains.login_chain'))))) {
			
			// the chain instance does not require authentication, so we can continue in the chain and then bail out early
			return $filterChain->execute($container);
		}

		// get chain credentials
		$chainCredentials = $chainInstance->getCredentials();
		
		// merge credentials
		$credential = array_merge((array) $chainCredentials, (array) $routingCredentials);

		// credentials can be anything you wish; a string, array, object, etc.
		// as long as you add the same exact data to the user as a credential,
		// it will use it and authorize the user as having the credential
		//
		// NOTE: the nice thing about the Action class is that getCredential()
		//       is vague enough to describe any level of security and can be
		//       used to retrieve such data and should never have to be altered
		if($user->isAuthenticated() && ($credential === null || $user->hasCredentials($credential))) {
			// the user has access, continue
			$filterChain->execute($container);
		} else {
			
			if($user->isAuthenticated()) {
				
				// the user doesn't have access
				$container->setNext($container->createSystemActionForwardContainer('secure'));
			} else {
				
				// the user is not authenticated
				$container->setNext($container->createSystemActionForwardContainer('login'));
			}
		}
	}
}

?>
