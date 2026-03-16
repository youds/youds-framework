<?php
namespace YoudsFramework\Config;


// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Parser parses XML files using XmlParser, but returns
 * old-style ValueHolders.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @deprecated Superseded by XmlParser, will be removed in Youds Framework 1.1
 *
 * @version    $Id$
 */
class Parser
{
	/**
	 * @var        string The encoding of the \DOMDocument
	 */
	protected $encoding = 'utf-8';
	
	/**
	 * @var        string The filesystem path to the configuration file.
	 */
	protected $config = '';
	
	/**
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      array  An associative array of validation information.
	 *
	 * @return     ValueHolder The data handlers use to perform tasks.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function parse($config, $validationFile = null)
	{
		
		// copy path in case convertEncoding() needs to complain about a missing ICONV extension
		$this->config = $config;
		
		$parser = new XmlParser($config, Config::get('core.environment'), null);
		
		$validation = array(
			XmlParser::STEP_TRANSFORMATIONS_BEFORE => array(),
			XmlParser::STEP_TRANSFORMATIONS_AFTER => array(
				XmlParser::VALIDATION_TYPE_XMLSCHEMA => array(),
			),
		);
		if($validationFile !== null) {
			$validation[XmlParser::STEP_TRANSFORMATIONS_AFTER][XmlParser::VALIDATION_TYPE_XMLSCHEMA][] = $validationFile;
		}
		$doc = $parser->execute(array(), $validation);
		
		// remember encoding for convertEncoding()
		$this->encoding = strtolower($doc->encoding);
		
		$rootRes = new ValueHolder();
		
		if($doc->documentElement) {
			$this->parseNodes(array($doc->documentElement), $rootRes);
		}
		
		return $rootRes;
	}

	/**
	 * Iterates through a list of nodes and stores to each node in the
	 * ValueHolder
	 *
	 * @param      mixed An array or an object that can be iterated over
	 * @param      XmlValueHolder The storage for the info from the nodes
	 * @param      bool Whether this list is the singular form of the parent node
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	protected function parseNodes($nodes, ValueHolder $parentVh, $isSingular = false)
	{
		foreach($nodes as $node) {
			if($node->nodeType == XML_ELEMENT_NODE && (!$node->namespaceURI || $node->namespaceURI == XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ENVELOPE)) {
				$vh = new ValueHolder();
				$nodeName = $this->convertEncoding($node->localName);
				$vh->setName($nodeName);
				$parentVh->addChildren($nodeName, $vh);

				foreach($node->attributes as $attribute) {
					if((!$attribute->namespaceURI || $attribute->namespaceURI == XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ENVELOPE)) {
						$vh->setAttribute($this->convertEncoding($attribute->localName), $this->convertEncoding($attribute->nodeValue));
					}
				}

				// there are no child nodes so we set the node text contents as the value for the valueholder
				if($node->getElementsByTagName('*')->length == 0) {
					$vh->setValue($this->convertEncoding($node->nodeValue));
				}

				if($node->hasChildNodes()) {
					$this->parseNodes($node->childNodes, $vh);
				}
			}
		}
	}
	
	/**
	 * Handle encoding for a value, i.e. translate from UTF-8 if necessary.
	 *
	 * @param      string A UTF-8 string value from the DomDocument.
	 *
	 * @return     string A value in the correct encoding of the parsed document.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	protected function convertEncoding($value)
	{
		if($this->encoding == 'utf-8') {
			return $value;
		} elseif($this->encoding == 'iso-8859-1') {
			return utf8_decode($value);
		} elseif(function_exists('iconv')) {
			return iconv('UTF-8', $this->encoding, $value);
		} else {
			throw new Parse('No iconv content available, configuration file "' . $this->config . '" with input encoding "' . $this->encoding . '" cannot be parsed.');
		}
	}
}

?>
