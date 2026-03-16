var moduleChain, i, element, elementType, dataValues, parsedData;
class LiveContent {
	constructor() {
		this.content = [];
		this.socket = {};
		if (typeof this.socket == 'undefined' || this.socket.hasOwnProperty('url') == false) {

			try {

				// open socket
				this.socket = new WebSocket('wss://01.youds.dev:8043');
				this.socket.onopen = function() {
					console.info('LiveContent Socket: Open');
					this.send('Authentication: "{token}" "{private}" Username: "{name}" Action: "auth" Data: false Value: false');
				}
				this.socket.onmessage = function(message){
					//console.info('LiveContent Socket: Received Input From Server', message);

					// check if we have a valid token
					if (message.data.indexOf('::') >= 0) {

						// if forward or backward
						var direction = message.data.substr(message.data.indexOf('::') + 2);
						direction = direction.substr(0, direction.indexOf(' '));

						// module:chain name
						moduleChain = message.data.substr(0, message.data.indexOf('::'));

						// handle data
						try {

							// grab json if valid
					        parsedData = JSON.parse(message.data.substr(message.data.indexOf(' ') + 1));
					    } catch (e) {

							// else grab string
					        parsedData = message.data.substr(message.data.indexOf(' ') + 1);
					    }

						if (parsedData !== null && typeof parsedData == 'object' && parsedData.length > 0) {

							// collect id values
							for (i = 0;i < parsedData.length;i++) {
								element = document.getElementById(parsedData[i]);
								if (typeof element != null) {

									// got element of type elementType
									elementType = element.tagName;

									// assign content
									switch (elementType) {
										case 'input':
										case 'select':

											// replace value
											document.getElementById(Object.keys(parsedData)[i]).value = parsedData[i];
											break;
										default:

											// all other elements
											document.getElementById(Object.keys(parsedData)[i]).innerHTML = parsedData[i];

									}

									// check for changes
									element.onchange = function () {};
								}
							}
						}

						// store data

						//console.log('...', window.ws.content, moduleChain);
						if (typeof window.ws.content[moduleChain] !== undefined) {
							window.ws.content[moduleChain].value = parsedData;

							// determine if we have a hook
							if (direction == 'hook')
								direction = 'forward';

							// perform action
							window.ws.content[moduleChain].returnValue = message.data;

							if (direction == 'forward')
								window.ws.content[moduleChain].forward(parsedData);
							else if (direction == 'backward')
								window.ws.content[moduleChain].backward(parsedData);
							else if (direction == 'callback')
								window.ws.content[moduleChain].callback(parsedData);
						}
					}

				}
				this.socket.onclose = function() {
					//console.log('--Socket Close----');
				}
			} catch (exception){

				//console.log('Error: ' + exception);
			}

		}

		// assign to global scope
		window.ws = this;

		return window.ws;
	}
	new (name) {

		// check for correct syntax
		if (name.indexOf(':') < 0)
			return 'Invalid module:chain provided';

		// assign object vars
		var module = name.substr(0, name.indexOf(':'));
		var chain = name.substr(name.indexOf(':') + 1);

		// assign to global var
		this.content[name] = {
			module: module,
			chain: chain,
			forward: function (func) {
				this.forward = func;
			},
			backward: function (func) {
				this.backward = func;
			},
			go: function (value, direction = 'forward') {

				// check live content socket
				return window.ws.sendToServer(value, this.module, this.chain, direction);
			}
		};

		return this.content[name];

	}

	sendToServer (value, module, chain, direction = 'forward') {

		// save func if given
		if (typeof direction == 'function') {
			this.content[module + ':' + chain].action['callback'] = direction;
			direction = 'callback';
		}

		// send to server
		if (this.socket.readyState == 1) {
			//console.log(module + ':' + chain + '::' + direction, JSON.stringify(value));
			this.socket.send('Authentication: "{token}" "{private}" Username: "{name}" Action: "' + module + ':' + chain + '::' + direction + '" ' + JSON.stringify(value));
		} else {
			$.ajax({
				type: 'GET',
				url: 'wss/{token}',
				data: {
					authentication: '{token}',
					username: '{name}',
					action: module + ':' + chain + '::' + direction,
					value: value
				},
				success: function (response) {
					const params = {};

					if (this.url) {

						// split query string into key-value pairs
						this.url.split('&').forEach(param => {
							const [key, value] = param.split('=');
							params[decodeURIComponent(key)] = decodeURIComponent(value || ''); // Decode URI components
						});
					}

					// work out the module/chain combo
					moduleChain = params.action.substr(0, params.action.indexOf('::'));

					try {
						response = JSON.parse(response);
					} catch (e) {

					}

					// call the forward method for this page, passing response from the server
					if (typeof window.ws.content[moduleChain] !== undefined) {
						window.ws.content[moduleChain].forward(response);
					}

				},
				error: function (xhr, status, error) {
					console.error('AJAX fallback error:', error);
				}
			});
		}
	}


}
