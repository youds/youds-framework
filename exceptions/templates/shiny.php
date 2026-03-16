<?php

namespace YoudsFramework;

use YoudsFramework\Exceptions\Exception;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.						 |
// | Copyright (c) 2022 the Youds Framework Project.					  |
// |																		   |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE		|
// +---------------------------------------------------------------------------+

/**
 * A drop dead gorgeous exception template with eye candy embedded as inline SVG
 *
 * @package	Youds Framework - https://framework.youds.com
 * @subpackage exception
 *
 * @author	 David Zülke <dz@bitxtender.com>
 *
 * @since	  0.1
 *
 * @version	$Id$
 */

// we're not supposed to display errors
// let's throw the exception so it shows up in error logs
if(!ini_get('display_errors')) {
	throw $e;
}

$ua = '';
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	$ua = $_SERVER['HTTP_USER_AGENT'];
} elseif(isset($container) && ($rd = $container->getRequestData()) !== null && $rd instanceof IHeadersDataHolder && $rd->hasHeader('User-Agent')) {
	$ua = $rd->getHeader('User-Agent');
} elseif(isset($context) && ($rq = $context->getRequest()) !== null && !$rq->isLocked() && ($rd = $rq->getRequestData()) !== null && $rd instanceof IHeadersDataHolder) {
	$ua = $rd->getHeader('User-Agent');
}

if(!headers_sent()) {
	header('HTTP/1.0 500 Internal Server Error');
	header('Content-Type: text/html; charset=utf-8');
}

?>
<!--
<?php ob_start(); include_once('plaintext.php'); $plaintext = ob_get_contents(); ob_end_clean(); echo str_replace('--', '~~', $plaintext); /* or else unclosed comments break XHTML */ ?>
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content=text/html; charset=utf-8" />
		<title>Application Error</title>
		<meta http-equiv="Content-Language" content="en" />
		<meta name="robots" content="none" />
		
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
		<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
		<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
		<script>
		$(function() {
			$('#accordion').accordion({
		  	heightStyle: "content"
			});
		});
		</script>
		
		
		<style type="text/css">
			section#accordion h3 {clear:both;cursor:pointer;}
			section#accordion dl {font-size:11px;margin-top:15px;}
			section#accordion dl dt {display:block;float:left;width:200px;clear:left;}
			section#accordion dl dd {display:block;margin-left:200px;}

			html {
				background-color:		#EEE;
				
			}

			body {
				margin:							5em;
				padding:						2em;
				border:							1px solid #DDD;
                border-radius:	0.2em;
				-moz-border-radius:	0.2em;
				-webkit-border-radius: 0.2em;
				background-color:		#FFF;
				font-family:				Verdana, Arial, sans-serif;
				line-height:				1.5em;
				font-size:					10pt;
			}

			h1 {
				margin:							0 0 1.5em 0;
			}

			h2 {
				margin:							1em 0;
			}

			h2 a {
				color:							#000;
				text-decoration:		none;
			}

			h3 {
				margin:							1em 0 0 0;
			}
			dd {padding-bottom:15px;}
			div.nice {
				margin:							2em 0 2em 1em;
				padding-left:				1.80em !important;
			}

			div.box {
				font-weight:				bold;
				padding:						0.5em;
				-moz-border-radius:	0.2em;
				-webkit-border-radius: 0.2em;
				border:							1px solid #CCC;
				background-color:		#FCFCFC;
				position:						relative;
			}

			div.error {
				border:							1px solid #F22;
				background-color:		#FCC;
			}

			div.message {
				border:							1px solid #FB2;
				background-color:		#FFC;
				padding: 7px 18px;
			}
			div.message svg {width:45px;height:45px;}
			div.help {
				border:							1px solid #66D;
				background-color:		#F0F0FF;
			}

			ol {
				font-size:					12px;
				line-height:				1.5em;
				font-family:"Exo 2", monospace;
				margin:							10px auto;
				
			}

			li a.toggle:after {
				content:						' ▾';
			}

			li.closed a.toggle:after {
				content:						' ▸';
			}

			li.closed ol {
				display:						none;
			}
			
			li.closed {
				margin-bottom:7px;
			}
			
			ol li {position:relative;}
			ol li svg {position:absolute;top:4px;left:15px;height:18px;}
			li code {padding-left:24px;}

			dl {
				margin-top:					0;
			}

			section h2 a:before {
				content:						'▾ ';
				display:						inline-block;
				width:							1em;
			}

			section.closed h2 a:before {
				content:						'▸ ';
				display:						inline-block;
				width:							1em;
			}

			section div.container {
				padding-left:				1em;
			}

			section.closed div.container {
				display:						none;
			}

			ol ol {
				border:							1px solid #DDD;
				-moz-border-radius:	0.2em;
				-webkit-border-radius: 0.2em;
				font-size:					10pt;
				line-height:				0;
				min-height:					7em;
				padding-left:				auto;
				padding-top:				0.5em;
				padding-right:			0.5em;
				padding-bottom:			0.5em;
				text-wrap: wrap;
				overflow-x: auto;
				white-space: pre-wrap;
				word-wrap: break-word;
			}
			ol li, ol code {
				line-height: 20px;
			}
			
			
			li.highlight code {
				background-color:		#efefef;
				border-radius:2px;
			}

			#svgDefinitions {
				width:							0;
				height:							0;
				overflow:						hidden;
			}

			abbr {
				border-bottom:			1px dotted #000;
				cursor:							help;
			}

			code {
				display:						block;
				margin:							0;
				padding:						0;
			}

			ol ol li {
				padding-left:				8px;
			}
			li.highlight div{background:none!important;}
			span.primary {color:blue;}
			span.constructors {color:green;}
			span.comments {color:orange;}
			
			@media (prefers-color-scheme: dark) {
				html, body{color:#ddd;}
					
				html{
					background-color:#000;
				}
				body {
					background-color: #111;
				
				}
				div.message{color:#fafafa;background:#777;border:1px solid #ccc;}
				li a.toggle{color:#ccc;}
				li.highlight code{background:#444;}

				span.primary {color:#ccc;}
				span.constructors {color:#888;}
				span.comments {color:#ddd;}
			}
		</style>
	</head>
	<body>
		<div id="svgDefinitions">
			<svg version="1.1" id="exceptionSignContainer" xmlns:svg="http://www.w3.org/2000/svg"
				 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 150 150"
				 style="enable-background:new 0 0 150 150;" xml:space="preserve">
			<style type="text/css">
	
					.ex-st0{fill:url(#path1314_00000135674753018889822960000011155527806904175758_);stroke:#B20000;stroke-width:1;stroke-miterlimit:3.682;}
	
					.ex-st1{opacity:0.3466;fill:#CC0000;fill-opacity:0;stroke:url(#path3560_00000010289721146353594350000004995521993808040584_);stroke-width:1;stroke-miterlimit:3.4244;enable-background:new	;}
				.ex-st2{fill:#EFEFEF;}
				.ex-st3{fill:url(#path3955_00000059281225385443108920000002253185276154482827_);}
			</style>
			<g id="exceptionSign">
				<g>
		
						<linearGradient id="path1314_00000161591860131034866910000002168089033783413904_" gradientUnits="userSpaceOnUse" x1="167.3653" y1="-84.1068" x2="107.146" y2="120.8716" gradientTransform="matrix(0.9205 0 0 -0.9205 -39.2958 129.8361)">
						<stop  offset="0" style="stop-color:#A40000"/>
						<stop  offset="1" style="stop-color:#FF1717"/>
					</linearGradient>
		
						<path id="path1314" style="fill:url(#path1314_00000161591860131034866910000002168089033783413904_);stroke:#B20000;stroke-width:1;stroke-miterlimit:3.682;" d="
						M148.4,74.9c0,40.1-32.5,72.6-72.5,72.6S3.3,114.9,3.3,74.9c0,0,0,0,0,0c0-40.1,32.5-72.6,72.5-72.6
						C115.9,2.3,148.4,34.8,148.4,74.9C148.4,74.9,148.4,74.9,148.4,74.9z"/>
		
						<linearGradient id="path3560_00000054965774886292127060000004751778727679363225_" gradientUnits="userSpaceOnUse" x1="212.4251" y1="-57.511" x2="133.0337" y2="150.0166" gradientTransform="matrix(0.8561 0 0 -0.8561 -73.589 110.3208)">
						<stop  offset="0" style="stop-color:#FFE69B"/>
						<stop  offset="1" style="stop-color:#FFFFFF"/>
					</linearGradient>
		
						<path id="path3560" style="opacity:0.3466;fill:#CC0000;fill-opacity:0;stroke:url(#path3560_00000054965774886292127060000004751778727679363225_);stroke-width:1;stroke-miterlimit:3.4244;enable-background:new	;" d="
						M145.2,74.9c0,38.3-31,69.3-69.3,69.3s-69.3-31-69.3-69.3s31-69.3,69.3-69.3S145.2,36.6,145.2,74.9z"/>
				</g>
				<g>
					<rect id="rect2070" x="28.6" y="61.4" class="ex-st2" width="94.5" height="27"/>
				</g>
				<g>
		
						<linearGradient id="path3955_00000052080656070140441760000007650376715965191554_" gradientUnits="userSpaceOnUse" x1="75.5223" y1="41.0079" x2="71.8892" y2="102.2199" gradientTransform="matrix(1.003 0 0 -1.003 1.497 152.8909)">
						<stop  offset="0" style="stop-color:#FFFEFF;stop-opacity:0.3333"/>
						<stop  offset="1" style="stop-color:#FFFEFF;stop-opacity:0.2157"/>
					</linearGradient>
					<path id="path3955" style="fill:url(#path3955_00000052080656070140441760000007650376715965191554_);" d="M141.4,70.8
						c0,36.7-35-21.2-63.2,1.3c-27.5,22-69.7,41.3-69.7,4.6c0-37.5,29.4-72.3,66.1-72.3S141.4,34.2,141.4,70.8z"/>
				</g>
			</g>
			</svg>
			
		<svg version="1.1" id="warningSignContainer" xmlns:svg="http://www.w3.org/2000/svg"
			 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 14 14"
			 style="enable-background:new 0 0 14 14;" xml:space="preserve">
		<style type="text/css">
			.st0{fill:#CC0000;stroke:#9F0000;stroke-width:1;stroke-miterlimit:6.2667;}
			.st1{fill:url(#path6496_00000140720923568110408920000013906266440059090876_);}
	
				.st2{opacity:0.5;fill:none;stroke:url(#path1325_00000150799377724173813510000013565583842400139909_);stroke-width:1;stroke-miterlimit:6.2667;enable-background:new	;}
		</style>
		<g id="warningSign">
			<g transform="matrix(1.566667,0.000000,0.000000,1.566667,-8.925566,-23.94764)">
				<g transform="matrix(1,0,4.537846e-3,1,-0.138907,-1.394718e-15)">
					<path id="path6485" class="st0" d="M14.2,22.6l-3.5-6.6c-0.1-0.2-0.3-0.3-0.5-0.3c-0.2,0-0.4,0.1-0.5,0.3l-3.5,6.6
						c-0.1,0.2-0.1,0.4,0,0.5c0.1,0.2,0.3,0.2,0.5,0.2h7c0.2,0,0.4-0.1,0.5-0.3C14.2,23,14.2,22.8,14.2,22.6z"/>
					<g transform="matrix(0.625,0,-5.534934e-3,0.634254,6.164053,15.76055)">
				
							<linearGradient id="path6496_00000012466466572395071560000001518764978130370964_" gradientUnits="userSpaceOnUse" x1="13.2526" y1="11.8513" x2="27.2161" y2="-2.6985" gradientTransform="matrix(0.9792 0 -4.162200e-03 -0.9937 -12.8553 13.9363)">
							<stop  offset="0" style="stop-color:#D4D4D4"/>
							<stop  offset="0.3982" style="stop-color:#E2E2E2"/>
							<stop  offset="1" style="stop-color:#FFFFFF"/>
						</linearGradient>
						<path id="path6496" style="fill:url(#path6496_00000012466466572395071560000001518764978130370964_);" d="M1.8,10.7
							C1.7,10.9,1.8,11,2,11h9.1c0.2,0,0.3-0.1,0.2-0.3L6.7,2.1c-0.1-0.2-0.2-0.2-0.3,0L1.8,10.7z"/>
					</g>
			
						<linearGradient id="path1325_00000152952754274671326090000013056416129797042877_" gradientUnits="userSpaceOnUse" x1="-100.0519" y1="56.438" x2="-92.2575" y2="50.1406" gradientTransform="matrix(1.4084 0 1.916444e-02 -1.4636 146.8905 101.0497)">
						<stop  offset="0" style="stop-color:#FFFFFF"/>
						<stop  offset="1" style="stop-color:#FFFFFF;stop-opacity:0.3402"/>
					</linearGradient>
			
						<path id="path1325" style="opacity:0.5;fill:none;stroke:url(#path1325_00000152952754274671326090000013056416129797042877_);stroke-width:1;stroke-miterlimit:6.2667;enable-background:new	;" d="
						M13.9,22.5l-3.2-6.1c-0.1-0.3-0.2-0.3-0.4-0.3c-0.2,0-0.3,0.1-0.4,0.4l-3.3,6.1c-0.2,0.3-0.2,0.4-0.1,0.6
						c0.1,0.2,0.2,0.1,0.6,0.2h6.4c0.4,0,0.5,0,0.6-0.2C14.1,22.9,14,22.8,13.9,22.5z"/>
				</g>
				<g transform="matrix(0.555088,0,0,0.555052,7.749711,17.80196)">
					<path id="path6500" d="M4.3,7.6c-0.4,0-0.7-0.3-0.7-0.7c0-0.5,0.3-0.7,0.7-0.7S5,6.4,5.1,6.9C5.1,7.3,4.8,7.6,4.3,7.6L4.3,7.6z
						 M3.9,5.7L3.7,2H5L4.8,5.7H3.9L3.9,5.7z"/>
				</g>
			</g>
		</g>
		</svg>
		
			
			<svg version="1.1" id="importantSignContainer" xmlns:svg="http://www.w3.org/2000/svg"
				 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 44 44"
				 style="enable-background:new 0 0 44 44;" xml:space="preserve">
			<style type="text/css">
				.ip-st0{fill:#F57900;stroke:#914900;stroke-width:0.9821;stroke-linecap:round;stroke-linejoin:round;}
				.ip-st1{fill:none;stroke:#FCAF3E;stroke-width:0.9821;stroke-linecap:round;stroke-linejoin:round;}
				.ip-st2{fill:#FFFFFF;}
				.ip-st3{fill:#FFFEFF;fill-opacity:0.2139;}
			</style>
			<g id="importantSign">
				<path id="path1650" class="ip-st0" d="M43.3,22c0,11.7-9.5,21.1-21.1,21.1S1,33.7,1,22S10.5,0.9,22.2,0.9S43.3,10.4,43.3,22z"/>
				<path id="path3392" class="ip-st1" d="M42.7,21.9c0,11.3-9.1,20.4-20.4,20.4S1.9,33.2,1.9,21.9S11,1.5,22.3,1.5S42.7,10.6,42.7,21.9z"
					/>
				<path id="rect1872" class="ip-st2" d="M19.3,10.8c-0.1,0-0.2,0.2-0.2,0.4l1.1,14.7c0,0.2,0.1,0.4,0.2,0.4c0,0,0.9,0,1.6,0
					c0.2,0,0.3,0,0.5,0c0.7,0,1.6,0,1.6,0c0.1,0,0.2-0.2,0.2-0.4l1.1-14.7c0-0.2-0.1-0.4-0.2-0.4h-2.6c0,0,0,0,0,0H19.3z"/>
				<path id="path2062" class="ip-st2" d="M24.6,30.9c0,1.3-1,2.3-2.3,2.3c-1.3,0-2.3-1-2.3-2.3s1-2.3,2.3-2.3
					C23.5,28.6,24.6,29.6,24.6,30.9z"/>
				<path id="path3068" class="ip-st3" d="M41.8,21.1c0,11-6.1-4.4-19,0.4C9.9,26.3,2,32.1,2,21.1S10.9,1.1,21.9,1.1S41.8,10.1,41.8,21.1z
					"/>
			</g>
			</svg>
			
			
			<svg id="arrowContainer" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="200px" width="200px">
				<defs>
					<linearGradient id="linearGradient1442">
						<stop id="stop1444" offset="0" style="stop-color:#73d216"/>
						<stop id="stop1446" offset="1.0000000" style="stop-color:#4e9a06"/>
					</linearGradient>
					<linearGradient id="linearGradient8650">
						<stop id="stop8652" offset="0" style="stop-color:#ffffff;stop-opacity:1;"/>
						<stop id="stop8654" offset="1" style="stop-color:#ffffff;stop-opacity:0;"/>
					</linearGradient>
					<radialGradient xlink:href="#linearGradient1442" id="radialGradient1469" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.871885e-16,-0.843022,1.020168,2.265228e-16,0.606436,42.58614)" cx="35.292667" cy="20.494493" fx="35.292667" fy="20.494493" r="16.956199"/>
					<radialGradient xlink:href="#linearGradient8650" id="radialGradient1471" gradientUnits="userSpaceOnUse" gradientTransform="matrix(3.749427e-16,-2.046729,-1.557610,-2.853404e-16,44.11559,66.93275)" cx="15.987216" cy="1.5350308" fx="15.987216" fy="1.5350308" r="17.171415"/>
				</defs>
				<g id="arrow">
					<g transform="matrix(-1.000000,0.000000,0.000000,-1.000000,47.02856,43.99921)">
						<path style="opacity:1.0000000;color:#000000;fill:url(#radialGradient1469);fill-opacity:1.0000000;fill-rule:evenodd;stroke:#3a7304;stroke-width:1.0000004;stroke-linecap:round;stroke-linejoin:round;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:10.000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1;visibility:visible;display:inline;overflow:visible" d="M 14.519136,38.500000 L 32.524165,38.496094 L 32.524165,25.504468 L 40.519531,25.496656 L 23.374809,5.4992135 L 6.5285585,25.497284 L 14.524440,25.501074 L 14.519136,38.500000 z " id="path8643"/>
						<path style="opacity:0.50802141;color:#000000;fill:url(#radialGradient1471);fill-opacity:1.0000000;fill-rule:evenodd;stroke:none;stroke-width:1.0000000;stroke-linecap:round;stroke-linejoin:round;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:10.000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000;visibility:visible;display:inline;overflow:visible" d="M 39.429889,24.993467 L 32.023498,25.005186 L 32.026179,37.998023 L 16.647623,37.98887 C 17.417545,19.64788 27.370272,26.995797 32.029282,16.341991 L 39.429889,24.993467 z " id="path8645"/>
						<path id="path8658" d="M 15.520704,37.496094 L 31.522109,37.500000 L 31.522109,24.507050 L 38.338920,24.491425 L 23.384644,7.0388396 L 8.6781173,24.495782 L 15.518018,24.501029 L 15.520704,37.496094 z " style="opacity:0.48128340;color:#000000;fill:none;fill-opacity:1.0000000;fill-rule:evenodd;stroke:#ffffff;stroke-width:1.0000004;stroke-linecap:butt;stroke-linejoin:miter;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:10.000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000;visibility:visible;display:inline;overflow:visible"/>
					</g>
				</g>
			</svg>
		</div>
		<div style="float:right; margin:-6em -6em 0 0; width:10em; height:10em"><svg layoutBox="1 0 46 46" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="height:300px;z-index:100;position:relative;left:-30px;top:10px;max-width:150px;margin-left:-10px;"><use xlink:href="#exceptionSign" /></svg></div>
		<h1>Application Error</h1>
<?php if(count($exceptions) > 1): ?>
		<div class="box nice">
			<div style="float:left; position:relative; margin-top:-1.75em; margin-left:-5.5em; height:5em; width:5em;"><svg layoutBox="4 7 39 34" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#arrow" /></svg></div>
			The <?php echo get_class($e); ?> was caused by <?php if(count($exceptions) == 2): ?>another exception<?php else: ?>other exceptions<?php endif; ?>. A full chain of exceptions is listed below.
		</div>
<?php endif; ?>
<?php foreach($exceptions as $ei => $e): ?>
		<section class="<?php if($ei+1 != count($exceptions)): ?>closed<?php endif; ?>">
			<h2 class="exception"><?php echo get_class($e); ?></a></h2>
			<div class="container" id="exception<?php echo $ei; ?>container">
<?php $msg = nl2br(htmlspecialchars($e->getMessage())); ?>
<?php if($msg != ''): ?>
				<div class="box message nice">
					<div style="position:absolute; top:-1.25em; left:-2em; height:45px; width:45px;"><svg layoutBox="3 0 43 43" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#importantSign" /></svg></div>
					<?php echo $msg; ?>
				</div>
<?php endif; ?>
				<section id="accordion">
					<h3>Stack Trace</h3>
					<ol>
<?php
$i = 0;
$highlights = array();
$filepaths = array();
foreach(array(
	'core.chains_dir',
	'core.output_dir',
	'core.config_dir',
	'core.cache_dir',
	'core.base_dir',
	'core.app_dir',
	'core.src_dir',
) as $directive) {
	$filepaths['#^' . preg_quote(Config::get($directive)) . '(?<=.)#'] = sprintf('<abbr title="%s">%s</abbr>', htmlspecialchars(Config::get($directive)), $directive);
} 
$fixedTrace = Exception::getFixedTrace($e, isset($exceptions[$ei+1]) ? $exceptions[$ei+1] : null);
foreach($fixedTrace as $trace):
	$i++;
	if(isset($trace['file']) && !isset($highlights[$trace['file']])) {
		$highlights[$trace['file']] = Exception::highlightFile($trace['file']);
	}
?>
					<li id="exception<?php echo $ei; ?>frame<?php echo $i; ?>"<?php if($i != 2 && count($fixedTrace) > 1): ?> class="closed"<?php endif; ?>>at <?php if($i > 1): ?><strong><?php if(isset($trace['class'])): ?><?php echo $trace['class'], htmlspecialchars($trace['type']); ?><?php endif; ?><?php echo $trace['function']; ?>(</strong><?php if(isset($trace['args'])): ?><?php echo Exception::buildParamList($trace['args']); ?><strong>)</strong><?php endif; ?><?php else: ?><em>exception origin</em><?php endif; ?><br />in <?php if(isset($trace['file'])): echo preg_replace(array_keys($filepaths), $filepaths, $trace['file']); ?> <a href="#frame<?php echo $i; ?>" class="toggle" title="Toggle source code snippet" onclick="this.parentNode.className = this.parentNode.className == 'closed' ? '' : 'closed'; return false;">line <?php echo $trace['line']; ?></a><ol start="<?php echo $start = $trace['line'] < 4 ? 1 : $trace['line'] - 3; ?>" style="padding-left:<?php echo strlen($start+6)*0.6+2; ?>em"><?php
$lines = array_slice($highlights[$trace['file']], $start - 1, 7, true);
foreach($lines as $key => &$line) {
	if($key + 1 == $trace['line']): ?><li class="highlight"><div style="float:left; width:1em; height:1em; margin-left:-1.35em;"><svg layoutBox="3 3 42 42" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#warningSign" /></svg></div><?php else: ?><li><?php endif; ?><code><?php
	echo str_replace('#DD0000', '#e25454', $line);
?></code></li>
<?php } ?></ol><?php else: // no info about origin file ?><em>unknown</em><?php endif; ?></li>
<?php endforeach; ?>
				</ol>
			
				<h3>Project Information</h3>
				<dl>
					<dt>Project Name</dt>
					<dd><?php echo Config::get('core.name'); ?></dd>
					<dt>Availability</dt>
					<dd><?php echo (Config::get('core.available')?'Enabled':'Disabled'); ?></dd>
					<dt>Renderer</dt>
					<dd><?php echo Config::get('core.renderer'); ?></dd>
					<dt>Generator</dt>
					<dd><?php echo Config::get('core.generator'); ?></dd>
					<dt>Database</dt>
					<dd><?php echo (Config::get('core.use_database')?'Enabled':'Disabled'); ?></dd>
					<dt>Logging</dt>
					<dd><?php echo (Config::get('core.use_logging')?'Enabled':'Disabled'); ?></dd>
					<dt>Security</dt>
					<dd><?php echo (Config::get('core.use_security')?'Enabled':'Disabled'); ?></dd>
					<dt>Translation</dt>
					<dd><?php echo (Config::get('core.use_translation')?'Enabled':'Disabled'); ?></dd>
				</dl>
				
				<h3>Environment Variables</h3>
				<dl>
					<dt>Name</dt>
					<dd><?php echo Config::get('core.environment'); ?></dd>
					<dt>Chain</dt>
					<dd><?php 
						if ($container):
							$opt = $container->getContext()->getRouting()->getRoutes()[$container->getContext()->getRouting()->getName()]['opt'];
						    echo $opt['content'] . '/' . $opt['chain']; ?>
                        <?php endif; ?></dd>
					<dt>Output Type</dt>
					<dd><?php echo $container ? $container->getOutputType():'Unknown'; ?></dd>
					<dt>Context</dt>
					<dd><?php echo ($container ? $container->getContext()->getName():'Unknown');?></dd>
					<dt>Project Version</dt>
					<dd><?php echo Config::get('core.version'); ?></dd>
					<dt>Youds Framework Version:</dt>
					<dd><?php echo htmlspecialchars(Config::get('framework.version')); ?></dd>
					<dt>PHP:</dt>
					<dd><?php echo htmlspecialchars(phpversion()); ?></dd>
					<dt>System:</dt>
					<dd><?php echo htmlspecialchars(php_uname()); ?></dd>
					<dt>Timestamp:</dt>
					<dd><?php echo gmdate(DATE_ISO8601); ?></dd>
				</dl>
				
				<h3>Request Headers</h3>
				<dl>
					<?php if (isset($rd) && is_object($rd) && method_exists($rd, 'getHeaders')): foreach ($rd->getHeaders() as $name => $header):?>
						<dt><?php echo $name; ?></dt>
						<dd><?php echo $header; ?></dd>
					<?php endforeach; endif;?>
				</dl>
				<h3>Current Session</h3>
				<dl>
					<dt>Authenticated</dt>
					<dd><?php echo $container?($container->getContext()->getUser()->isAuthenticated()?'Yes':'No'):'No'; ?></dd>
					<?php if ($container): foreach ($container->getContext()->getUser()->getCredentials() as $key => $value):?>
						<dt><?php echo $key; ?></dt>
						<dd><?php echo $value; ?></dd>
					<?php endforeach; endif;?>
				</dl>
				<h3>Matched Route</h3> 
				<dl>
					<?php 
					$routes = $container?$container->getContext()->getRouting()->getRoutes():[];
                    if ($container && isset($routes[$container->getContext()->getRouting()->getName()]['opt'])):
                        foreach($routes[$container->getContext()->getRouting()->getName()]['opt'] as $key => $value):
                            if (is_bool($value))
                                $value = ($value?'Yes':'No');
                            elseif (is_null($value))
                                continue;
                            elseif (is_array($value) && count($value) > 0)
                                $value = var_export($value, true);
                            elseif (is_array($value) && count($value) == 0)
                                continue;
                            echo '<dt>' . $key . '</dt> <dd>' . $value . '</dd>';
                        endforeach;
                    endif;
					 ?>
				</dl>
				<h3>Validator Status</h3>
				<dl>
					<dt>Successful Arguments</dt>
					<dd><?php 
						$arguments = $container?$container->getValidationManager()->getReport()->getSucceededArguments():[];
						if (count($arguments) > 0):
							var_export($arguments);
						else:
							echo 'None';
						endif;
					?></dd>
					<dt>Failed Arguments</dt>
					<dd><?php 
						$arguments = $container?$container->getValidationManager()->getReport()->getFailedArguments():[];
						if (count($arguments) > 0):
							var_export($arguments);
						else:
							echo 'None';
						endif;
					?></dd>
					<dt>Incidents</dt>
					<dd><?php 
						if ($container && $container->getValidationManager()->getReport()->hasIncidents()):
							var_export($container->getValidationManager()->getReport()->getIncidents());
						else:
							echo 'None';
						endif;
					?></dd>
				</dl>
			
				<h3>Database Credentials</h3> 
				<dl>
					<dt>Status</dt>
					<dd><?php echo (Config::get('core.database')?'Enabled':'Disabled'); ?></dd>
					<dt>Default Database:</dt>
					<dd><?php echo $container?$container->getContext()->getDatabaseManager()->getDefaultDatabaseName():'None'; ?></dd>
					<?php
                    if ($container):
                        foreach ($container->getContext()->getDatabaseManager()->getCredentials() as $name => $database):	?>
                            <dt><?php echo $name; ?></dt>
                            <dd><?php
                            foreach ($database as $key => $value):
                                if ($key == 'password')
                                    $value = '******';
                                echo $key . ': ' . $value;
                                ?><br /><?php
                            endforeach; ?></dd><?php
                        endforeach;
                    endif; ?>
				</dl>
			
				
				<p style="clear:both;height:0;">&nbsp;</p>
			
			</div>
			
		</section>
<?php
endforeach;
?>	
	</body>
</html>
<!--
<?php ob_start(); include_once('plaintext.php'); $plaintext = ob_get_contents(); ob_end_clean(); echo str_replace('--', '~~', $plaintext); /* or else unclosed comments break XHTML */ ?>
-->
