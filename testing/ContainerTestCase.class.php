<?php
namespace YoudsFramework\Testing;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2005-2010 the Youds Framework Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * ContainerTestCase is the base class for all tests that target a specific
 * container execution and provides the necessary assertions
 * 
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id: FlowTestCase.class.php 3843 2009-02-16 14:12:47Z felix $
 */
abstract class ContainerTestCase extends FragmentTestCase
{
	/**
	 * @var        string the name of the chain to use
	 */
	protected $actionName;
	
	/**
	 * @var        string the name of the content the chain resides in
	 */
	protected $contentName;
	
	/**
	 * @var        Response the response after the dispatch call
	 */
	protected $response;
	
	/**
	 * dispatch the request
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com> 
	 */
	public function execute($arguments = null, $outputType = null, $requestMethod = null)
	{
		$context = Context::getInstance();
		
		$controller = $context->getController();
		$controller->setParameter('send_response', false);
		
		if(!($arguments instanceof DataHolder)) {
			$arguments = $this->createDataHolder(array(DataHolder::SOURCE_PARAMETERS => $arguments));
		}
		
		$this->response = $controller->dispatch(null, $controller->createExecutionContainer($this->contentName, $this->chainName, $arguments, $outputType, $requestMethod));
	}
	
	/**
	 * assert that the response has a given tag
	 * 
	 * @see the documentation of PHPUnit's assertTag()
	 * 
	 * @param      array the matcher describing the tag
	 * @param      string an optional message
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function assertResponseHasTag($matcher, $message = '', $isHtml = true)
	{
		
		$this->assertTag($matcher, $this->response->getContent(), $message, $isHtml);
	}
	
	
	/**
	 * assert that the response does not have a given tag
	 * 
	 * @see the documentation of PHPUnit's assertTag()
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function assertResponseHasNotTag($matcher, $message = '', $isHtml = true)
	{
		$this->assertNotTag($matcher, $this->response->getContent(), $message, $isHtml);
	}
}

?>
