<?php

/*
*	
*
*	Special Thanks to Kimili <a href="http://kimili.com/plugins/kml_flashembed"> for showing how to embed flash object in wordpress
*   
*	
*
*/

/***********************************************************************
*	Global Vars
************************************************************************/

$sw__request_type	= "";
$sw_flashembed_ver	= "1.2";

require('buttonsnap.php');
require_once('SitepalWizardLib.php');
/***********************************************************************
*	Trigger Function
************************************************************************/

function swDoObStart()
{
	ob_start('sw_flashembed');

}


/***********************************************************************
*	Run the main function 
************************************************************************/

function sw_flashembed($content) {
	$pattern = '/(<p>[\s\n\r]*)??(([\[<]SITEPAL_(FLASH|SWF)EMBED.*\/[\]>])|([\[<]SITEPAL_(FLASH|SWF)EMBED.*[\]>][\[<]\/SITEPAL_(FLASH|SWF)EMBED[\]>]))([\s\n\r]*<\/p>)??/Umi'; 
	$result = preg_replace_callback($pattern,'SitepalWizard_flashembed_parse_sw_tags',$content);
	return $result;	
}


/***********************************************************************
*	Parse out the KFE Tags
************************************************************************/

function SitepalWizard_flashembed_parse_sw_tags($match) {
		
	$r	= "";
	$strip		= array('[SITEPAL_FLASHEMBED',
						'][/SITEPAL_FLASHEMBED]',
						'[sitepal_flashembed',
						'][/sitepal_flashembed]',
						'[SITEPAL_SWFEMBED',
						'][/SITEPAL_SWFEMBED]',
						'[sitepal_swfembed',
						'][/sitepal_swfembed]',
						'/]',
						'<SITEPAL_FLASHEMBED',
						'></SITEPAL_FLASHEMBED>',
						'<sitepal_flashembed',
						'></sitepal_flashembed>',
						'<SITEPAL_SWFEMBED',
						'></SITEPAL_SWFEMBED>',
						'<sitepal_swfembed',
						'></sitepal_swfembed>',
						'/>',
						'\n',
						'<br>',
						'<br />',
						'<p>',
						'</p>'
						);
				
	
	$elements	= str_replace($strip, '', $match[0]);
	$elements	= preg_replace("/=(\s*)\"/", "==`", $elements);
	$elements	= preg_replace("/=(\s*)&Prime;/", "==`", $elements);
	$elements	= preg_replace("/=(\s*)&prime;/", "==`", $elements);
	$elements	= preg_replace("/=(\s*)&#8221;/", "==`", $elements);
	$elements	= preg_replace("/\"(\s*)/", "`| ", $elements);
	$elements	= preg_replace("/&Prime;(\s*)/", "`|", $elements);
	$elements	= preg_replace("/&prime;(\s*)/", "`|", $elements);
	$elements	= preg_replace("/&#8221;(\s*)/", "`|", $elements);
	$elements	= preg_replace("/&#8243;(\s*)/", "`|", $elements);
	$elements	= preg_replace("/&#8216;(\s*)/", "'", $elements);
	$elements	= preg_replace("/&#8217;(\s*)/", "'", $elements);
	
	$attpairs	= preg_split('/\|/', $elements, -1, PREG_SPLIT_NO_EMPTY);
	$atts		= array();
	
	// Create an associative array of the attributes
	for ($x = 0; $x < count($attpairs); $x++) {
		
		$attpair		= explode('==', $attpairs[$x]);
		$attn			= trim(strtolower($attpair[0]));
		$attv			= preg_replace("/`/", "", trim($attpair[1]));
		$atts[$attn]	= $attv;
	}
	
	// replace back the link 
	$pos = 0;
	$pos = strpos($atts['movie'], '_SITEPAL_');
	//if ($pos !== false )
	//	$atts['movie'] = str_replace('_SITEPAL_', 'vhost.oddcast.com/vhsssecure.php?doc=', $atts['movie']);
		//$atts['movie'] = str_replace('_SITEPAL_', 'vhost.staging.oddcast.com/vhsssecure.php?doc=', $atts['movie']);
	//else
	//	$atts['movie'] = "Invalid Sitepal Link";
	
	if (!empty($atts['movie']) && !empty($atts['height']) && !empty($atts['width'])) {
		
		$atts['fversion'] 			= (!empty($atts['fversion'])) ? $atts['fversion'] : 6;
		$atts['height']				= ($height{strlen($atts['height']) - 1} == "%") ? '"' . $atts['height'] . '"' : $atts['height'];
		$atts['width']				= ($width{strlen($atts['width']) - 1} == "%") ? '"' . $atts['width'] . '"' : $atts['width'];
		$atts['useexpressinstall']	= (!empty($atts['useexpressinstall'])) ? $atts['useexpressinstall'] : '""';
		$atts['detectKey']			= (!empty($atts['detectKey'])) ? ',"' . $atts['detectKey'] . '"' : '';
		
		$fvarpair_regex		= "/(?<!([$|\?]\{))\s+;\s+(?!\})/";
		$atts['fvars']		= (!empty($atts['fvars'])) ? preg_split($fvarpair_regex, $atts['fvars'], -1, PREG_SPLIT_NO_EMPTY) : array();
		
		// Convert any quasi-HTML in alttext back into tags
		$atts['alttext']	= (!empty($atts['alttext'])) ? preg_replace("/{(.*?)}/i", "<$1>", $atts['alttext']) : "" ;
		
		// If we're not serving up a feed, generate the script tags
		if ($GLOBALS['sw__request_type'] != "feed") {
			$r	= sw_flashembed_build_fo_script($atts);
		} else {
			$r	= sw_flashembed_build_object_tag($atts);
		}
	}
 	return $r; 
}


/***********************************************************************
*	Build the Javascript from the tags
************************************************************************/

function sw_flashembed_build_fo_script($atts) {
	
	$out	= array();	
	if (is_array($atts)) extract($atts);
	
	$rand		= mt_rand();  // For making sure this instance is unique
	
	// Extract the filename minus the extension...
	$swfname			= (strrpos($movie, "/") === false) ?
							$movie :
							substr($movie, strrpos($movie, "/") + 1, strlen($movie));
	$swfname			= (strrpos($swfname, ".") === false) ?
							$swfname :
							substr($swfname, 0, strrpos($swfname, "."));
	
	// ... to use as a default ID if an ID is not defined.
	$id			= (!empty($id)) ? $id : "fm_" . $swfname;
	// ... as well as an empty target if that isn't defined.
	if (empty($target)) {              
		$targname	= "fo_targ_" . $swfname . $rand;
		$classname	= (empty($targetclass)) ? "flashmovie" : $targetclass;
		// Create a target div
		$out[]		= '<div id="' . $targname . '" class="' . $classname . '">'.$alttext.'</div>';
		$target	= $targname;
	}				
	
	
									$out[] = '';
						  	  		$out[] = '<script type="text/javascript">';
						  	  		$out[] = '	// <![CDATA[';
									$out[] = '';
						  	  		$out[] = '	var so_' . $rand . ' = new SWFObject("' . $movie . '","' . $id . '","' . $width . '","' . $height . '","' . $fversion . '","' . $bgcolor . '",' . $useexpressinstall . ',"' . $quality . '","' . $xiredirecturl . '","' . $redirecturl . '"' . $detectKey . ');';
	if (!empty($play))				$out[] = '	so_' . $rand . '.addParam("play", "' . $play . '");';
	if (!empty($loop))				$out[] = '	so_' . $rand . '.addParam("loop", "' . $loop . '");';
	if (!empty($menu)) 				$out[] = '	so_' . $rand . '.addParam("menu", "' . $menu . '");';
	if (!empty($scale)) 			$out[] = '	so_' . $rand . '.addParam("scale", "' . $scale . '");';
	if (!empty($wmode)) 			$out[] = '	so_' . $rand . '.addParam("wmode", "' . $wmode . '");';
	if (!empty($align)) 			$out[] = '	so_' . $rand . '.addParam("align", "' . $align . '");';
	if (!empty($salign)) 			$out[] = '	so_' . $rand . '.addParam("salign", "' . $salign . '");';    
	if (!empty($base)) 	   		 	$out[] = '	so_' . $rand . '.addParam("base", "' . $base . '");';
	if (!empty($allowscriptaccess))	$out[] = '	so_' . $rand . '.addParam("AllowScriptAccess", "' . $allowscriptaccess . '");';
	// Loop through and add any name/value pairs in the $fvars attribute
	for ($i = 0; $i < count($fvars); $i++) {
		$thispair	= trim($fvars[$i]);
		$nvpair		= explode("=",$thispair);
		$name		= trim($nvpair[0]);
		$value		= "";
		for ($j = 1; $j < count($nvpair); $j++) {			// In case someone passes in a fvars with additional "="       
			$value		.= trim($nvpair[$j]);
			$value		= preg_replace('/&#038;/', '&', $value);
			if ((count($nvpair) - 1)  != $j) {
				$value	.= "=";
			}
		}
		// Prune out JS or PHP values
		if (preg_match("/^\\$\\{.*\\}/i", $value)) { 		// JS
			$endtrim 	= strlen($value) - 3;
			$value		= substr($value, 2, $endtrim);
			$value		= str_replace(';', '', $value);
		} else if (preg_match("/^\\?\\{.*\\}/i", $value)) {	// PHP
			$endtrim 	= strlen($value) - 3;
			$value 		= substr($value, 2, $endtrim);
			$value 		= '"'.eval("return " . $value).'"';
		} else {
			$value = '"'.$value.'"';
		}
									$out[] = '	so_' . $rand . '.addVariable("' . $name . '",' . $value . ');';
	}
	
									$out[] = '	so_' . $rand . '.write("' . $target . '");';
									$out[] = '';
									$out[] = '	// ]]>';
									$out[] = '</script>';
	// Add NoScript content
	if (!empty($noscript)) {
									$out[] = '<noscript>';
									$out[] = '	' . $noscript;
									$out[] = '</noscript>';
	}
									$out[] = '';
											
	$ret .= join("\n", $out);
	return $ret;
}
           
/***********************************************************************
*	Build a Satay Object for RSS feeds
************************************************************************/

function sw_flashembed_build_object_tag($atts) {
	
	$out	= array();	
	if (is_array($atts)) extract($atts);
	
	// Build a query string based on the $fvars attribute
	$querystring = (count($fvars) > 0) ? "?" : "";
	for ($i = 0; $i < count($fvars); $i++) {
		$thispair	= trim($fvars[$i]);
		$nvpair		= explode("=",$thispair);
		$name		= trim($nvpair[0]);
		$value		= "";
		for ($j = 1; $j < count($nvpair); $j++) {			// In case someone passes in a fvars with additional "="
			$value		.= trim($nvpair[$j]);
			$value		= preg_replace('/&#038;/', '&', $value);
			if ((count($nvpair) - 1)  != $j) {
				$value	.= "=";
			}
		}
		// Prune out JS or PHP values
		if (preg_match("/^\\$\\{.*\\}/i", $value)) { 		// JS
			$endtrim 	= strlen($value) - 3;
			$value		= substr($value, 2, $endtrim);
			$value		= str_replace(';', '', $value);
		} else if (preg_match("/^\\?\\{.*\\}/i", $value)) {	// PHP
			$endtrim 	= strlen($value) - 3;
			$value 		= substr($value, 2, $endtrim);
			$value 		= '"'.eval("return " . $value).'"';
		} else {
			$value = '"'.$value.'"';
		}
		$querystring .= $name . '=' . $value;
		if ($i > count($fvars)) {
			$querystring .= "&";
		}
	}
	
									$out[] = '';    
						  	  		$out[] = '<object	type="application/x-shockwave-flash"';
									$out[] = '			data="'.$movie.$querystring.'"'; 
	if (!empty($base)) 	   		 	$out[] = '			base="'.$base.'"';
									$out[] = '			width="'.$width.'"';
									$out[] = '			height="'.$height.'">';
									$out[] = '	<param name="movie" value="' . $movie.$querystring . '" />';
	if (!empty($play))				$out[] = '	<param name="play" value="' . $play . '" />';
	if (!empty($loop))				$out[] = '	<param name=loop" value="' . $loop . '" />';
	if (!empty($menu)) 				$out[] = '	<param name=menu" value="' . $menu . '" />';
	if (!empty($scale)) 			$out[] = '	<param name=scale" value="' . $scale . '" />';
	if (!empty($wmode)) 			$out[] = '	<param name=wmode" value="' . $wmode . '" />';
	if (!empty($align)) 			$out[] = '	<param name=align" value="' . $align . '" />';
	if (!empty($salign)) 			$out[] = '	<param name=salign" value="' . $salign . '" />';    
	if (!empty($base)) 	   		 	$out[] = '	<param name=base" value="' . $base . '" />';
	if (!empty($allowscriptaccess))	$out[] = '	<param name=AllowScriptAccess" value="' . $allowscriptaccess . '" />';
	 								$out[] = '</object>';     

	$ret .= join("\n", $out);
	return $ret;
	
}

/***********************************************************************
*	Add the call to flashobject.js
************************************************************************/

function sw_flashembed_add_flashobject_js() {
	global $sw_flashembed_ver;
	//echo '<!--'. buttonsnap_dirname(__FILE__).' -->';
	//echo '<br></br>';
	//echo '<br></br>';
	//echo '<br></br>';
	//echo '<!--'. __FILE__.' -->';
	echo '
	<!-- Courtesy of Kimili Flash Embed - Version '. $sw_flashembed_ver . ' -->
	<script src="' . buttonsnap_dirname(__FILE__) . '/SitepalWizard_flashembed.php?swfobject.js" type="text/javascript"></script>
';
}

// Thanks to Jeff Minard ( http://www.thecodepro.com/ ) for showing how to embed the JS directly in the plugin.
?>