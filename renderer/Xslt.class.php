<?php
namespace YoudsFramework\Renderer;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * XsltRenderer uses an XML Stylesheet Language Template to render the
 * given input (an XML document in $inner).
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage renderer
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Xslt extends Renderer implements IRenderer
{
	const ENVELOPE_XMLNS = 'http://framework.youds.com/xml/renderer/xslt/envelope';
	
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.xsl';
	
	/**
	 * Load an XML document from a string, return a \DOMDocument and return errors
	 * in case something went wrong.
	 *
	 * @param      string The XML source to load.
	 * @param      int    libxml option flags for loading.
	 *
	 * @return     \DOMDocument The parsed XML document.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function loadDomDocumentXml($source, $options = 0)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = new \DOMDocument();
		$loaded = @$result->loadXML($source, $options);
		
		if(libxml_get_last_error() !== false || !$loaded) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			
			if(!$errors) {
				$errors = array('Unknown error (document empty?)');
			}
			throw new \Exceptions\DOM(
				sprintf(
					'Error%s occurred while parsing the document: ' . "\n\n%s",
					count($errors) > 1 ? 's' : '',
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Load an XML document from a file, return a \DOMDocument and return errors in
	 * case something went wrong.
	 *
	 * @param      string The XML source to load.
	 * @param      int    libxml option flags for loading.
	 *
	 * @return     \DOMDocument The parsed XML document.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function loadDomDocument($source, $options = 0)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = new \DOMDocument();
		$result->load($source, $options);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			
			if(!$errors) {
				$errors = array('Unknown error (document empty?)');
			}
			throw new \Exceptions\DOM(
				sprintf(
					'Error%s occurred while parsing the document: ' . "\n\n%s",
					count($errors) > 1 ? 's' : '',
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function render(TemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		if($this->getParameter('envelope', true)) {
			if(!($moreAssigns['inner'] instanceof \DOMDocument)) {
				// plain text, load it as a document
				try {
					$inner = $this->loadDomDocumentXml($moreAssigns['inner']);
				} catch(\Exceptions\DOM $e) {
					throw new Render(sprintf("Unable to load input document for layer '%s'.\n\n%s", $layer->getName(), $e->getMessage()), 0, $e);
				}
			} else {
				$inner = $moreAssigns['inner'];
			}
			
			// construct envelope
			$doc = new \DOMDocument();
			$doc->appendChild($doc->createElementNS(self::ENVELOPE_XMLNS, 'envelope'));
			
			// inner content container
			$doc->documentElement->appendChild($innerWrapper = $doc->createElementNS(self::ENVELOPE_XMLNS, 'inner'));
			$innerWrapper->appendChild($doc->importNode($inner->documentElement, true));
			
			// slots container
			$slotsWrapper = $doc->createElementNS(self::ENVELOPE_XMLNS, 'slots');
			$doc->documentElement->appendChild($slotsWrapper);
			
			// flatten slots, iterate and wrap them each
			$flattenedSlots = ArrayPathDefinition::flatten($slots);
			foreach($flattenedSlots as $slotName => $slotContent) {
				if(!($slotContent instanceof \DOMDocument)) {
					try {
						$slot = $this->loadDomDocumentXml($slotContent);
					} catch(Exception $e) {
						throw new Render(sprintf("Unable to load contents for slot '%s'.\n\n%s", $slotName, $e->getMessage()), 0, $e);
					}
				} else {
					$slot = $slotContent;
				}
				
				$slotWrapper = $doc->createElementNS(self::ENVELOPE_XMLNS, 'slot');
				$slotWrapper->setAttribute('name', $slotName);
				$slotWrapper->appendChild($doc->importNode($slot->documentElement, true));
				
				$slotsWrapper->appendChild($slotWrapper);
			}
		} else {
			if(!($moreAssigns['inner'] instanceof \DOMDocument)) {
				// plain text, load it as a document
				$doc = $this->loadDomDocumentXml($moreAssigns['inner']);
			} else {
				$doc = $moreAssigns['inner'];
			}
			// This will pretty much never work, so we're not doing it. Users must enable the envelope feature to use slots.
			// Warning: XSLTProcessor::transformToXml() [xsltprocessor.transformtoxml]: Cannot create XPath expression (string contains both quote and double-quotes)
			// $flattenedSlots = ArrayPathDefinition::flatten($slots);
			// foreach($flattenedSlots as $slotName => $slotContent) {
			// 	if($slotContent instanceof \DOMDocument) {
			// 		$slotContent = $slotContent->saveXML();
			// 	}
			// 	$xsl->setParameter('', 'slot:' . $slotName, addslashes($slotContent));
			// }
		}
		
		try {
			$xslt = $this->loadDomDocument($layer->getResourceStreamIdentifier());
		} catch(\Exceptions\DOM $e) {
			throw new Render(sprintf("Unable to load template '%s'.\n\n%s", $layer->getResourceStreamIdentifier(), $e->getMessage()), 0, $e);
		}
		
		$xsl = new XSLTProcessor();
		$xsl->importStylesheet($xslt);
		foreach($attributes as $name => $attribute) {
			if(is_scalar($attribute) || (is_object($attribute) && method_exists($attribute, '__toString'))) {
				$xsl->setParameter('', $name, $attribute);
			}
		}
		
		return $xsl->transformToXML($doc);
	}
}

?>
