<?php
namespace YoudsFramework\Renderer;
use YoudsFramework\Context;
use YoudsFramework\Renderer;
use YoudsFramework\Config;
use YoudsFramework\FileTemplateLayer;
use YoudsFramework\TemplateLayer;
use YoudsFramework\Exceptions\Exception;



// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * A renderer produces the output as defined by a Layout
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage renderer
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Plain extends Renderer implements IRenderer
{
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.php';
	
	/**
	 * @var        TemplateLayer Temporary storage for the template layer,
	 *                                used during rendering.
	 */
	private $layer = null;
	
	/**
	 * @var        array Temporary storage for the template layer, used during
	 *                   rendering.
	 */
	private $attributes = null;
	
	/**
	 * @var        array Temporary storage for the template layer, used during
	 *                   rendering.
	 */
	private $slots = null;
	
	/**
	 * @var        array Temporary storage for additional assigns, used during
	 *                   rendering.
	 */
	private $moreAssigns = null;
	
	/**
	 * @var        array Temporary variable for use between methods in class
	 *                   
	 */
	private $for = null;
	
	/**
	 * Transformation variables
	 *
	 * @var string Used for transforming matches
	 */
	private $var = '';
	private $generatedRoutes = '';
	private $generatedContent = '';
	private $generatorAttributeKeys = '';
	private $inner = '';
	private $matchedArray = array();
	
	/**
	 * Render the presentation and return the result.
	 *
	 * @param      TemplateLayer The template layer to render.
	 * @param      array              The template variables.
	 * @param      array              The slots.
	 * @param      array              Associative array of additional assigns.
	 *
	 * @return     string A rendered result.
	 *
	 * @author    David Zülke <dz@bitxtender.com>
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function render($layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		// DO NOT USE VARIABLES IN HERE, THEY INTERFERE WITH TEMPLATE VARS
		$this->layer = $layer;
		$this->attributes = &$attributes;
		$this->slots = &$slots;
		$this->moreAssigns = &self::buildMoreAssigns($moreAssigns, $this->moreAssignNames);

		unset($layer, $attributes, $slots, $moreAssigns);
		
		if($this->extractVars) {
			extract($this->attributes, EXTR_REFS | EXTR_PREFIX_INVALID, '_');
		} else {
			${$this->varName} = &$this->attributes;
			foreach ($this->attributes as $attr => $data)
				$$attr = $data;
		}
		
		${$this->slotsVarName} = &$this->slots; 
		
		foreach($this->assigns as $name => $getter) {
			${$name} = $this->context->$getter();
		}
		unset($name, $getter);
				
		extract($this->moreAssigns, EXTR_REFS | EXTR_PREFIX_INVALID, '_');
		
		// store vars before execution
		$localScopeVarsBefore = get_defined_vars();
				
		// grab the output
		ob_start();
		
		require($this->layer->getResourceStreamIdentifier());
		
		$doc = ob_get_contents();
		ob_end_clean();
		
		// include YoudsFramework css and js
		if (strstr($doc, '</head>'))
			$doc = str_replace('</head>', '<link rel="stylesheet" src="yf/youdsframework.css" /><script src="yf/youdsframework.js"></script></head>', $doc);
		
		unset($this->layer, $this->attributes, $this->slots, $this->moreAssigns);
		
		// renderer: transform generator <[blocks]>
		$generator = $this->getContext()->getGenerator();
		$generatedContent = $generator->process($doc);
		if (isset($generatedContent['variables']))
            $generatorAttributeKeys = array_keys($generatedContent['variables']);
        else
            $generatorAttributeKeys = [];
		if (isset($generatedContent['variables']['attr']))
			$generatorAttributes = $generatedContent['variables']['attr'];
		else
			$generatorAttributes = NULL;

		$doc = $generatedContent['content'];

		// store data for transformation
		// possible replace patterns
		$this->var = ${$this->varName};
		$this->generatedRoutes = $this->getContext()->getRouting()->getRoutes();
		$this->generatedContent = $generatedContent;
		$this->generatorAttributeKeys = $generatorAttributeKeys;
		$this->inner = $inner;

		// transform 
		$doc = $this->process($doc, $this->getContext()); // if else for
			
		// transform {matches}
		preg_match_all('/(\{([\w\d\-\>\\\.\(\)\[\]\,\/\#\%\&\?\$\!\:\;\_\=\s]+)\})/', $doc, $matches);
		foreach ($matches[2] as $match):

			// perform transformation
			$doc = $this->match($doc, $match);
		endforeach;
				
		return $doc;
	}
	
	
	/**
	 * Process Statement
	 *
	 * @return string The parsed document
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function process ($doc) {
		
		// remove {}'s
        if (is_array($doc) && isset($doc['doc']))
            $doc = $doc['doc'];
		$doc = $this->match($doc, 'inner');

		// for
		if (strstr($doc, '{for:') || strstr($doc, '{ for:'))
			$doc = $this->processFor($doc);
			
		// if else
		if (strstr($doc, '{if:') || strstr($doc, '{ if:'))
			$doc = $this->processIfElse($doc);
		
		
		return $doc;
		
	}
	
	/**
	 * Process For Statements
	 *
	 * @return string The parsed document
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function processFor ($doc)
	{
		// {for: a as b? c} do this {/for}
		preg_match_all('/{(for):\s([\w\d\-]+)\s?(as)(\s[\w\d\-]+?)?(\s[\w\d\-]+?)\s?}([.\s\w\d\W]+?){\/for}/', $doc, $matches);
		
		if (isset($matches[2][0])):
			for ($i = 0;$i < count($matches[2]); $i++):

				// get matched data
				$matched = $this->getMatches($matches[2][$i]);
				
				// vanity vars
				$all = trim($matches[0][$i]);
				$array = $matched[$matches[2][$i]][0];
				$key = trim($matches[4][$i]);
				$as = trim($matches[5][$i]);
				$replace = $matches[6][$i];
				$for = '';
				
				// loop $array
				foreach ($array as $val => $value):
					if (strlen($key) > 0) 
						$for .= str_replace('{' . $as . '}', $value, str_replace('{' . $key . '}', $val, $replace));
					else
						$for .= str_replace('{' . $as . '}', $value, $replace);
										
					// store values
					$this->for = array('keyName' => $key, 'key' => $val, 'valueName' => $as, 'value' => $value);
					
					// if else
					if (strstr($for, '{if:') || strstr($for, '{ if:'))
						$for = $this->processIfElse($for);
									
					// remove values
					unset($this->for);
				endforeach;
				
				// do replace
				$doc = str_replace($all, $for, $doc);
			endfor;			
		endif;
		
		return $doc;
		
	}
	
	/**
	 * Process Condiditional If Else Block
	 * {if: a = b} do this {else} do that {/if}
	 *
	 * @return string Processed document
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function processIfElse ($doc)
	{
		
		// {if: a = b} do this {else} do that {/if}
		preg_match_all('/\{if:\s*([\w\d\-]+)\s*([!=><]{1,2})\s*([\w\d\-]+)\s?}([\w\d\-\!\>\<\&\\\.\(\)\#\'\"\[\]\,\/\:\;\_\=\s]+\s*)({else})?([\w\d\-\!\>\\\.\(\)\'\"\[\]\,\/\:\;\_\=\s]+\s*){\/if}/', $doc, $matches);

		if (isset($matches[0][0])):
			for ($i = 0;$i < count($matches[0]); $i++):
				
				// get matched data
				$matched = $this->getMatches($matches[1][$i]);
				
				// vanity vars
				$all = $matches[0][$i];
				if (isset($matched[$matches[1][$i]][0]))
					$left = $matched[$matches[1][$i]][0];
				else
					continue; // no replacement
				$operator = $matches[2][$i];
				$right = $matches[3][$i];
				$replace = $matches[4][$i];
				$else = $matches[6][$i];				
				
				// check if true
				switch ($operator):
					case '=':
					if ($left == $right):
						$doc = str_replace($all, $replace, $doc);
					elseif ($else):
						$doc = str_replace($all, $else, $doc);
					else:
						$doc = str_replace($all, '', $doc);
					endif;
					break;
					case '!=':
					if ($left != $right):
						$doc = str_replace($all, $replace, $doc);
					elseif ($else):
						$doc = str_replace($all, $else, $doc);
					else:
						$doc = str_replace($all, '', $doc);
					endif;
					break;
					case '<':
					if (is_numeric($left) && is_numeric($right) && $left < $right):
						$doc = str_replace($all, $replace, $doc);
					elseif ($else):
						$doc = str_replace($all, $else, $doc);
					else:
						$doc = str_replace($all, '', $doc);
					endif;
					break;
					case '>':
					if (is_numeric($left) && is_numeric($right) && $left > $right):
						$doc = str_replace($all, $replace, $doc);
					elseif ($else):
						$doc = str_replace($all, $else, $doc);
					else:
						$doc = str_replace($all, '', $doc);
					endif;
					break;
					case '<=':
					if (is_numeric($left) && is_numeric($right) && $left <= $right):
						$doc = str_replace($all, $replace, $doc);
					elseif ($else):
						$doc = str_replace($all, $else, $doc);
					else:
						$doc = str_replace($all, '', $doc);
					endif;
					break;
					case '>=':
					if (is_numeric($left) && is_numeric($right) && $left >= $right):
						$doc = str_replace($all, $replace, $doc);
					elseif ($else):
						$doc = str_replace($all, $else, $doc);
					else:
						$doc = str_replace($all, '', $doc);
					endif;
					break;
				endswitch;
			endfor;
		endif;
		
		return $doc;
	}
	
	/**
	 * Match nested arrays
	 *
	 * @return string	The processed document string
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function matchValue($as, $value, $replace) {
		if (is_array($value)):
			foreach ($value as $key => $val):
				if (is_array($val))
					$replace = $this->matchArrayValue($as . '.' . $key, $val, $replace);
				else
					$replace = str_replace('{' . $as . '.' . $key . '}', (is_string($val)?$val:''), $replace);
			endforeach;
		endif;

		return str_replace('{' . $as . '}', (is_string($value)?$value:''), $replace);


	}
	
	function match ($doc, $match) {

        // init vars
		$params = array();
        $context = Context::getInstance(Config::get('core.profile'));
		$matchedPattern = array();
		$match = (string) $match;
		
		// check for default value definition
		$matchedPattern[$match] = $match;
		$matchBefore = $match;
		if (strstr($match, ':') && preg_match('/^[.\s]+\:{1}[\w\d\.\s]*$/', $match)):
			$match = substr($match, 0, strpos($match, ':'));
		endif;
		
		// get matches
		$result = $this->getMatches($match, $context);
		
		// process matches, where no duplicate
		foreach ($result as $keyMatch => $replaceWith):
			if (count($replaceWith) > 1):
				$replaceValuePrev = $replaceWith[0];
				foreach ($replaceWith as $replaceValue):
					if ($replaceValuePrev != $replaceValue):
						throw new Exception(sprintf('Multiple renderer values found for "{%s}"', $keyMatch));
					endif;
				endforeach;
			endif;
			
			// do replace
			if ((count($replaceWith) == 1 || (isset($replaceWith[0]) && isset($replaceWith[1]) && $replaceWith[0] == $replaceWith[1])) && (is_string($replaceWith[0]) || is_numeric($replaceWith[0])))
				$doc = str_replace('{' . $keyMatch . '}', $replaceWith[0], $doc);
		endforeach;
		
		// use default value
		if (strstr($matchedPattern[$matchBefore], ':') && preg_match('/^([\w\d\:\s\[\]\.]+\:{1})([\w\:\d\.\s]*)$/', $matchedPattern[$matchBefore], $matched)):
			$default = $matched[2];
			$doc = str_replace('{' . $matchedPattern[$matchBefore] . '}', $default, $doc);
		endif;
		
		return $doc;
	}
	
	/**
	 * Get Matches Based On Params
	 *
	 * @return void
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	private function getMatches($match, $context = NULL)
	{
		// init vars
		$retVal = array();
		if ($context == NULL)
			$context = $this->getContext();
		
		// check for inner variable
		if ($match == 'inner'):
			$retVal[$match][] = $this->inner;
		endif;
		
		// check for standard variables
		if (isset($this->var[$match]) && (is_string($this->var[$match]) || is_numeric($this->var[$match]))):
			$retVal[$match][] =  $this->var[$match];			
		endif;
		
		// check for array definition
		if (strstr($match, '.')):
			$matchedArray = explode('.', $match);
		else:
			$matchedArray = array($match);
		endif;
		array_walk($matchedArray, function ($value, $key) use (&$matchedArray) {
			
			// strip default value
			if (is_string($value) && strstr($value, ':') && preg_match('/^(.+)\:{1}([\w\d\.\s]*)$/', $value, $matched))
				$matchedArray[$key] = $matched[1];
		});

		// standard variables as array 
		if (isset($matchedArray) && is_array($matchedArray) && isset($this->var[$matchedArray[0]])): 
			
			// work.with.many.itterations.of.arrays
			$key = 0;
			for ($replace = $this->var[$matchedArray[0]], $i = 1; $i < count($matchedArray); $i++):
				if (isset($replace[$matchedArray[$i]])):
					$replace = $replace[$matchedArray[$i]];
				endif;
				$key = $matchedArray[$i]; // for checking if array index is associative
			endfor;

			// standard
			if ((is_string($replace) || is_numeric($replace))):
				$retVal[$match][] = $replace;
				
			// check for nested numerical keys
			elseif (isset($replace[0]) && is_numeric($key)): 
				$retVal[$match][] = $replace[0];
			
			// matches values of type array
			elseif (is_array($replace)):
				$retVal[$match][] = $replace; 
			endif;
		endif;
				
		// array parameter
		if (preg_match('/\[.+\:\s?.+\]/', $match)):
			preg_match_all('/(([\w\d\-\_\.]+)\:\s?([\w\d\-\_\.]+))/', $match, $arrayValues);
			$array = array();
			if (isset($arrayValues[2]) && is_array($arrayValues[2])):
				for ($i = 0;$i < count($arrayValues[2]);$i++):
					$key = $arrayValues[2][$i];
					$value = $arrayValues[3][$i];
					$array[$key] = $value;
				endfor;
				
				preg_match('/([\w\d\.\_\-]+)\[[\w\d\:\.\_\-\s\,]+\]/', $match, $matched);
				if (isset($this->generatedRoutes[$matched[1]]))
					$retVal[$match][] = $context->getRouting()->gen($matched[1], $array);
			endif;
		endif;

		// check for route name
		if ($match == 'matched_route_name' || $match == 'matchedRouteName'):
			
			// get routes
		    $route_names_array = $context->getRequest()->getAttribute('matched_routes', 'org.framework.routing');
			
			// replace route
			if (isset($route_names_array[count($route_names_array) - 1])):
				$retVal[$match][] = $route_names_array[count($route_names_array) - 1];
			elseif (isset($route_names_array[0])):
				$retVal[$match][] = $route_names_array[0];
			endif;
		endif;
		
		// check for matched route
		if ($match == 'matched_route' || $match == 'matchedRoute'):
		    $route_names_array = $context->getRequest()->getAttribute('matched_routes', 'org.framework.routing');
			if (isset($route_names_array[count($route_names_array) - 1])):
				$retVal[$match][] = $context->getRouting()->gen($route_names_array[count($route_names_array) - 1]);
			elseif (isset($route_names_array[0])):
				$retVal[$match][] = $context->getRouting()->gen($route_names_array[0]);
			endif;
		endif;
			
		// check for generated routes	
		if (isset($this->generatedRoutes[$match])):
			$retVal[$match][] = $context->getRouting()->gen($match);
		endif;
		
		// check for base_href
		if ($match == 'base_href' || $match == 'baseHref'):
			if ($context->getName() == 'web')
				$retVal[$match][] = $context->getRouting()->getBaseHref();
		endif;
			
		// config
		if (strpos($match, '.') && $result = Config::get($match)):
			$retVal[$match][] = $result;
		endif;
		
		// objects
		if (strstr($match, '->') && preg_match('/.+\-\>.+\(.+\)/', $match)):
			$object = substr($match, 0, strpos($match, '->'));
			$methodParameters = $this->fetchMethodParameters(substr($match, strpos($match, '->') + 2));

			// loop variables
			$callback = array();
			$count = 0;
			
			// loop methods for object
			foreach ($methodParameters as $methods):
				if ($count == 0 && isset($$object))
					$result = call_user_func_array(array($$object, $methods['method']), $methods['parameters']);
				else if (count($methodParameters) - 1 >= $count && isset($$object))
					$result = call_user_func_array(array($result, $methods['method']), $methods['parameters']);

				// increment count var
				$count++;
			endforeach;
			
			// do replace
			if (isset($result) && is_string($result)):
				$retVal[$match][] = $result;
				unset($result);
			endif;
		endif;
		
		// if else for var		
		if (isset($this->for) && $this->for['keyName'] == $match):
			$retVal[$match][] = $this->for['key'];
		endif;
		if (isset($this->for) && $this->for['valueName'] == $match):
			$retVal[$match][] = $this->for['value'];
		endif;
		
		return $retVal;
		
	}
	
	/**
	 * Fetch methods from given string
	 *
	 * @return string	Method matched
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	function fetchMethodParameters ($string)
	{
		// setup variables
		$object = array();
		$count = 0;
		
		// pcre
		preg_match_all('/[\w\d]+\([\w\d\'\"\_\-\,\s\=\|\/\.\-\*\[\]\s\/\:\;\>]*\)/', $string, $matches);
		
		// loop matches
		foreach ($matches[0] as $match):
			
			// retrieve method
			$method = substr($match, 0, strpos($match, '('));
			$parameters = substr($match, strpos($match, '(') + 1, strpos($match, ')') - 1 - strpos($match, '('));
			$_parameters = $parameters; // save for later
			$parameters = preg_replace('/\[[\'\"]{1}([\w\d\W\D]+)[\'\"]{1}\]?/', '', $parameters);
			//$parameters = preg_replace('/[\'\"]\,?\s?/', '', $parameters);
			
			// remove abbreviations 
			$parameters = str_replace('"', '', str_replace('\'', '', $parameters));
			
			// check for comma-seperated parameters
			if (is_string($parameters) && strpos($parameters, ','))
				$parameters = explode(',', str_replace(', ', ',', $parameters));
			
			// convert to array
			if (!is_array($parameters))
				$parameters = array($parameters);
			
			// remove empty/invalid parameters
			$parametersLength = count($parameters);
			for ($i = 0; $parametersLength > $i; $i++):
				if (strlen($parameters[$i]) <= 0 || preg_match('/[\w\d\-\.\_]+\:\s?[\w\d\-\_\.]+/', $parameters[$i])):
					unset($parameters[$i]);
				endif;
			endfor;
			$parameters = array_values($parameters);
			
			// process arrays in parameters
			preg_match_all('/\[([\w\d\W\D\:]+)\]/', $_parameters, $arrays);
			if (isset($arrays[0][0])):
				preg_match_all('/(([\'|\"][\w\d]+[\'|\"]\s?\=\>\s?)?[\'|\"][\w\d\-\_]+[\'|\"])[\,|\]]/', $arrays[0][0], $arrayValues);
				if (isset($arrayValues[1]) && is_array($arrayValues[1])):
					foreach ($arrayValues[1] as $key => $value):
						
						// first process the string/statement
						$string = str_replace('"', '', str_replace('\'', '', $value));
						
						// next retain the data in a formatted array
						if (strstr($value, '=>'))
							$array[trim(substr($string, 0, strpos($string, '=>')))] = trim(substr($string, strpos($string, '=>') + 2));
						else
							$array[] = $string;
					endforeach;
					
					// assign to return value
					if (isset($array) && !empty($array))
						array_push($parameters, $array);
				endif;
			endif;
									
			// save for return
			$object[$count]['method'] = $method;
			$object[$count]['parameters'] = $parameters;
			
			// count for $object
			$count++;
		endforeach;
		return $object;
		
	}
}

?>
