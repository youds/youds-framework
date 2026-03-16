<?php
namespace Defaults\Core\Common\Base;
use YoudsFramework\Action as Base;

class Action extends Base
{
	/*
		This is the base chain all your application's chains should extend.
		This way, you can easily inject new functionality into all of your chains.
		
		One example would be to extend the initialize() method and assign commonly
		used objects such as the request as protected class members.
		
		Another example would be a custom isSimple() method that returns true if the
		current container has the "is_slot" parameter set - that way, all chains
		run as a slot would automatically be switched to "simple" mode.
		
		Even if you don't need any of the above and this class remains empty, it is
		strongly recommended you keep it. There shall come the day where you are
		happy to have it this way ;)
		
		It is of course highly recommended that you change the names of any default
		base classes to carry a prefix and have an overall meaningful naming scheme.
		You can enable the usage of the respective custom template files via
		build.properties settings. Also, keep in mind that you can define templates
		for specific content in case you require this.
	*/
}

?>
