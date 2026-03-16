<?php
?>
<style>
	@import url('https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300&display=swap');
	a,a:hover,a:active,a:visited {text-decoration:none;color:#676767;}
	div#yfErrorInterface {position:fixed;z-index:100000000;top:0;left:0;right:0;bottom:0;width:100%;height:100%;background:whitesmoke;font-family:"Source Sans Pro";}
	div#yfErrorList, div#yfOutputFile, div#yfStackTrace, div#yfErrorMessage {position:absolute;left:30px;top:165px;bottom:0;width:25%;height:100%;}
	div#yfStackTrace {left:auto;right:30px;width:68%;}
	div#yfOutputFile {top:40%;bottom:0px;left:30px;right:30px;width:auto;}
	div#yfErrorMessage{border:1px solid red;top:105px;right:30px;padding:2px 10px;bottom:auto;background:orange;width:auto;margin:auto;border-radius:5px;color:red;height:40px;line-height:40px;font-size:13px;}
	
	div#yfHeader{position:absolute;top:20px;left:30px;right:30px;background:#f9f9f9;box-shadow:0px 0px 16px #bbb;height:60px;}
	div#yfHeader img{position:absolute;top:10px;height:40px;left:14px;}
	div#yfHeader #yfFrameworkInfo {position:absolute;width:400px;height:25px;top:20px;right:30px;text-align:right;font-size:15px;color:black;}
	div.yfContainer{box-shadow:0px 0px 12px 10px #e8e8e8;}
	div.yfHeader {background:slategray;height:44px;line-height:44px;padding:0 20px;color:#e3e3e3;}
	div.yfBox {background:whitesmoke;line-height:30px;padding:10px 30px;color:slategray;}
	div#yfStackTrace div.yfBox, div#yfOutputFile div.yfBox {max-height:150px;overflow-y:scroll;}
	div.yfError {line-height:40px;}]
</style>
<div id="yfErrorInterface">
	<div id="yfHeader">
		<img src="https://www.youds.com/images/logo.png" alt="Youds Media Logo" />
		<div id="yfFrameworkInfo">
			<?php echo Config::get('framework_info'); ?>
		</div>
	</div>	
	
	<div id="yfErrorMessage">
		Hello world!
	</div>
	<div id="yfErrorList">
		<div class="yfContainer">
			<div class="yfHeader">
				Error List
			</div>
			<div class="yfBox">
				<div class="yfError">
					Argument too much
				</div>
				<div class="yfError">
					Argument too much
				</div>
			</div>
		</div>
	</div>
	<div id="yfStackTrace">
		<div class="yfContainer">
			<div class="yfHeader">
				Stack Trace
			</div>
			<div class="yfBox">
				<div class="yfStackTrace">
					<div class="yfError">
						Argument too much
					</div>
					<div class="yfError">
						Argument too much
					</div>
					<div class="yfError">
						Argument too much
					</div>
					<div class="yfError">
						Argument too much
					</div>
					<div class="yfError">
						Argument too much
					</div>
					<div class="yfError">
						Argument too much
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="yfOutputFile">
		<div class="yfContainer">
			<div class="yfHeader">
				File Output
			</div>
			<div class="yfBox">
				<div class="yfError">
					Argument too much
				</div>
			</div>
		</div>
	</div>
</div>