<?php
namespace YoudsFramework\Config;
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
 * Extended \DOMDocument class with several convenience enhancements.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class XmlDomDocument extends \DOMDocument
{
	/**
	 * @var        string Default namespace used by several convenience methods in
	 *                    other node classes to access/retrieve elements.
	 */
	protected $defaultNamespaceUri = '';
	
	/**
	 * @var        string XPath prefix of the default namespace defined above.
	 */
	protected $defaultNamespacePrefix = '';
	
	/**
	 * @var        \DOMXPath A \DOMXPath instance for this document.
	 */
	protected $xpath = null;
	
	/**
	 * @var        array A map of \DOM classes and extended Youds Framework implementations.
	 */
	protected $nodeClassMap = array(
		'\DOMAttr'                  => 'Config\XmlDomAttr',
		'\DOMCharacterData'         => 'Config\XmlDomCharacterData',
		'\DOMComment'               => 'Config\XmlDomComment',
		// yes, even \DOMDocument, so we don't get back a vanilla \DOMDocument when doing $doc->documentElement etc
		'\DOMDocument'              => 'Config\XmlDomDocument',
		'\DOMDocumentFragment'      => 'Config\XmlDomDocumentFragment',
		'\DOMDocumentType'          => 'Config\XmlDomDocumentType',
		'\DOMElement'               => 'Config\XmlDomElement',
		'\DOMEntity'                => 'Config\XmlDomEntity',
		'\DOMEntityReference'       => 'Config\XmlDomEntityReference',
		'\DOMNode'                  => 'Config\XmlDomNode',
		// '\DOMNotation'              => 'XmlDomNotation',
		'\DOMProcessingInstruction' => 'Config\XmlDomProcessingInstruction',
		'\DOMText'                  => 'Config\XmlDomText',
	);
	
	/**
	 * The constructor.
	 * Will auto-register Youds Framework \DOM node classes and create an XPath instance.
	 *
	 * @param      string The XML version.
	 * @param      string The XML encoding.
	 *
	 * @see        \DOMDocument::__construct()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct($version = "1.0", $encoding = "UTF-8")
	{
		parent::__construct($version, $encoding);
		
		foreach($this->nodeClassMap as $domClass => $yfClass) {
			$this->registerNodeClass('\\' . trim($domClass, '\\'), 'YoudsFramework\\' . $yfClass);
		}
		
		$this->xpath = new \DOMXPath($this);
	}
	
	/**
	 * Load XML from a file.
	 *
	 * @param      string The path to the XML document.
	 * @param      int    Bitwise OR of the libxml option constants.
	 *
	 * @return     bool True of the operation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function load($filename, $options = 0): bool
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::load($filename, $options);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);

			throw new Exception(
				sprintf(
					'Error%s occurred while parsing the document (%s): ' . "\n\n%s",
					count($errors) > 1 ? 's' : '',
                    $filename,
                    implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		$this->xpath = new \DOMXPath($this);
		
		if($this->isConfiguration()) {
			XmlParser::registerNamespaces($this);
		}
		
		return $result;
	}
	
	/**
	 * Load XML from a string.
	 *
	 * @param      string The string containing the XML.
	 * @param      int    Bitwise OR of the libxml option constants.
	 *
	 * @return     bool True of the operation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function loadXml($source, $options = 0): bool
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::loadXML($source, $options);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new \Exceptions\DOM(
				sprintf(
					'Error%s occurred while parsing the document: ' . "\n\n%s",
					count($errors) > 1 ? 's' : '',
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		$this->xpath = new \DOMXPath($this);
		
		if($this->isConfiguration()) {
			XmlParser::registerNamespaces($this);
		}
		
		return $result;
	}
	
	/**
	 * Substitutes XIncludes in a \DOMDocument object.
	 *
	 * @param      int Bitwise OR of the libxml option constants.
	 *
	 * @return     int The number of XIncludes in the document.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function xinclude($options = 0): int|false
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::xinclude($options);
		
		if(libxml_get_last_error() !== false) {
			$throw = false;
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				if($error->level != LIBXML_ERR_WARNING) {
					$throw = true;
				}
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			if($throw) {
				libxml_use_internal_errors($luie);
				throw new \Exceptions\DOM(
					sprintf(
						'Error%s occurred while resolving XInclude directives: ' . "\n\n%s", 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Import a node into the current document.
	 *
	 * @param      \DOMNode The node to import.
	 * @param      bool    Whether or not to recursively import the node's
	 *                     subtree.
	 *
	 * @return     mixed The copied node, or false if it cannot be copied.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function importNode(\DOMNode $node, $deep = NULL)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::importNode($node, $deep);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new \Exceptions\DOM(
				sprintf(
					'Error%s occurred while importing a new node "%s": ' . "\n\n%s",
					count($errors) > 1 ? 's' : '', 
					$node->nodeName,
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Validate a document based on a schema.
	 *
	 * @param      string The path to the schema.
	 *
	 * @return     bool True if the validation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function schemaValidate($filename, $flags = 0): bool
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// gotta do the @ to suppress PHP warnings when the schema cannot be loaded or is invalid
		if(!$result = @parent::schemaValidate($filename)) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new \DOMException(
				sprintf(
					'XML Schema validation with "%s" failed due to the following error%s: ' . "\n\n%s", 
					$filename, 
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Validate a document based on a schema.
	 *
	 * @param      string A string containing the schema.
	 *
	 * @return     bool True if the validation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function schemaValidateSource($source, $flags = 0): bool
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// gotta do the @ to suppress PHP warnings when the schema cannot be loaded or is invalid
		if(!$result = @parent::schemaValidateSource($source)) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new \Exceptions\DOM(
				sprintf(
					'XML Schema validation failed due to the following error%s: ' . "\n\n%s", 
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Perform RELAX NG validation on the document.
	 *
	 * @param      string The path to the schema.
	 *
	 * @return     bool True if the validation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	public function relaxNGValidate($filename): bool
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// gotta do the @ to suppress PHP warnings when the schema cannot be loaded or is invalid
		if(!$result = @parent::relaxNGValidate($filename)) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new \Exceptions\DOM(
				sprintf(
					'RELAX NG validation with "%s" failed due to the following error%s: ' . "\n\n%s",
					$filename,
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Retrieve the \DOMXPath instance that is associated with this document.
	 *
	 * @return     \DOMXPath The \DOMXPath instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getXpath()
	{
		return $this->xpath;
	}
	
	/**
	 * Set a default namespace that should be used when accessing elements via
	 * convenience methods (such as magic get overload for children), and bind it
	 * to the given prefix for use in XPath expressions.
	 *
	 * @param      string A namespace URI
	 * @param      string An optional prefix, defaulting to "_default"
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function setDefaultNamespace($namespaceUri, $prefix = '_default')
	{
		$this->defaultNamespaceUri = $namespaceUri;
		$this->defaultNamespacePrefix = $prefix;
		
		$this->xpath->registerNamespace($prefix, $namespaceUri);
	}
	
	/**
	 * Retrieve the default namespace URI that will be used by node classes, if
	 * set, to conveniently retrieve child elements etc in some methods.
	 *
	 * @return     string A namespace URI.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDefaultNamespaceUri()
	{
		return $this->defaultNamespaceUri;
	}
	
	/**
	 * Retrieve the default namespace prefix that will be used by node classes, if
	 * set, to conveniently retrieve child elements etc via XPath. 
	 *
	 * @return     string A namespace prefix.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getDefaultNamespacePrefix()
	{
		return $this->defaultNamespacePrefix;
	}
	
	/**
	 * Check whether or not this is a standard Youds Framework configuration file, i.e. with
	 * a <configurations> and <configuration> envelope.
	 *
	 * @return     bool true, if it is an Youds Framework config structure, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function isConfiguration()
	{
		return XmlParser::isConfigurationDocument($this);
	}
	
	/**
	 * Retrieve the namespace of the Youds Framework envelope.
	 *
	 * @return     string A namespace URI, or null if it's not an Youds Framework config.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getEnvelopeNamespace()
	{
		if($this->isConfiguration()) {
			return $this->documentElement->namespaceURI;
		}
	}
	
	/**
	 * Method to retrieve a list of Youds Framework <configuration> elements regardless of
	 * their namespace.
	 *
	 * @return     array A list of XmlDomElement elements.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getConfigurationElements()
	{
		$retVal = array();
		if($this->isConfiguration()) {
			$yfNamespace = $this->getEnvelopeNamespace();
			foreach($this->documentElement->childNodes as $node) {
				if($node->nodeType == XML_ELEMENT_NODE && $node->localName == 'configuration' && $node->namespaceURI == $yfNamespace) {
					$retVal[] = $node;
				}
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Method to retrieve the Youds Framework <sandbox> element regardless of the namespace.
	 *
	 * @return     XmlDomElement The <sandbox> element, or null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function getSandbox()
	{
		if($this->isConfiguration()) {
			$yfNamespace = $this->getEnvelopeNamespace();
			
			foreach($this->documentElement->childNodes as $node) {
				if($node->nodeType == XML_ELEMENT_NODE && $node->localName == 'sandbox' && $node->namespaceURI == $yfNamespace) {
					return $node;
				}
			}
		}
	}
}

?>
