<?php
namespace YoudsFramework\Filter;
use YoudsFramework\Filter;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * ExecutionTimeFilter tracks the length of time it takes for an entire
 * request to be served starting with the dispatch and ending when the last 
 * chain request has been served.
 *
 * Optional parameters:
 *
 * # comment - [Yes] - Should we add an HTML comment to the end of each
 *                            output with the execution time?
 * # replace - [No] - If this exists, every occurrence of the value in the
 *                           client response will be replaced by the execution
 *                           time.
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
class ExecutionTime extends Base implements IGlobal, IAction
{
	/**
	 * Execute this filter.
	 *
	 * @param      Chain        The filter chain.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @throws     Exceptions\Filter If an error occurs during execution.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container)
	{
		$comment = $this->getParameter('comment', false);
		$replace = $this->getParameter('replace', false);
		
		$start = microtime(true);
		$filterChain->execute($container);
		
		$response = $container->getResponse();
		
		$outputTypes = (array) $this->getParameter('output_types');
		if(!$response->isContentMutable() || (is_array($outputTypes) && !in_array($response->getOutputType()->getName(), $outputTypes))) {
			return;
		}
		
		$time = (microtime(true) - $start);
		
		
		if($replace) {
			$output = $response->getContent();
			$output = str_replace($replace, $time, $output);
			$response->setContent($output);
		}
		
		if($comment) {
			if($comment === true) {
				$comment = "\n\n<!-- This page took %s seconds to process -->";
			}
			$response->appendContent(sprintf($comment, $time));
		}
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Filter If an error occurs during 
	 *                                         initialization.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		// set defaults
		$this->setParameter('comment', true);
		$this->setParameter('replace', null);
		$this->setParameter('output_types', null);

		// initialize parent
		parent::initialize($context, $parameters);
	}
}

?>
