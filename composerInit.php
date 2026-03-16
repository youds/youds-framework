<?php

class ComposerLoaderShim {
	protected $triggerClasses = array(
		'Config' => true,
		'' => true,
		'Autoloader' => true,
		'Inflector' => true,
		'ArrayPathDefinition' => true,
		'VirtualArrayPath' => true,
		'ParameterHolder' => true,
		'Cache' => true,
		'Exception' => true,
		'Exceptions\Autoload' => true,
		'Exceptions\Cache' => true,
		'Exceptions\Configuration' => true,
		'Exceptions\Unreadable' => true,
		'Exceptions\Parse' => true,
		'Toolkit' => true,
	);
	
	public function trigger($className) {
		if(!empty($this->triggerClasses[$className])) {
			require_once(__DIR__ . '/youds-framework.php');
		}
	}
}

spl_autoload_register(array(new ComposerLoaderShim(), 'trigger'));

?>
