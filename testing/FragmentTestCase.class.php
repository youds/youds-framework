<?php
namespace YoudsFramework\Testing;
use YoudsFramework\Layout;
use YoudsFramework\Context;
use YoudsFramework\Util\Toolkit;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * FragmentTestCase is the base class for all fragment tests and provides
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
abstract class FragmentTestCase extends PhpUnitTestCase implements IFragmentTestCase
{
	/**
	 * @var        string the name of the context to use, null for default context
	 */
	protected $contextName = null;
	
	/**
	 * @var        string the name of the chain to test
	 */
	protected $chainName;
	
	/**
	 * @var        string the name of the content 
	 */
	protected $contentName;
	
	/**
	 * @var        bool   the result of the validation process
	 */
	protected $validationSuccess;
	
	/**
	 * @var        ExecutionContainer the container to run the chain in
	 */
	protected $container;


	/**
	 * Constructs a test case with the given name.
	 *
	 * @param  string $name
	 * @param  array  $data
	 * @param  string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		//$this->setRunTestInSeparateProcess(true);
	}
	
	
	/**
	 * creates a new ExecutionContainer for each test
	 * 
	 * @return void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function setUp() :void
	{
		$this->container = $this->createExecutionContainer();
	}
	
	
	/**
	 * unsets the ExecutionContainer after each test
	 * 
	 * @return void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	public function tearDown() :void
	{
		$this->container = null;
	}
	
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
	
	/**
	 * normalizes a layoutname according to the configured rules
	 * 
	 * Please do not use this method, it exists only for internal 
	 * purposes and may be removed ASAP. You have been warned
	 * 
	 * @param      string the short layout name
	 * 
	 * @return     string the full layout name
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function normalizeLayoutName($shortName)
	{
		if($shortName !== Layout::NONE) {
			$shortName = Toolkit::evaluateModuleDirective(
				$this->contentName,
				'framework.layout.name',
				array(
					'chainName' => $this->chainName,
					'layoutName' => $shortName,
				)
			);
			$shortName = Toolkit::canonicalName($shortName);
		}
		
		return $shortName;
	}

	/**
	 * create an executionfilter for the test
	 * 
	 * the configured executionfilter class will be wrapped in a testing
	 * extension to provide advanced capabilities required for testing 
	 * only
	 * 
	 * @return     ExecutionFilter 
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function createExecutionFilter()
	{
		$effi = $this->getContext()->getFactoryInfo('execution_filter');

		$wrapper_class = 'YoudsFramework\\' . $effi['class'];

		//extend the original class to overwrite runAction, so that the containers request data is cloned
		if(!class_exists($wrapper_class)) {
			$code = sprintf('namespace YoudsFramework\Testing;
class %1$s extends %2$s
{
	protected $validationResult = null;
	
	public function executeLayout(ExecutionContainer $container)
	{
		$container->initRequestData();
		return parent::executeLayout($container);
	}
}',
			$wrapper_class,
			$effi['class']);

			eval($code);
		}

		// create a new execution container with the wrapped class
		$filter = new $wrapper_class();
		$filter->initialize($this->getContext(), $effi['parameters']);
		return $filter;
	}
    /**
     * create a requestDataHolder with the given arguments and type
     *
     * arguments need to be passed in the way {@see AgaviRequestDataHolder} accepts them
     *
     * array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('foo' => 'bar'))
     *
     * if no type is passed, the default for the configured request class will be used
     *
     * @param      array   a two-dimensional array with the arguments
     * @param      string  the subclass of AgaviRequestDataHolder to create
     *
     * @return     AgaviRequestDataHolder
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     * @since      1.0.0
     */
    protected function createRequestDataHolder(array $arguments = array(), $type = null)
    {
        if(null === $type) {
            $type = $this->getContext()->getRequest()->getParameter('request_data_holder_class', 'Request\DataHolder');
        }
        $type = 'YoudsFramework\\' . $type;
        $class = new $type($arguments);
        return $class;
    }

	/**
	 * create an ExecutionContainer for the test
	 * 
	 * the configured ExecutionContainer class will be wrapped in a testing
	 * extension to provide advanced capabilities required for testing 
	 * only
	 * 
	 * @return     \YoudsFramework\ExecutionContainer
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function createExecutionContainer($arguments = null, $outputType = null, $requestMethod = null)
	{
		$context = $this->getContext();

		$ecfi = $context->getFactoryInfo('execution_container');
		
		$wrapper_class = $ecfi['class'] . 'UnitTesting';

		// extend the original class to add a setter for the chain instance
		if(!class_exists($wrapper_class)) {
			$code = sprintf('namespace YoudsFramework;
class %1$s extends %2$s
{

	public function setActionInstance(Action $chain)
	{
		$this->chainInstance = $chain;
	}
	
	public function initRequestData()
	{
		parent::initRequestData();
	}
}',
			$wrapper_class,
			$ecfi['class']);

			eval($code);
		}
		
		$ecfi['class'] = $wrapper_class;
		$context->setFactoryInfo('execution_container', $ecfi);
		
		if(!($arguments instanceof DataHolder)) {
			$arguments = $this->createDataHolder(array(\YoudsFramework\Request\DataHolder::SOURCE_PARAMETERS => $arguments));
		}
		
		// create a new execution container with the wrapped class
		$container = $context->getController()->createExecutionContainer($this->contentName, $this->chainName, $arguments, $outputType, $requestMethod);
		
		return $container;
	}

	/**
	 * creates an Action instance and initializes it with this testcases
	 * container
	 * 
	 * @return     Action
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function createActionInstance()
	{
		$chainInstance = $this->getContext()->getController()->createActionInstance($this->contentName, $this->chainName);
		$chainInstance->initialize($this->container);
		return $chainInstance;
	}
	
	/**
	 * create a requestDataHolder with the given arguments and type
	 * 
	 * arguments need to be passed in the way {@see DataHolder} accepts them
	 * 
	 * array(DataHolder::SOURCE_PARAMETERS => array('foo' => 'bar'))
	 * 
	 * if no type is passed, the default for the configured request class will be used
	 * 
	 * @param      array   a two-dimensional array with the arguments
	 * @param      string  the subclass of DataHolder to create
	 * 
	 * @return     DataHolder
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function createDataHolder(array $arguments = array(), $type = null)
	{
		if(null === $type) {
			$type = '\\YoudsFramework\\' . $this->getContext()->getRequest()->getParameter('request_data_holder_class', 'DataHolder');
		}
		
		$class = new $type($arguments);
		return $class;
	}
	
	
	/**
	 * assert that the exectionContainer has a given attribute with the expected value
	 * 
	 * @param      mixed   the expected attribute value
	 * @param      string  the attribute name
	 * @param      string  the attribute namespace
	 * @param      string  an optional message to display if the test fails
	 * @param      float   $delta
	 * @param      integer $maxDepth
	 * @param      boolean $canonicalizeEol
	 * 
	 * @see        PHPUnit_Framework_Assert::assertEquals()
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertContainerAttributeEquals($expected, $attributeName, $namespace = null, $message = 'Failed asserting that the attribute <%1$s/%2$s> has the value <%3$s>', $delta = 0, $maxDepth = 10, $canonicalizeEol = false)
	{
		$this->assertEquals($expected, $this->container->getAttribute($attributeName, $namespace), sprintf($message, $namespace, $attributeName, $expected), $delta, $maxDepth, $canonicalizeEol);
	}
	
	/**
	 * assert that the exectionContainer has a given attribute 
	 * 
	 * @param      string  the attribute name
	 * @param      string  the attribute namespace
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertContainerAttributeExists($attributeName, $namespace = null, $message = 'Failed asserting that the container has an attribute named <%1$s/%2$s>.')
	{
		$this->assertTrue($this->container->hasAttribute($attributeName, $namespace), sprintf($message, $namespace, $attributeName));
	}
	
	/* --- container delegates --- */

	/**
	 * @see        ExcutionContainer::setOutputType()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setOutputType(\YoudsFramework\OutputType $outputType)
	{
		$this->container->setOutputType($outputType);
	}

	/**
	 * @see        Request::setRequestData()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setRequestData($rd)
	{
		$this->container->setRequestData($rd);
	}
	
	/**
	 * @see        ExcutionContainer::setArguments()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setArguments($rd)
	{
		$this->container->setArguments($rd);
	}

	/**
	 * @see        ExcutionContainer::setRequestMethod()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setRequestMethod($method)
	{
		$this->container->setRequestMethod($method);
	}

	/**
	 * @see        AttributeHolder::clearAttributes()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function clearAttributes()
	{
		$this->container->clearAttributes();
	}

	/**
	 * @see        AttributeHolder::getAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function &getAttribute($name, $default = null)
	{
		return $this->container->getAttribute($name, null, $default);
	}

	/**
	 * @see        AttributeHolder::getAttributeNames()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function getAttributeNames()
	{
		return $this->container->getAttributeNames();
	}

	/**
	 * @see        AttributeHolder::getAttributes()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function &getAttributes()
	{
		return $this->container->getAttributes();
	}

	/**
	 * @see        AttributeHolder::hasAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function hasAttribute($name)
	{
		return $this->container->hasAttribute($name);
	}

	/**
	 * @see        AttributeHolder::removeAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function &removeAttribute($name)
	{
		return $this->container->removeAttribute($name);
	}

	/**
	 * @see        AttributeHolder::setAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setAttribute($name, $value)
	{
		$this->container->setAttribute($name, $value);
	}

	/**
	 * @see        AttributeHolder::appendAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function appendAttribute($name, $value)
	{
		$this->container->appendAttribute($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setAttributeByRef($name, &$value)
	{
		$this->container->setAttributeByRef($name, $value);
	}

	/**
	 * @see        AttributeHolder::appendAttributeByRef()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function appendAttributeByRef($name, &$value)
	{
		$this->container->appendAttributeByRef($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributes()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setAttributes(array $attributes)
	{
		$this->container->setAttributes($attributes);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function setAttributesByRef(array &$attributes)
	{
		$this->container->setAttributesByRef($attributes);
	}
}

?>
