<?php
namespace YoudsFramework\Testing;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Validator\ValidationArgument;
use YoudsFramework\Response\Web;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * ActionTestCase is the base class for all chain testcases and provides
 * the necessary assertions
 * 
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class ChainTestCase extends FragmentTestCase
{	
	/**
	 * @var        string the name of the resulting layout
	 */
	protected $layoutName;
	
	/**
	 * @var        string the name of the resulting layout's content
	 */
	protected $layoutContentName;

    /**
     * @var        mixed the result of the layout execution
     */
    protected $layoutResult;

    /**
     * @var        string the error messages produced by the validation manager
     */
    protected $errors;

    protected $contentName;

    /**
	 * Run the chain for this testcase
	 *  
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */ 
	protected function runAction()
	{
		$this->container->setActionInstance($this->createActionInstance());
		//$executionFilter = $this->createExecutionFilter();
		$this->container->initRequestData();
		list($this->layoutContentName, $this->layoutName) = $this->container->runAction();

	}

    /**
     *  runs the layout instance for this testcase
     *
     * @param      string the name of the output type to run the layout for
     *                    null for the default output type
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function runLayout($otName = null)
    {
        $this->container->setActionInstance($this->createActionInstance());
        $this->container->setOutputType($this->getContext()->getController()->getOutputType($otName));
        $this->container->setLayoutInstance($this->createLayoutInstance());
        $executionFilter = $this->createExecutionFilter();
        $this->layoutResult = $executionFilter->executeLayout($this->container);

        $report = $this->container->getValidationManager()->getReport();

        $errors = '';
        foreach ($report->getErrorMessages() as $errorMessage):
            if ($errorMessage !== null && strlen($errorMessage) > 0)
                $errors .= $errorMessage . ', ';
        endforeach;
        $this->errors = strlen($errors) > 0?substr($errors, 0, -2):null;
    }
	
	/**
	 * register the validators for this testcase
	 *  
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */ 
	protected function performValidation()
	{
		$this->container->setActionInstance($this->createActionInstance());
		$this->validationSuccess = $this->container->performValidation($this->container);
	}

    /**
     *  creates the layout instance for this testcase
     *
     * @return     Layout
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function createLayoutInstance()
    {
        $this->getContext()->getController()->initializeContent($this->contentName);
        $layoutName = $this->normalizeLayoutName($this->layoutName);
        $layoutInstance = $this->getContext()->getController()->createLayoutInstance($this->contentName, $this->chainName, 'Success');
        $layoutInstance->initialize($this->container);
        return $layoutInstance;
    }

	/**
	 * asserts that the layoutName is the expected value after runAction was called
	 * 
	 * @param      string the expected layoutname in short form ('Success' etc)
	 * @param      string an optional message to display if the test fails
	 * 
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com> 
	 */
	protected function assertLayoutNameEquals($expected, $message = 'Failed asserting that the layout\'s name is <%1$s>.')
	{
		$expected = $this->normalizeLayoutName($expected);

		$this->assertEquals($expected, $this->layoutName, sprintf($message, $expected));
	}
	
	/**
	 * asserts that the layout's contentname is the expected value after runAction was called
	 * 
	 * @param      string the expected contentName 
	 * @param      string an optional message to display if the test fails
	 * 
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com> 
	 */
	protected function assertLayoutContentNameEquals($expected, $message = 'Failed asserting that the layout\'s Core Content Directive is <%1$s>.')
	{
		$this->assertEquals($expected, $this->layoutContentName, sprintf($message, $expected));
	}
	
	/**
	 * asserts that the DefaultLayout is the expected 
	 * 
	 * @param     mixed A string containing the Layout Name associated with the
	 *                   chain.
	 *                   Or an array with the following indices:
	 *                   - The parent content of the layout that will be executed.
	 *                   - The layout that will be executed.
	 *
	 * @param      string an optional message to display if the test fails
	 * 
	 * @return     void
	 * 
	 * @see        \Core\Chains\DefaultContent\Static\JavaScript\Action::getDefaultLayoutName()
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com> 
	 */
	protected function assertDefaultLayout($expected, $message = 'Failed asserting that the defaultLayout is the expected value.')
	{
		$chainInstance = $this->createActionInstance();
		$this->assertEquals($expected, $chainInstance->getDefaultLayoutName(), $message);
	}
	
	/**
	 * assert that the chain handles the given request method
	 * 
	 * @param      string  the method name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertHandlesMethod($method, $acceptGeneric = true, $message = '')
	{
		$chainInstance = $this->createActionInstance();
		$constraint = new ConstraintActionHandlesMethod($chainInstance, $acceptGeneric);
		
		self::assertThat($method, $constraint, $message);
	}
	
	/**
	 * assert that the chain does not handle the given request method
	 * 
	 * @param      string  the method name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertNotHandlesMethod($method, $acceptGeneric = true, $message = '')
	{
		$chainInstance = $this->createActionInstance();
		$constraint = self::logicalNot(new ConstraintActionHandlesMethod($chainInstance, $acceptGeneric));
		
		self::assertThat($method, $constraint, $message);
	}
	
	/**
	 * assert that the chain is simple
	 * 
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertIsSimple($message = 'Failed asserting that the chain is simple.')
	{
		$chainInstance = $this->createActionInstance();
		$this->assertTrue($chainInstance->isSimple(), $message);
	}
	
	/**
	 * assert that the chain is not simple
	 * 
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertIsNotSimple($message = 'Failed asserting that the chain is not simple.')
	{
		$chainInstance = $this->createActionInstance();
		$this->assertFalse($chainInstance->isSimple(), $message);
	}

	/**
	 * asserts that the given argument has been touched by a validator
	 * 
	 * This does not imply that the validation failed or succeeded, just
	 * that a validator attempted to validate the given argument
	 * 
	 * @param      string the name of the argument
	 * @param      string the source of the argument
	 * @param      string an optional message 
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertValidatedArgument($argumentName, $source = DataHolder::SOURCE_PARAMETERS, $message = 'Failed asserting that the argument <%1$s> is validated.')
	{
		$report = $this->container->getValidationManager()->getReport();

		$this->assertTrue($report->isArgumentValidated(new ValidationArgument($argumentName, $source)), sprintf($message, $argumentName));
	}

    /**
     * Assert that there are no validation errors
     *
     * @author  Craig Fairhurst <craig.fairhurst@youds.com>
     */
    protected function assertNoValidationErrors($message = 'Failed asserting that there are no validation errors.')
    {
        $this->assertNull($this->errors, sprintf('Failed asserting that there are no validation errors.', $this->errors));
    }

	/**
	 * asserts that the given argument has failed the validation
	 * 
	 * @param      string the name of the argument
	 * @param      string the source of the argument
	 * @param      string an optional message 
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertFailedArgument($argumentName, $source = DataHolder::SOURCE_PARAMETERS, $message = 'Failed asserting that the argument <%1$s> is failed.')
	{
		$report = $this->container->getValidationManager()->getReport();
		$this->assertTrue($report->isArgumentFailed(new ValidationArgument($argumentName, $source)), sprintf($message, $argumentName));
	}

	/**
	 * Asserts that the given argument has succeeded the validation
	 * 
	 * @param      string the name of the argument
	 * @param      string the source of the argument
	 * @param      string an optional message 
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 */
	protected function assertSucceededArgument($argumentName, $source = DataHolder::SOURCE_PARAMETERS, $message = 'Failed asserting that the argument <%1$s> is succeeded.')
	{
		$report = $this->container->getValidationManager()->getReport();
		$success = $report->isArgumentValidated(new ValidationArgument($argumentName, $source)) && ! $report->isArgumentFailed(new ValidationArgument($argumentName, $source));
		$this->assertTrue($success, sprintf($message, $argumentName));
	}

    /**
     * assert that the layout handles the given output type
     *
     * @param      string  the output type name
     * @param      boolean true if the generic 'execute' method should be accepted as handled
     * @param      string  an optional message to display if the test fails
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertHandlesOutputType($method, $acceptGeneric = false, $message = '')
    {
        $layoutInstance = $this->createLayoutInstance();
        $constraint = new ConstraintLayoutHandlesOutputType($layoutInstance, $acceptGeneric);

        self::assertThat($method, $constraint, $message);
    }

    /**
     * assert that the layout does not handle the given output type
     *
     * @param      string  the output type name
     * @param      boolean true if the generic 'execute' method should be accepted as handled
     * @param      string  an optional message to display if the test fails
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertNotHandlesOutputType($method, $acceptGeneric = false, $message = '')
    {
        $layoutInstance = $this->createLayoutInstance();
        $constraint = self::logicalNot(new ConstraintLayoutHandlesOutputType($layoutInstance, $acceptGeneric));

        self::assertThat($method, $constraint, $message);
    }

    /**
     * assert that the response contains a redirect
     *
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutRedirects($message = 'Failed asserting that the layout redirects')
    {
        $response = $this->container->getResponse();
        try {
            $this->assertTrue($response->hasRedirect(), $message);
        } catch (Exception $e) {
            $this->fail($message);
        }
    }

    /**
     * assert that the response contains no redirect
     *
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutRedirectsNot($message = 'Failed asserting that the layout does not redirect')
    {
        $response = $this->container->getResponse();
        try {
            $this->assertFalse($response->hasRedirect(), $message);
        } catch (Exception $e) {
            $this->fail($message);
        }
    }

    /**
     * assert that the response contains the expected redirect
     *
     * @param      mixed  the expected redirect
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutRedirectsTo($expected, $message = 'Failed asserting that the layout redirects to the given target.')
    {
        $response = $this->container->getResponse();
        try {
            $this->assertEquals($expected, $response->getRedirect(), $message);
        } catch (Exception $e) {
            $this->fail($message);
        }
    }

    /**
     * Assert that the layout sets the given content type.
     *
     * this assertion only works on Response\Web or subclasses
     *
     * @param      string the expected content type
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutSetsContentType($expected, $message = 'Failed asserting that the layout sets the content type "%1$s".')
    {
        $response = $this->container->getResponse();

        if(!($response instanceof Response\Web)) {
            $this->fail(sprintf($message . ' (response is not an Response\Web)', $expected));
        }
        $this->assertEquals($expected, $response->getContentType(), sprintf($message, $expected));
    }

    /**
     * Assert that the layout sets the given header with the given value.
     *
     * this response only works on Response\Web and subclasses
     *
     * @param      string the name of the expected header
     * @param      string the value of the expected header
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutSetsHeader($expected, $expectedValue = null, $message = 'Failed asserting that the layout sets a header named <%1$s> with the value <%2$s>')
    {
        $response = $this->container->getResponse();

        if(!($response instanceof Response\Web)) {
            $this->fail(sprintf($message . ' (response is not an Response\Web)', $expected));
        }
        $this->assertEquals($expectedValue, $response->getHttpHeader($expected), sprintf($message, $expected, $expectedValue));
    }

    /**
     * Assert that the layout sets the given cookie with the given value.<y></y>
     *
     * this response only works on Response\Web and subclasses
     *
     * @param      string the name of the expected cookie
     * @param      string the value of the expected header
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutSetsCookie($expected, $expectedValue = null, $message = 'Failed asserting that the layout sets a cookie named <%1$s> with a value of <%2$s>')
    {
        $response = $this->container->getResponse();

        if(!($response instanceof Response\Web)) {
            $this->fail(sprintf($message . ' (response is not an Response\Web)', $expected, var_export($expectedValue, true)));
        }
        $this->assertEquals($expectedValue, $response->getCookie($expected), sprintf($message, $expected, var_export($expectedValue, true)));
    }

    /**
     * assert that the response has the given http status
     *
     * this assertion only works on Response\Web or subclasses
     *
     * @param      string the expected http status
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutResponseHasHTTPStatus($expected, $message = 'Failed asserting that the response status is %1$s.')
    {
        $response = $this->container->getResponse();

        if(!($response instanceof Web)) {
            $this->fail(sprintf($message . ' (response is not an Response\Web)', $expected));
        }
        $this->assertEquals($expected, $response->getHttpStatusCode(), sprintf($message, $expected));
    }

    /**
     * assert that the response has the given content
     *
     * @param      mixed the expected content
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutResponseHasContent($expected, $message = 'Failed asserting that the response has content <%1$s>.')
    {
        $response = $this->container->getResponse();
        $this->assertEquals($expected, $response->getContent(), sprintf($message, $expected));
    }

    /**
     * assert that the layout result has the given content
     *
     * @param      mixed the expected content
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutResultEquals($expected, $message = 'Failed asserting the expected layout result.')
    {
        $this->assertEquals($expected, $this->layoutResult, sprintf($message, $expected));
    }

    /**
     * assert that the layout forwards to the given content/action
     *
     * @param      string the expected Core Content Directive
     * @param      string the expected chain name
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertLayoutForwards($expectedModule, $expectedAction, $message = 'Failed asserting that the layout forwards to "%1$s" "%2$s".')
    {
        if(!($this->layoutResult instanceof ExecutionContainer)) {
            $this->fail(sprintf($message, $expectedModule, $expectedAction));
        }
        $this->assertEquals($expectedModule, $this->layoutResult->getContentName());
        $this->assertEquals(Toolkit::canonicalName($expectedAction), $this->layoutResult->getChainName());
    }

    /**
     * assert that the layout has the  given layer
     *
     * @param      string the expected layer name
     * @param      string the message to emit on failure
     *
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
     */
    protected function assertHasLayer($expectedLayer, $message = 'Failed asserting that the layout contains the layer "%1$s".')
    {
        $layoutInstance = $this->container->getLayoutInstance();
        $layer = $layoutInstance->getLayer($expectedLayer);

        if(null === $layer) {
            $this->fail(sprintf($message, $expectedLayer));
        }
    }

    /**
     * assert that the layout has the  given layer
     *
     * @param      string the expected layer name
     * @param      string the message to emit on failure
     *
     * @author     David Zülke <david.zuelke@bitextender.com>
     */
    protected function assertNotHasLayer($expectedLayer, $message = '')
    {
        $layoutInstance = $this->container->getLayoutInstance();
        $layer = $layoutInstance->getLayer($expectedLayer);

        if(null !== $layer) {
            $this->fail('Failed asserting that the layout does not contain the layer.');
        }
    }
}

?>
