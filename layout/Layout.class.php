<?php
namespace YoudsFramework;
use YoudsFramework\Request\DataHolder;
use YoudsFramework\Request\Console;
use YoudsFramework\Request\Web;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * A layout represents the presentation layer of an chain. Output can be
 * customized by supplying attributes, which a template can manipulate and
 * display.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage layout
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
abstract class Layout
{
	/**
	 */
	const NONE = null;

	/**
	 * @var        ExecutionContainer This layout's execution container.
	 */
	protected $container = null;

	/**
	 * @var        Context The Context instance this Layout belongs to.
	 */
	protected $context = null;

	/**
	 * @var        array An array of defined layers.
	 */
	protected $layers = array();

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @param      DataHolder The chain's request data holder.
	 *
	 * @return     ExecutionContainer An array of forwarding information in
	 *                                     case a forward should occur, or null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	abstract function execute(Console|Web $rd);

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the execution container for this chain.
	 *
	 * @return     ExecutionContainer This chain's execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getContainer()
	{
		return $this->container;
	}

	/**
	 * Retrieve the Response instance for this Layout.
	 *
	 * @return     Response The Response instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public final function getResponse()
	{
		return $this->container->getResponse();
	}

	/**
	 * Initialize this layout.
	 *
	 * @param      ExecutionContainer This Layout's execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(ExecutionContainer $container)
	{
		$this->container = $container;

		$this->context = $container->getContext();
	}

	/**
	 * Create a new template layer object.
	 *
	 * This will automatically set the name of the layer, the current content, the
	 * current Layout Nameas the template, and the output type name.
	 *
	 * @param      string The class name of the TemplateLayer implementation.
	 * @param      string The name of the layer.
	 * @param      mixed  An optional name of the non-default renderer to use, or
	 *                    an Renderer instance to use.
	 *
	 * @return     TemplateLayer A template layer instance.
	 *
	 * @author     	David Zülke <dz@bitxtender.com>
	 * @author		Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function createLayer($class, $name, $renderer = null)
	{
		$class = 'YoudsFramework\Layout\\' . $class;
		$layer = new $class();
		if(!is_subclass_of($layer, 'YoudsFramework\Layout\TemplateLayer')) {
			throw new Exceptions\Layout('Class "' . $class . '" is not a subclass of TemplateLayer');
		}
		$layer->initialize($this->context, 
			array(
				'name' => $name,
				'content' => $this->container->getLayoutContentName(),
				'template' => $this->container->getLayoutName(),
				'output_type' => $this->container->getOutputType()->getName()
		));
		if($renderer instanceof Renderer) {
			$layer->setRenderer($renderer);
		} else {
			$layer->setRenderer($this->container->getOutputType()->getRenderer());
		}
		return $layer;
	}

	/**
	 * Append a layer to the list of layers.
	 *
	 * If no reference layer is given, the layer will be added to the end of the
	 * list.
	 *
	 * @param      TemplateLayer The layer to insert.
	 * @param      TemplateLayer An optional other layer to insert after.
	 *
	 * @return     TemplateLayer The template layer that was inserted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function appendLayer(Layout\TemplateLayer $layer, ?Layout\TemplateLayer $otherLayer = null)
	{
		if($otherLayer !== null && !in_array($otherLayer, $this->layers, true)) {
			throw new Exceptions\Layout('Layer "' . $otherLayer->getName() . '" not in list');
		}

		if(($pos = array_search($layer, $this->layers, true)) !== false) {
			// given layer is already in the list, so we remove it first
			array_splice($this->layers, $pos, 1);
		}

		if($otherLayer === null) {
			$dest = count($this->layers);
		} elseif($otherLayer === $layer) {
			$dest = $pos;
		} else {
			$dest = array_search($otherLayer, $this->layers, true) + 1;
		}
		array_splice($this->layers, $dest, 0, array($layer));

		return $layer;
	}

	/**
	 * Prepend a layer to the list of layers.
	 *
	 * If no reference layer is given, the layer will be added to the beginning of
	 * the list.
	 *
	 * @param      TemplateLayer The layer to insert.
	 * @param      TemplateLayer An optional other layer to insert before.
	 *
	 * @return     TemplateLayer The template layer that was inserted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function prependLayer(TemplateLayer $layer, ?TemplateLayer $otherLayer = null)
	{
		if($otherLayer !== null && !in_array($otherLayer, $this->layers, true)) {
			throw new Layout('Layer "' . $otherLayer->getName() . '" not in list');
		}

		if(($pos = array_search($layer, $this->layers, true)) !== false) {
			// given layer is already in the list, so we remove it first
			array_splice($this->layers, $pos, 1);
		}

		if($otherLayer === null) {
			$dest = 0;
		} elseif($otherLayer === $layer) {
			$dest = $pos;
		} else {
			$dest = array_search($otherLayer, $this->layers, true);
		}
		array_splice($this->layers, $dest, 0, array($layer));

		return $layer;
	}

	/**
	 * Remove a layer from the list.
	 *
	 * @param      TemplateLayer The layer to remove.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function removeLayer(TemplateLayer $layer)
	{
		if(($pos = array_search($layer, $this->layers, true)) === false) {
			throw new Layout('Layer "' . $layer->getName() . '" not in list');
		}
		array_splice($this->layers, $pos, 1);
	}

	/**
	 * Remove all layers from the list.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearLayers()
	{
		$this->layers = array();
	}

	/**
	 * Retrieve a layer from the list.
	 *
	 * @param      string The name of the layer.
	 *
	 * @return     TemplateLayer The layer instance, or null if not found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLayer($name)
	{
		foreach($this->layers as $layer) {
			if($name == $layer->getName()) {
				return $layer;
			}
		}
	}

	/**
	 * Get all layers from the list.
	 *
	 * @return     array An array of template layer instances.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLayers()
	{
		return $this->layers;
	}

	/**
	 * Load a pre-configured layout.
	 *
	 * If no Layout Name is given, the default layout will be used.
	 *
	 * @param      string The (optional) name of the layout.
	 *
	 * @return     array An array of parameters set for the layout.
	 *
	 * @throws     Exception If the layout doesn't exist.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function loadLayout($layoutName = null)
	{
		$layout = $this->container->getOutputType()->getLayout($layoutName);

		$this->clearLayers();

		foreach($layout['layers'] as $name => $layer) {
			$l = $this->createLayer($layer['class'], $name, $layer['renderer']);
			$l->setParameters($layer['parameters']);
			foreach($layer['slots'] as $slotName => $slot) {
				$l->setSlot($slotName, $this->createSlotContainer($slot['content'], $slot['chain'], $slot['parameters'], $slot['output_type'], $slot['request_method']));
			}
			$this->appendLayer($l);
		}

		return $layout['parameters'];
	}

	/**
	 * Creates a new container with the same output type and request method as
	 * this layout's container.
	 *
	 * This container will have a parameter called 'is_slot' set to true.
	 *
	 * @param      string The name of the content.
	 * @param      string The name of the chain.
	 * @param      mixed  An DataHolder instance with additional
	 *                    request arguments or an array of request parameters.
	 * @param      string Optional name of an initial output type to set.
	 * @param      string Optional name of the request method to be used in this
	 *                    container.
	 *
	 * @return     ExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @see        ExecutionContainer::createExecutionContainer()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function createSlotContainer($contentName, $chainName, $arguments = null, $outputType = null, $requestMethod = null)
	{

		if($arguments !== null && !($arguments instanceof DataHolder)) {
			$rdhc = 'YoudsFramework\\' . $this->context->getRequest()->getParameter('request_data_holder_class');
			$arguments = new $rdhc(array(DataHolder::SOURCE_PARAMETERS => $arguments));
		}
		$container = $this->container->createExecutionContainer($contentName, $chainName, $arguments, $outputType, $requestMethod);
		$container->setParameter('is_slot', true);

		// just in case it was carried over by Container::createExecutionContainer()
		$container->removeParameter('is_forward');
		return $container;
	}
	
	/**
	 * Creates a slot for use by $slots in template file
	 *
	 * @return boolean 
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function createSlot ($slotName, $contentName, $chainName, $arguments = null, $outputType = null, $requestMethod = null)
	{
		$this->getLayer('content')->setSlot($slotName,  $this->createSlotContainer(
            $contentName,  
            $chainName,    
            $arguments,    
			$outputType,   
            $requestMethod 
        )); 
		
		return true;
	}

	/**
	 * Creates a new container with the same output type and request method as
	 * this layout's container.
	 *
	 * This container will have a parameter called 'is_forward' set to true.
	 *
	 * @param      string The name of the content.
	 * @param      string The name of the chain.
	 * @param      mixed  An DataHolder instance with additional
	 *                    request arguments or an array of request parameters.
	 * @param      string Optional name of an initial output type to set.
	 * @param      string Optional name of the request method to be used in this
	 *                    container.
	 *
	 * @return     ExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @see        ExecutionContainer::createExecutionContainer()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function createForwardContainer($contentName, $chainName, $arguments = null, $outputType = null, $requestMethod = null)
	{
		if($arguments !== null) {
			if(!($arguments instanceof DataHolder)) {
				$rdhc = 'YoudsFramework\\' . $this->context->getRequest()->getParameter('request_data_holder_class');
				$arguments = new $rdhc(array(DataHolder::SOURCE_PARAMETERS => $arguments));
			}
		} else {
			// we carry over our container's arguments
			$arguments = $this->container->getArguments();
		}
		$container = $this->container->createExecutionContainer($contentName, $chainName, $arguments, $outputType, $requestMethod);
		$container->setParameter('is_forward', true);
		return $container;
	}
	
	/**
	 * Run another chain, elegantly
	 *
	 * @return ExecutionContainer A new execution container instance,
     *                                      fully initialized.
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function runChain ($content, $chain, $arguments = null, $outputType = null, $requestMethod = null)
	{
		return $this->createForwardContainer($content, $chain, $arguments, $outputType, $requestMethod);
	}

    /**
     * Retrieve the validators.
     *
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
    public function getValidationManager ()
    {
        return $this->getContainer()->getValidationManager();
    }

    /**
     * Retrieve the ValidationManager Error Messages
     *
     * @return     array Array of error messages
     *
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
    public function getValidationErrors()
    {
        $report = $this->getValidationManager()->getReport();
        $errors = [];

        foreach ($report->getErrorMessages() as $errorMessage):
            if ($errorMessage !== null && strlen($errorMessage) > 0)
                $errors[] = $errorMessage;
        endforeach;

        return $errors;


    }

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function clearAttributes()
	{
		$this->container->clearAttributes();
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getAttribute($name, $default = null)
	{
		return $this->container->getAttribute($name, null, $default);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getAttributeNames()
	{
		return $this->container->getAttributeNames();
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &getAttributes()
	{
		return $this->container->getAttributes();
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function hasAttribute($name)
	{
		return $this->container->hasAttribute($name);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function &removeAttribute($name)
	{
		return $this->container->removeAttribute($name);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttribute($name, $value)
	{
		$this->container->setAttribute($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function appendAttribute($name, $value)
	{
		$this->container->appendAttribute($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttributeByRef($name, &$value)
	{
		$this->container->setAttributeByRef($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function appendAttributeByRef($name, &$value)
	{
		$this->container->appendAttributeByRef($name, $value);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttributes(array $attributes)
	{
		$this->container->setAttributes($attributes);
	}

	/**
	 * @see        AttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setAttributesByRef(array &$attributes)
	{
		$this->container->setAttributesByRef($attributes);
	}
	
	/**
	 * Retrieve the controller.
	 *
	 * @return     Controller The current Controller implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getController()
	{
		return $this->getContext()->getController();
	}

	/**
	 * Retrieve a database connection from the database manager.
	 *
	 * This is a shortcut to manually getting a connection from an existing
	 * database implementation instance.
	 *
	 * If the core.use_database setting is off, this will return null.
	 *
	 * @param      name A database name.
	 *
	 * @return     mixed A database connection.
	 *
	 * @throws     Exceptions\Database If the requested database name 
	 *                                           does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getDatabaseConnection($name = null)
	{
		$this->getContext()->getDatabaseConnection($name);
	}

	/**
	 * Retrieve the database manager.
	 *
	 * @return     DatabaseManager The current DatabaseManager instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getDatabaseManager()
	{
		return $this->getContext()->getDatabaseManager();
	}
	
	/**
	 * Retrieve the LoggerManager
	 *
	 * @return     LoggerManager The current LoggerManager implementation 
	 *                                instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getLoggerManager()
	{
		return $this->getContext()->getLoggerManager();
	}
	
	
	/**
	 * Retrieve the name of this Context.
	 *
	 * @return     string A context name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getName()
	{
		return $this->getContext()->getName();
	}
	
	/**
	 * Retrieve the request.
	 *
	 * @return     Request The current Request implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getRequest()
	{
		return $this->getContext()->getRequest();
	}

	/**
	 * Retrieve the routing.
	 *
	 * @return     Routing The current Routing implementation instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getRouting()
	{
		return $this->getContext()->getRouting();
	}

	/**
	 * Retrieve the storage.
	 *
	 * @return     Storage The current Storage implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getStorage()
	{
		return $this->getContext()->getStorage();
	}

	/**
	 * Retrieve the translation manager.
	 *
	 * @return     TranslationManager The current TranslationManager
	 *                                     implementation instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getTranslationManager()
	{
		return $this->getContext()->getTranslationManager();
	}

	/**
	 * Retrieve the user.
	 *
	 * @return     User The current User implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 */
	public function getUser()
	{
		return $this->getContext()->getUser();
	}
	
	/**
	 * Retrieve the integration toolset
	 *
	 * @return     Application The current Application implementation instance.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getIntegrations ()
	{
		return $this->getContext()->getIntegrations();
	}
	
	/**
	 * Retrieve the generator.
	 *
	 * @return     Generator The current Form Generator implementation instance.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getGenerator ()
	{
		return $this->getContext()->getGenerator();
	}
	
	/**
	 * Returns true is ajax or else false
	 *
	 *  @return    boolean	True if ajax
	 *  @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function isAjax()
	{
		return $this->getContext()->getRequest()->getParameter('ajax');
	}
	
}

?>
