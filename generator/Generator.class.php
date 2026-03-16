<?php
namespace YoudsFramework\Generator;
use YoudsFramework\Config;
use YoudsFramework\Context;
use YoudsFramework\Request\ParameterHolder;
use YoudsFramework\Renderer\Plain;
use YoudsFramework\Exceptions\Exception;


// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Generator provides access to generator facilities.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage generator
 *
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Generator extends ParameterHolder
{
	/**
	 * @var        GeneratorManager An GeneratorManager instance.
	 */
	protected $generatorManager = null;


	/**
	 * Retrieve the Generator Manager instance for this implementation.
	 *
	 * @return     GeneratorManager A Generator Manager instance.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getGeneratorManager()
	{
		return $this->generatorManager;
	}

	/**
	 * Initialize this Generator.
	 *
	 * @param      GeneratorManager The generator manager of this instance.
	 * @param      array                An associative array of initialization params.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initialising this Generator.
	 *
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function initialize(GeneratorManager $generatorManager, array $parameters = array())
	{
		$this->generatorManager = $generatorManager;

		$this->setParameters($parameters);
	}

	/**
	 * Process Generator Document
	 *
	 * @return array Processed Generator String
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	function process ($doc)
	{

		// setup vars
		$retVal = array('variables' => array(), 'attributes' => array());

        $pattern = '#<\[([\w\d_\-.:]+)[\w\d%&?!\"\'=|\/.\-\s{}\[\]():,\\\]*]>|<\[/([\w\d_\-.:]+)]>#';

		// fetch content blocks
		preg_match_all($pattern, $doc, $all, PREG_OFFSET_CAPTURE);

		for ($a = 0;$a < (count($all[0]) / 2);$a++):

			preg_match_all($pattern, $doc, $nested[$a], PREG_OFFSET_CAPTURE);
			if (count($nested[$a][0]) > 0):

				$extracts = $this->processDoc($doc, $nested[$a]);

				if (is_array($extracts) && count($extracts) > 0):
					$retVal['variables'] = array_merge($retVal['variables'], $extracts['variables']);
					$retVal['attributes'] = array_merge($retVal['attributes'], $extracts['attributes']);
				endif;

				// grab the doc
				if (isset($extracts['doc']))
					$doc = $extracts['doc'];
			endif;
		endfor;

        preg_match_all($pattern, $doc, $nested, PREG_OFFSET_CAPTURE);
        if (count($nested[0]) > 0):
            preg_match_all($pattern, $doc, $nested, PREG_OFFSET_CAPTURE);
            $retVal = $this->processDoc($doc, $nested);
            $doc = $retVal['doc'];
            $doc = preg_replace($pattern, '', $doc);
            unset($retVal['doc']);
        endif;

		// return formatted document
		return array('variables' => $retVal['variables'], 'content' => $doc, 'attributes' => $retVal['attributes']);

	}

	function processDoc ($doc, $nested) {

 		// setup vars
		$opens = array();
		$openTags = array();
		$openTagLevel = 0;

		// loop over matches (each entry either open or close tag)
		for ($i = 0; $i < count($nested[1]); $i++):

			// store open tags
			if (strlen($nested[1][$i][0]) > 0):

				// create open tag index
				$openCount = 0;
				if (isset($opens[$nested[1][$i][0]]) && count($opens[$nested[1][$i][0]]) > 0)
					$openCount = count($opens[$nested[1][$i][0]]);

				// record open tag
				$opens[$nested[1][$i][0]][$openCount] = $nested[0][$i];
			endif;

			// process tags
			if (substr($nested[0][$i][0], 0, 3) != '<[/'):

				// define variables
				$lastTag = $nested[1][$i][0];

				// increment open level
				$openTagLevel++;

				// store open tag
				$openTags[$openTagLevel] = array(
					'name' => $lastTag,
					'position' => $opens[$lastTag][count($opens[$lastTag]) - 1][1]
				);

			elseif (isset($openTags[$openTagLevel])):

				// variables
				$currentTag = $nested[2][$i][0];
				$closePosition = $nested[0][$i][1] + strlen($nested[0][$i][0]);
				$openPosition = $openTags[$openTagLevel]['position'];
				$blockLength = $closePosition - $openPosition;
				$beforeExtract = substr($doc, 0, $openPosition);
				$afterExtract = substr($doc, $closePosition);
				$processBlock = substr($doc, $openPosition, $blockLength);

				// extract block
				$extract = $this->processBlock(
					$currentTag,
					substr($doc, $openPosition, $blockLength)
				);

				// store values
				$extracts = array(
					'extractFromDoc' => $processBlock,
					'processed' => substr($doc, 0, $openPosition) . $extract['content'] . substr($doc, $openPosition + ($closePosition - $openPosition)),
					'content' => $extract['content'],
					'before' => $beforeExtract,
					'after' => $afterExtract,
					'openPosition' => $openPosition,
					'closePosition' => $openPosition + ($closePosition - $openPosition),
					'closePositionAfter' => $openPosition + strlen($extract['content']),
					'variables' => $extract['variables'],
					'attributes' => $extract['attributes']
				);
				$doc = $extracts['processed'];


				return array('doc' => $doc, 'extracts' => $extracts, 'nested' => $nested, 'variables' => $extracts['variables'], 'attributes' => $extracts['attributes']);


			endif;
		endfor;
	}

	/**
	 * Generator Content
	 *
	 * @return array 	Processed Template File
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	function processBlock ($block, $raw)
	{
		// first, match attributes
		preg_match_all('/([\w\d\_\-\.\[\]\:]+)\=\"([\w\d\!\%\&\?\-\_\|\/\.\'\:\,\s\{\}\[\]\=\\\]+)\"/', $raw, $attributeMatches);

		// format attributes for inclusion
		$attributes = array();
		for ($d = 0;$d < count($attributeMatches[1]);$d++):

			// get our 2 pieces of data
			$attributeName = $attributeMatches[1][$d];
			$attributeContent = $attributeMatches[2][$d];

			// check for array format
			if (strstr($attributeContent, '|'))
				$attributeContent = explode('|', $attributeContent);

			// check for valid json
			if (is_string($attributeContent) && @json_decode(str_replace('\'', '"', $attributeContent)))
				$attributeContent = json_decode(str_replace('\'', '"', $attributeContent));

			// check for array format in name
			if (strstr($attributeName, '[') && strstr($attributeName, ']')):
				$arrayPos = strpos($attributeName, '[');
				$arrayName = substr($attributeName, 0, $arrayPos);
				$arrayValue = str_replace('[', '', str_replace(']', '', substr($attributeName, $arrayPos)));

				// assign variable
				$attributes[$arrayName][] = $attributeContent;
				$$arrayName[] = $attributeContent; // variable variable

			else:

				// assign variables
				$attributes[$attributeName] = $attributeContent;
				$$attributeName = $attributeContent; // variable variable
			endif;

		endfor;


		// lazy pattern replacement
		$template = preg_replace('/\<\[([\w\d\_\-\.\:]+)[\w\d\%\&\?\!\"\'\=\|\/\.\-\s\{\}\[\]\(\)\:\,\\\]*\]\>|\<\[\/([\w\d\_\-\.\:]+)\]\>/', '', $raw);

		// finally, define primary content
		$$block = $template; // variable variable
		$inner = $template;
        $attr = $attributes;

        // get our required factories
        //$this->context = Context::getInstance();

        // next capture output of template
		$block = str_replace(':', '/', $block);
		$block = str_replace('.', '/', $block);

        // check for defaults content
        $templateFileDefaults = sprintf(
            '%s/%s.tmpl.php',
            Config::get('core.defaults_generator_dir'),
            $block
        );

		$templateFile = sprintf(
			'%s/%s.tmpl.php',
			Config::get('core.generator_dir'),
			$block
		);

        // overwrite with defaults dir if not present
        if (!is_file($templateFile) && is_file($templateFileDefaults)):
            $templateFile = $templateFileDefaults;
        endif;

		if (is_file($templateFile)):

            ob_start();
			include($templateFile);
			$content = ob_get_contents();
			ob_end_clean();

		else:
			throw new Exception('Could not find "' . $templateFile . '"');
		endif;

        /**
         * Plain Renderer Support
         */

        // replace values
        $contentShell = $content;
        $content = str_replace('{inner}', $template, $content);

        // replace attributes
        foreach ($attributes as $name => $attribute):

            // first, find where the value appears (for replacement)
            $position = strpos($contentShell, '{' . $name . '}');

            // now we need the length of the replacement section
            $innerLength = strlen($template);

            // determine the position of the inner section
            $start = strpos($contentShell, '{inner}');

            // determine the end of the inner section
            $end = $start + $innerLength;

            // identify the string sections
            $before = substr($content, 0, $start);
            $subsection = substr($content, $start, $end - $start);
            $after = substr($content, $end);

            // perform replacement in the subsection
            if (is_string($attribute)):
                $before = str_replace('{' . $name . '}', (string) $attribute, $before);
                $after = str_replace('{' . $name . '}', (string) $attribute, $after);
            endif;

            // reassemble the string
            $content = $before . $subsection . $after;

		endforeach;

		return array('variables' => get_defined_vars(), 'content' => $content, 'attributes' => $attributes);
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 * It is called during the startup() of the generator manager.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function startup()
	{
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     Exceptions\Generator If an error occurs while shutting
	 *                                           down this generator.
	 *
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	 public function shutdown() {

	 }

 	/**
 	 * Output Generator Content
 	 *
 	 * @return string
 	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
 	 */
 	function output ($block, $params = array(), $inner = NULL)
 	{
        // check for defaults content
        $templateFileDefaults = sprintf(
            '%s/%s.tmpl.php',
            Config::get('core.defaults_generator_dir'),
            $block
        );

        $templateFile = sprintf(
            '%s/%s.tmpl.php',
            Config::get('core.generator_dir'),
            $block
        );

        // overwrite with defaults dir if not present
        if (!is_file($templateFile) && is_file($templateFileDefaults)):
            $templateFile = $templateFileDefaults;
        endif;

        // params
        $attributes = $params;
        if (is_array($params)):
            foreach ($params as $key => $param):
                if (is_string($key))
                    $$key = $param;
            endforeach;
        endif;

        // process generator block
		if (is_readable($templateFile)):

			// grab the output
			ob_start();

			require($templateFile);

			$doc = ob_get_contents();
			ob_end_clean();
		else:
			throw new Exception ('Could not find template "' . $templateFile . '"');
		endif;

		// transform {matches}
		preg_match_all('/(\{([\w\d\-\>\\\.\(\)\[\]\,\/\#\%\&\?\$\!\:\;\_\=\s]+)\})/', $doc, $matches);
		foreach ($matches[2] as $match):

			// perform transformation
			$renderer = new Plain();
			$doc = $renderer->match($doc, $match);

		endforeach;

		return $doc;
 	}


	/**
	 * Fetch an array of Unicode Emojis
	 *
	 * @return array 	An array of Unicode Emojis
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getUnicodeEmojis ()
	{

		$emojis = new UnicodeEmoji();
		return $emojis->fetch();
	}
}

?>