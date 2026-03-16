<?php
namespace YoudsFramework\Filter;
use YoudsFramework\Filter;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * TidyFilter cleans up (X)HTML or XML using the tidy extension.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage filter
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Tidy extends Base implements IGlobal
{
	/**
	 * Execute this filter.
	 *
	 * @param      Chain        The filter chain.
	 * @param      ExecutionContainer The current execution container.
	 *
	 * @throws     Exceptions\Filter If an error occurs during execution.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function execute(Chain $filterChain, ExecutionContainer $container)
	{
		// nothing to do so far. let's carry on in the chain
		$filterChain->execute($container);
		
		// fetch some prerequisites
		$response = $container->getResponse();
		$ot = $response->getOutputType();
		$cfg = $this->getParameters();
		
		if(!$response->isContentMutable() || !($output = $response->getContent())) {
			// content empty or response not mutable; it's over!
			return;
		}
		
		if(is_array($cfg['methods']) && !in_array($container->getRequestMethod(), $cfg['methods'])) {
			// we're not allowed to run for this request method
			return;
		}
		
		if(is_array($cfg['output_types']) && !in_array($ot->getName(), $cfg['output_types'])) {
			// we're not allowed to run for this output type
			return;
		}
		
		$tidy = new tidy();
		$tidy->parseString($output, $cfg['tidy_options'], $cfg['tidy_encoding']);
		$tidy->cleanRepair();
		
		if($tidy->getStatus()) {
			// warning or error occurred
			$emsg = sprintf(
				'Tidy Filter encountered the following problems while parsing and cleaning the document: ' . "\n\n%s",
				$tidy->errorBuffer
			);
			
			if(Config::get('core.use_logging') && $cfg['log_errors']) {
				$lmsg = $emsg . "\n\nResponse content:\n\n" . $response->getContent();
				$lm = $this->context->getLoggerManager();
				$mc = $lm->getDefaultMessageClass();
				$m = new $mc($lmsg, $cfg['logging_severity']);
				$lm->log($m, $cfg['logging_logger']);
			}
			
			// all in all, that didn't go so well. let's see if we should just silently abort instead of throwing an exception
			if(!$cfg['ignore_errors']) {
				throw new Parse($emsg);
			}
		}
		
		$response->setContent((string)$tidy);
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Filter If an error occurs during
	 *                                         initialization
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		// set defaults
		$this->setParameters(array(
			'methods'          => null,
			'output_types'     => null,
			
			'tidy_options'     => array(),
			'tidy_encoding'    => null,
			
			'ignore_errors'    => true,
			'log_errors'       => true,
			'logging_severity' => Logger::WARN,
			'logging_logger'   => null,
		));
		
		// initialize parent
		parent::initialize($context, $parameters);
	}
}

?>
