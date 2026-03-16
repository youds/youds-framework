<h1>Robot Check</h1>
<p>Please complete the captcha below.</p>
<form action="{ro->gen('login.verify')}" method="post" accept-charset="utf-8">

	<div data-sitekey="6LcmUTIfAAAAABNdpyEwGFa6dnZDbD8Co6mjK-cL"></div>
	
	<p style="padding-top:27px;"><input type="submit" value="Verify" /></p>
</form>
<script type="text/javascript">
	function clearValue(id) {
		document.getElementById('char' + id).value = ''; // remove value
	}
	function nextElement(id) {
		
		// check input is valid
		if (document.getElementById('char' + (id - 1)).value.length > 0)
			document.getElementById('char' + id).focus(); // move to next sibling
	}
</script>
