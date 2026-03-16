<?php
namespace YoudsFramework\Config;
use YoudsFramework\Util\Inflector;
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
 * Extended \DOMElement class with several convenience enhancements.
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
class XmlDomElement extends \DOMElement implements \IteratorAggregate
{
	/**
	 * __toString() magic method, returns the element value.
	 *
	 * @see        XmlDomElement::getValue()
	 *
	 * @return     string The element value.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function __toString()
	{
		return $this->getValue();
	}
	
	/**
	 * Returns the element name.
	 *
	 * @return     string The element name.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getName()
	{
		// what to return here? name with prefix? no.
		// but... element name, or with ns prefix?
		return $this->nodeName;
	}
	
	/**
	 * Returns the element value.
	 *
	 * @return     string The element value.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getValue()
	{
		// TODO: or textContent?
		// trimmed or not? in utf-8 or native encoding?
		// I'd really say we only support utf-8 for the new api
		return $this->nodeValue;
	}
	
	/**
	 * Returns the literal value. By default, that means whitespace is trimmed,
	 * boolean literals ("on", "yes", "true", "no", "off", "false") are converted
	 * and configuration directives ("%core.app_dir%") are expanded.
	 *
	 * Takes attributes {http://www.w3.org/XML/1998/namespace}space and
	 * {http://framework.youds.com/xml/config/global/envelope}literalize into account
	 * when computing the literal value. This way, users can control the trimming
	 * and the literalization of values.
	 * 
	 * AEP-100 has a list of all the conversion rules that apply.
	 *
	 * @return     mixed The element content converted according to the rules
	 *                   defined in AEP-100.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getLiteralValue()
	{
		$value = $this->getValue();
		// XML specifies [\x9\xA\xD\x20] as whitespace
		// trim strips more than that
		// no problem though, because these other chars aren't legal in XML
		$trimmedValue = trim($value);
		
		$preserveWhitespace = $this->getAttributeNS(XmlParser::NAMESPACE_XML_1998, 'space') == 'preserve';
		$literalize = Toolkit::literalize($this->getAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ENVELOPE_LATEST, 'literalize')) !== false;
		
		if($literalize) {
			if($preserveWhitespace && ($trimmedValue === '' || $value != $trimmedValue)) {
				// we must preserve whitespace, and there is leading or trailing whitespace in the original value, so we won't run Toolkit::literalize(), which trims the input and then converts "true" to a boolean and so forth
				// however, we should still expand possible occurrences of config directives
				$value = Toolkit::expandDirectives($value);
			} else {
				// no need to preserve whitespace, or no leading/trailing whitespace, which means we can expand "true", "false" and so forth using Toolkit::literalize()
				$value = Toolkit::literalize($trimmedValue);
			}
		} elseif(!$preserveWhitespace) {
			$value = $trimmedValue;
			if($value === '') {
				// with or without literalize, an empty string must be converted to NULL if xml:space is default (see ticket #1203 and AEP-100)
				$value = null;
			}
		}
		
		return $value;
	}
	
	/**
	 * Returns an iterator for the child nodes.
	 *
	 * @return     Iterator An iterator.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getIterator(): \Traversable
	{
		// should only pull elements from the default ns
		$prefix = $this->ownerDocument->getDefaultNamespacePrefix();
		if($prefix) {
			return $this->ownerDocument->getXpath()->query(sprintf('child::%s:*', $prefix), $this);
		} else {
			return $this->ownerDocument->getXpath()->query('child::*', $this);
		}
	}
	
	/**
	 * Retrieve singular form of given element name.
	 * This does special splitting only of the last part of the name if the name
	 * of the element contains hyphens, underscores or dots.
	 *
	 * @param      string The element name to singularize.
	 *
	 * @return     string The singularized element name.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 */
	protected function singularize($name)
	{
		// TODO: shouldn't this be static?
		$names = preg_split('#([_\-\.])#', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
		$names[count($names) - 1] = Inflector::singularize(end($names));
		return implode('', $names);
	}
	
	/**
	 * Convenience method to retrieve child elements of the given name.
	 * Accepts singular or plural forms of the name, and will detect and handle
	 * parent containers with plural names properly.
	 *
	 * @param      string The name of the element(s) to check for.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     \DOMNodeList A list of the child elements.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function get($name, $namespaceUri = null)
	{
		return $this->getChildren($name, $namespaceUri, true);
	}
	
	/**
	 * Convenience method to check if there are child elements of the given name.
	 * Accepts singular or plural forms of the name, and will detect and handle
	 * parent containers with plural names properly.
	 *
	 * @param      string The name of the element(s) to check for.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     bool True if one or more child elements with the given name
	 *                  exist, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function has($name, $namespaceUri = null)
	{
		return $this->hasChildren($name, $namespaceUri, true);
	}
	
	/**
	 * Count the number of child elements with a given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 * @param      bool   Whether or not to apply automatic singular/plural
	 *                    handling that skips plural container elements.
	 *
	 * @return     int The number of child elements with the given name.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function countChildren($name, $namespaceUri = null, $pluralMagic = false)
	{
		// if arg is null, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		
		// init our vars
		$query = '';
		$singularName = null;
		
		// tag our element, because older libxmls will mess things up otherwise
		// http://trac.agavi.org/ticket/1039
		$marker = uniqid('', true);
		$this->setAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ANNOTATIONS_LATEST, 'framework_annotations_latest:marker', $marker);
		
		if($pluralMagic) {
			// we always assume that we either get plural names, or the singular of the singular is not different from the singular :)
			$singularName = $this->singularize($name);
			if($namespaceUri) {
				$query = 'count(child::*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../@framework_annotations_latest:marker = "%4$s"]) + count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@framework_annotations_latest:marker = "%4$s"]/*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../../@framework_annotations_latest:marker = "%4$s"])';
			} else {
				$query = 'count(%1$s[../@framework_annotations_latest:marker = "%4$s"]/%2$s[../../@framework_annotations_latest:marker = "%4$s"]) + count(%2$s[../@framework_annotations_latest:marker = "%4$s"])';
			}
		} else {
			if($namespaceUri) {
				$query = 'count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@framework_annotations_latest:marker = "%4$s"])';
			} else {
				$query = 'count(%1$s[../@framework_annotations_latest:marker = "%4$s"])';
			}
		}
		
		$retVal = (int)$this->ownerDocument->getXpath()->evaluate(sprintf($query, $name, $singularName, $namespaceUri, $marker), $this);
		
		$this->removeAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ANNOTATIONS_LATEST, 'framework_annotations_latest:marker');
		
		return $retVal;
	}
	
	/**
	 * Determine whether there is at least one instance of a child element with a
	 * given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 * @param      bool   Whether or not to apply automatic singular/plural
	 *                    handling that skips plural container elements.
	 *
	 * @return     bool True if one or more child elements with the given name
	 *                  exist, false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function hasChildren($name, $namespaceUri = null, $pluralMagic = false)
	{
		return $this->countChildren($name, $namespaceUri, $pluralMagic) !== 0;
	}
	
	/**
	 * Retrieve all children with the given element name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 * @param      bool   Whether or not to apply automatic singular/plural
	 *                    handling that skips plural container elements.
	 *
	 * @return     \DOMNodeList A list of the child elements.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getChildren($name, $namespaceUri = null, $pluralMagic = false)
	{
		// if arg is null, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		
		// init our vars
		$query = '';
		$singularName = null;
		
		// tag our element, because libxml will mess things up otherwise
		$marker = uniqid('', true);
		$this->setAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ANNOTATIONS_LATEST, 'framework_annotations_latest:marker', $marker);
		
		if($pluralMagic) {
			// we always assume that we either get plural names, or the singular of the singular is not different from the singular :)
			$singularName = $this->singularize($name);
			if($namespaceUri) {
				$query = 'child::*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../@framework_annotations_latest:marker = "%4$s"] | child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@framework_annotations_latest:marker = "%4$s"]/*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../../@framework_annotations_latest:marker = "%4$s"]';
			} else {
				$query = '%1$s[../@framework_annotations_latest:marker = "%4$s"]/%2$s[../../@framework_annotations_latest:marker = "%4$s"] | %2$s[../@framework_annotations_latest:marker = "%4$s"]';
			}
		} else {
			if($namespaceUri) {
				$query = 'child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@framework_annotations_latest:marker = "%4$s"]';
			} else {
				$query = '%1$s[../@framework_annotations_latest:marker = "%4$s"]';
			}
		}
		
		$retVal = $this->ownerDocument->getXpath()->query(sprintf($query, $name, $singularName, $namespaceUri, $marker), $this);
		
		$this->removeAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ANNOTATIONS_LATEST, 'framework_annotations_latest:marker');
		
		return $retVal;
	}
	
	/**
	 * Determine whether this element has a particular child element. This method
	 * succeeds only when there is exactly one child element with the given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     bool True if there is exactly one instance of an element with
	 *                  the given name; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function hasChild($name, $namespaceUri = null)
	{
		return $this->getChild($name, $namespaceUri) !== null;
	}
	
	/**
	 * Return a single child element with a given name.
	 * Only returns anything if there is exactly one child of this name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     \DOMElement The child element, or null if none exists.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getChild($name, $namespaceUri = null)
	{
		// if arg is null, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		
		// tag our element, because libxml will mess things up otherwise
		$marker = uniqid('', true);
		$this->setAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ANNOTATIONS_LATEST, 'framework_annotations_latest:marker', $marker);
		
		if($namespaceUri) {
			$query = 'self::node()[count(child::*[local-name() = "%1$s" and namespace-uri() = "%2$s" and ../@framework_annotations_latest:marker = "%3$s"]) = 1]/*[local-name() = "%1$s" and namespace-uri() = "%2$s" and ../@framework_annotations_latest:marker = "%3$s"]';
		} else {
			$query = 'self::node()[count(child::%1$s[../@framework_annotations_latest:marker = "%3$s"]) = 1]/%1$s[../@framework_annotations_latest:marker = "%3$s"]';
		}
		
		$retVal = $this->ownerDocument->getXpath()->query(sprintf($query, $name, $namespaceUri, $marker), $this)->item(0);
		
		$this->removeAttributeNS(XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ANNOTATIONS_LATEST, 'framework_annotations_latest:marker');
		
		return $retVal;
	}
	
	/**
	 * Retrieve an attribute value.
	 * Unlike \DOMElement::getAttribute(), this method accepts an optional default
	 * return value.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null or the given default.
	 *
	 * @see        \DOMElement::getAttribute()
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getAttribute($name, $default = null): string
	{
		$retVal = parent::getAttribute($name);
		
		// getAttribute returns '' when the attribute doesn't exist, but any
		// null-ish value is probably unacceptable anyway
		if($retVal == null) {
			$retVal = $default;
		}
		
		// force string
		if ($retVal == null)
			$retVal = '';
			
		return $retVal;
	}
	
	/**
	 * Retrieve a namespaced attribute value.
	 * Unlike \DOMElement::getAttributeNS(), this method accepts an optional
	 * default return value.
	 *
	 * @param      string A namespace URI.
	 * @param      string An attribute name.
	 * @param      mixed  A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null or the given default.
	 *
	 * @see        \DOMElement::getAttributeNS()
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getAttributeNS($namespaceUri, $localName, $default = null): string
	{
		$retVal = parent::getAttributeNS($namespaceUri, $localName);
		
		if($retVal === null) {
			$retVal = $default;
		}
		
		return $retVal;
	}
	
	/**
	 * Retrieve all attributes of the element that are in no namespace.
	 *
	 * @return     array An associative array of attribute names and values.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getAttributes()
	{
		return $this->getAttributesNS('');
	}
	
	/**
	 * Retrieve all attributes of the element that are in the given namespace.
	 *
	 * @return     array An associative array of attribute names and values.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getAttributesNS($namespaceUri)
	{
		$retVal = array();
		
		foreach($this->ownerDocument->getXpath()->query(sprintf('@*[namespace-uri() = "%s"]', $namespaceUri), $this) as $attribute) {
			$retVal[$attribute->localName] = $attribute->nodeValue;
		}
		
		return $retVal;
	}
	
	/**
	 * Check whether or not the element has Youds Framework parameters as children.
	 *
	 * @return     bool True, if there are parameters, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function hasParameters()
	{
		if($this->ownerDocument->isConfiguration()) {
			return $this->has('parameters', XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ENVELOPE_LATEST);
		}
		
		return false;
	}
	
	/**
	 * Retrieve all of the Youds Framework parameter elements associated with this
	 * element.
	 *
	 * @param      array An array of existing parameters.
	 *
	 * @return     array The complete array of parameters.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getParameters(array $existing = array())
	{
		$result = $existing;
		$offset = 0;
		
		if($this->ownerDocument->isConfiguration()) {
			$elements = $this->get('parameters', XmlParser::NAMESPACE_YOUDS_FRAMEWORK_ENVELOPE_LATEST);
			
			foreach($elements as $element) {
				$key = null;
				if(!$element->hasAttribute('name')) {
					$result[$key = $offset++] = null;
				} else {
					$key = $element->getAttribute('name');
				}
				
				if($element->hasParameters()) {
					$result[$key] = isset($result[$key]) && is_array($result[$key]) ? $result[$key] : array();
					$result[$key] = $element->getParameters($result[$key]);
				} else {
					$result[$key] = $element->getLiteralValue();
				}
			}
		}
		
		return $result;
	}
}

?>
