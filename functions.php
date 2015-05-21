<?php
//Force HTTPS
/*if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
	if(!headers_sent()) {
		header("Status: 301 Moved Permanently");
		header(sprintf(
			'Location: https://%s%s',
			$_SERVER['HTTP_HOST'],
			$_SERVER['REQUEST_URI']
		));
		exit();
	}
}*/

$accounts = array(
	3 => 'Rachel\'s Visa',
	4 => 'Ally',
	1 => 'SJ\'s Mastercard',
	5 => 'Notes/Extra',
	6 => 'Cash',
	8 => 'NTM Account',
	9 => 'Amex CC',
	2 => 'Chase CC',
	7 => 'Paypal Account',
);

/*********************/
/*** The Functions ***/
/*********************/
function db_connect($full_feature = true) {
	require_once('db_config.php');
	
	if ( $GLOBALS['db']->host_info ) {
		$GLOBALS['db']->close();
	}
	
	if(!$full_feature) {
		$GLOBALS['db'] = new mysqli( $GLOBALS['db_host'], $GLOBALS['ff_db_user'], $GLOBALS['ff_db_pass'], $GLOBALS['db_database'] )
			or die("Could not connect" . $GLOBALS['db']->connect_error);
	} else {
		$GLOBALS['db'] = new mysqli( $GLOBALS['db_host'], $GLOBALS['db_user'], $GLOBALS['db_pass'], $GLOBALS['db_database'] )
			or die("Could not connect");
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