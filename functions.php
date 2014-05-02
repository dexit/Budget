<?php
//Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
	if(!headers_sent()) {
		header("Status: 301 Moved Permanently");
		header(sprintf(
			'Location: https://%s%s',
			$_SERVER['HTTP_HOST'],
			$_SERVER['REQUEST_URI']
		));
		exit();
	}
}

/*********************/
/*** The Functions ***/
/*********************/
function db_connect($full_feature = true) {
	require_once('db_config.php');
	
	if($GLOBALS['con'] && mysql_ping($GLOBALS['con'])) {
		mysql_close($GLOBALS['con']);
	}
	
	if(!$full_feature) {
		$GLOBALS['con'] = mysql_connect($GLOBALS['db_host'], $GLOBALS['ff_db_user'], $GLOBALS['ff_db_pass'])
			or die("Could not connect");
		mysql_select_db($GLOBALS['db_database'], $GLOBALS['con'])
			or die("Could not select db. " . mysql_error());
	} else {
		$GLOBALS['con'] = mysql_connect($GLOBALS['db_host'], $GLOBALS['db_user'], $GLOBALS['db_pass'])
			or die("Could not connect");
		mysql_select_db($GLOBALS['db_database'], $GLOBALS['con'])
			or die("Could not select db. " . mysql_error());
	}
}

function get_month_dates($month) {
	$year = 2011;

	while($month > 12) {
		$month = $month - 12;
		$year++;
	}
	
	return array(mktime(0,0,0,$month,1,$year), mktime(0,0,0,$month + 1,1,$year));
}

function get_month_no($timestamp = false) {
	if(!$timestamp)
		$timestamp = time();
	$year = date("Y", $timestamp);
	$month = date("n", $timestamp);
	
	while($year > 2011) {
		$year--;
		$month = $month + 12;
	}
	
	return $month;
}

function clean_postarray($inarray) {
	if( is_array($inarray) ) {
		foreach($inarray as $key => $value) {
			if( is_array($value) ) {
				$outarray[$key] = clean_postarray($value);
			} else {
				$outvalue = htmlentities($value, ENT_QUOTES);
				$outarray[$key] = $outvalue;
			}
		}
	} else {
		$outarray = htmlentities($inarray, ENT_QUOTES);
	}
	return $outarray;
}

function senc($data) {
return $data;
	$key = 'SADFo92jzVnzSj39IUYGvi6eL8v6RvJH8Cytuiouh547vCytdyUFl76R';
	$iv = '“æ+$PýJ';

	// encrypt using Blowfish/CBC
	$cipher = mcrypt_encrypt( MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_CBC, $iv );

	return trim(base64_encode($cipher));
}

function sdec($cipher) {
return $cipher;
	$key = 'SADFo92jzVnzSj39IUYGvi6eL8v6RvJH8Cytuiouh547vCytdyUFl76R';
	$iv = '“æ+$PýJ';
	
	$data = mcrypt_decrypt( MCRYPT_BLOWFISH, $key, $cipher, MCRYPT_MODE_CBC, $iv );
	return trim($data);
}

function budget_return($message) {
	global $camefrom;
	if(strpos($camefrom, '?') !== false)
		header("Location: $camefrom&message=$message");
	else
		header("Location: $camefrom?message=$message");
	die();
}
?>