<?php
	
/**
 * Developer Tools for outputting data
 *
 * @author Craig Fairhurst <craig.fairhurst@youds.com>
 */
function dump () {
	$backtrace = debug_backtrace();
	$args = func_get_args();
	if (count($args) > 0):
    if (php_sapi_name() != 'cli') echo "<div style=\"padding:10px 11px;\"><pre>";
        $callingClass = isset($backtrace[1]['class']) ? $backtrace[1]['class'] : '';
        $callingFunction = isset($backtrace[1]['function']) && $backtrace[1]['function'] != 'include' && $backtrace[1]['function'] != 'require'? ($callingClass ? $callingClass . '::' : '') . $backtrace[1]['function'] . '() ' : '';
        echo '# Debug function called from ' . $callingFunction . $backtrace[0]['file'] . ' at line <strong>' . $backtrace[0]['line'] . '</strong>' . PHP_EOL;
        //echo stackTrace();

		// fetch file
		$lines = file($backtrace[0]['file']);
		
		// validate parameters
		preg_match('/dump\((.+)\)/', $lines[$backtrace[0]['line'] - 1], $matches);
		if (isset($matches[1])):
            preg_match('/\(.+(,).+\)/', $matches[1], $match);

            $uniqueId = uniqid();
			if (isset($match[0]))
				$matched = str_replace($match[0], str_replace(',', '<|' . $uniqueId . '|>', $match[0]), $matches[1]);
			else
				$matched = $matches[1];

            $varNames = explode(',', $matched);

            foreach ($varNames as $key => $varName):
				$varNames[$key] = str_replace('<|' . $uniqueId . '|>', ',', $varName);
			endforeach;
		else:
			
			// parameters on one or more lines
			$a = 1;$i = 0;
			while (!strstr($lines[$backtrace[0]['line'] - $a], 'dump')):
				$varNames[$i] = str_replace(',', '', trim($lines[$backtrace[0]['line'] - $a]));
				$a++;$i++;
			endwhile;
			if (strstr($lines[$backtrace[0]['line'] - $a], '$'))
				$varNames[$i] = trim(str_replace('dump', '', str_replace('(', '', str_replace(',', '', $lines[$backtrace[0]['line'] - $a]))));
				
			if (isset($varNames))
				$varNames = array_reverse($varNames);
		endif;
		
		echo PHP_EOL;
		foreach (func_get_args() as $i => $arg):

            if (isset($varNames[$i])):

				// remove whitespace
				$varNames[$i] = trim($varNames[$i]);
			
				// if variable name is known
				if (strstr($varNames[$i], '$') || str_contains($varNames[$i], '::')):
					printf(php_sapi_name() != 'cli'?'<u>%s</u>':'%s', trim($varNames[$i]));
					echo PHP_EOL;
				endif;
			endif;
			var_dump($arg);
			echo PHP_EOL;
		endforeach;
		if (php_sapi_name() != 'cli') echo "</pre></div>";
		else echo '# END' . PHP_EOL . PHP_EOL;
	endif;

}


function vdump () {
	$args = func_get_args();
	$args[] = '__vdump';
	call_user_func_array('dump', $args);
}

function stackTrace() {
    $stack = debug_backtrace();
    $output = '';

    $stackLen = count($stack);
    for ($i = 1; $i < $stackLen; $i++) {
        $entry = $stack[$i];

        $func = $entry['function'] . '(';
        $argsLen = count($entry['args']);
        for ($j = 0; $j < $argsLen; $j++) {
            $my_entry = $entry['args'][$j];
            if (is_string($my_entry)) {
                $func .= $my_entry;
            }
            if ($j < $argsLen - 1) $func .= ', ';
        }
        $func .= ')';

        $entry_file = 'NO_FILE';
        if (array_key_exists('file', $entry)) {
            $entry_file = $entry['file'];               
        }
        $entry_line = 'NO_LINE';
        if (array_key_exists('line', $entry)) {
            $entry_line = $entry['line'];
        }           
        $output .= ' - ' . $entry_file . ':' . $entry_line . ' - ' . $func . '<br />';
    }
    return $output;
}


/**
 * Checks for a fatal error, the workaround for set_error_handler not working on fatal errors.
 */
function errorHandler()
{
	// get last error
    $error = error_get_last();

	// check for cli or continue
	if (isset($error['message']) && strlen($error['message']) > 0): 
		if (php_sapi_name() == 'cli'):
			$output = $error['message'];
		else:
			if (!YoudsFramework\Config::get('core.environment') || substr(YoudsFramework\Config::get('core.environment'), 0, 3) == 'dev'):
				
				if (strstr($error['message'], ':')):
					$prepend = substr($error['message'], 0, strpos($error['message'], ':'));
					$noPrepend = substr($error['message'], strpos($error['message'], ':') + 2);
					if (strpos($noPrepend, ' in ') > 0)
						$message = substr($noPrepend, 0, strrpos($noPrepend, ' in '));
					else
						$message = $noPrepend;
				else:
					$prepend = 'Error';
					
					$message = $error['message'];
				endif;
				
				// line number
				$lineNumber = $error['line'];
				if (isset($noPrepend) && strpos($noPrepend, 'Stack trace:') > 0):
					$stackTrace = substr($noPrepend, strrpos($noPrepend, 'Stack trace:'));
					$stackTrace = substr($stackTrace, 0, -9);
				else:
					$stackTrace = NULL;
				endif;
				$file = $error['file'];
				
				// check for @ errors
				$displayError = true;
				$fp = @fopen($file, 'r');
				if ($fp):
					$line = 0;
					while (($buffer = fgets($fp, 4096)) !== false):
						$line++; 
						if ($line == $lineNumber):
							if (strstr($buffer, '@' . $prepend))
								$displayError = false;
						endif;
					endwhile;
				endif;
				
				if ($displayError)
					echo sprintf('<pre><strong>%s:</strong> %s in file %s on line <strong>%s</strong> <br />%s</pre>', $prepend, $message, $file, $lineNumber, $stackTrace);
			endif;
		endif;
	endif;
}

if (php_sapi_name() != 'cli')
	ini_set('display_errors', 'off');
register_shutdown_function('errorHandler');

error_reporting(E_ALL);

?>
