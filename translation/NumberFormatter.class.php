<?php
namespace YoudsFramework\Translation;
use YoudsFramework\Context;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * The number formatter will format numbers according to a given format
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage translation
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class NumberFormatter extends DecimalFormatter implements ITranslator
{
	/**
	 * @var        Context An Context instance.
	 */
	protected $context = null;

	/**
	 * @var        Locale The locale which should be used for formatting.
	 */
	protected $locale = null;

	/**
	 * @var        string The custom format supplied by the user (if any).
	 */
	protected $customFormat = null;

	/**
	 * @var        string The translation domain to translate the format (if any).
	 */
	protected $translationDomain = null;

	/**
	 * @see        ITranslator::getContext()
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Initialize this Translator.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->context = $context;
		if(!empty($parameters['rounding_mode'])) {
			$this->setRoundingMode($this->getRoundingModeFromString($parameters['rounding_mode']));
		}
		if(isset($parameters['translation_domain'])) {
			$this->translationDomain = $parameters['translation_domain'];
		}
		if(isset($parameters['format'])) {
			$this->customFormat = $parameters['format'];
			if(is_array($this->customFormat)) {
				// it's an array, so it contains the translations already, DOMAIN MUST NOT BE SET
				$this->translationDomain = null;
			} elseif($this->translationDomain === null) {
				// if the translation domain is not set and the format is not an array of per-locale strings then we don't have to delay parsing
				$this->setFormat($this->customFormat);
			}
		}
	}

	/**
	 * Translates a message into the defined language.
	 *
	 * @param      mixed       The message to be translated.
	 * @param      string      The domain of the message.
	 * @param      Locale The locale to which the message should be 
	 *                         translated.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function translate($message, $domain, ?Locale $locale = null)
	{
		if($locale) {
			$fn = clone $this;
			$fn->localeChanged($locale);
		} else {
			$fn = $this;
			$locale = $this->locale;
		}
		
		if($this->customFormat && $this->translationDomain) {
			if($fn === $this) {
				$fn = clone $this;
			}
			
			$td = $this->translationDomain . ($domain ? '.' . $domain : '');
			$format = $this->getContext()->getTranslationManager()->_($this->customFormat, $td, $locale);
			
			$fn->setFormat($format);
		}
		
		return $fn->formatNumber($message);
	}

	/**
	 * This method gets called by the translation manager when the default locale
	 * has been changed.
	 *
	 * @param      string The new default locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;
		
		$this->groupingSeparator = $this->locale->getNumberSymbolGroup();
		$this->decimalSeparator = $this->locale->getNumberSymbolDecimal();
		
		$format = $this->locale->getDecimalFormat('__default');
		
		if(is_array($this->customFormat)) {
			$format = Toolkit::getValueByKeyList($this->customFormat, Locale::getLookupPath($this->locale->getIdentifier()), $format);
		} elseif($this->customFormat) {
			$format = $this->customFormat;
		}
		
		$this->setFormat($format);
	}
}

?>
