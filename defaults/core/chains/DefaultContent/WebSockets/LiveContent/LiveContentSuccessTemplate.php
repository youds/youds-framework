class WebSockets {
	constructor() {
		
		// assign empty functions 
		this.action = {};
		this.action.forward = function () {};
		this.action.backward = function () {};
		
		return this.action
	}
	auth(token, username) {
		
		// values used on server-side
	  	this.token = token;
	  	this.username = username;
	}
	new (name, data) {
		
		// check for correct syntax
		if (name.indexOf(':') < 0)
			return 'Invalid module:chain provided';
		
		// assign vars
		this.module = name.substr(0, name.indexOf(':'));
		this.chain = name.substr(name.indexOf(':'));
		
		return this;
		
	}
	forward (func) {
		this.action.forward = func;
	}
	backward (func) {
		this.action.backward = func;
	}
	go () {
		this.action.forward();
	}
	undo () {
		this.action.backward();
	}
}

