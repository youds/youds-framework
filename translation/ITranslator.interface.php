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
 * ITranslator defines the interface for different translator 
 * implementations (like gettext, XLIFF, ...)
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage translation
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
interface ITranslator
{
	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function getContext();

	/**
	 * Initialize this Translator.
	 *
	 * @param      Context The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function initialize(Context $context, array $parameters = array());

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
	 */
	public function translate($message, $domain, ?Locale $locale = null);

	/**
	 * This method gets called by the translation manager when the default locale
	 * has been changed.
	 *
	 * @param      Locale The new default locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 */
	public function localeChanged($newLocale);

}

?>
