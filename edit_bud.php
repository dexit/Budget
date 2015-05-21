<?php
require_once('functions.php');
db_connect();
if(!$camefrom = $_POST['camefrom'])
	if(!$camefrom = $_SERVER['HTTP_REFERER'])
		$camefrom = 'https://s.stevish.com/budget/';
	
if(sha1(md5(sdec($_COOKIE['budget']))) != "dd5642287c7a8b1bee2b23410a5c4fcce1c01c2e")
	budget_return("Error: " . $_COOKIE['budget'] . " Invalid Password. Make sure you have a smiley face before you edit anything (to get a smily face, just put in the right password in the 'add transaction' section a few times)");
		
$catnames = array();
$result = $GLOBALS['db']->query("SELECT * FROM `categories`");
while( $row = $result->fetch_row() ) {
	$catnames[$row[0]] = $row[1];
}
	
if(!is_array($vs = varsec())) {
	budget_return($vs);
}

if ( $GLOBALS['db']->query("SELECT * FROM `budget` WHERE `month` = '{$vs['month']}' AND `category` = '{$vs['category']}';")->num_rows > 0) {
	if($GLOBALS['db']->query("UPDATE `budget` SET `total` = '{$vs['total']}' WHERE `month` = '{$vs['month']}' AND `category` = '{$vs['category']}' LIMIT 1;")) {
		budget_return("Budget successfully updated");
	} else {
		budget_return("ERROR. The budget was NOT updated");
	}
} else {
	if($GLOBALS['db']->query("INSERT INTO `budget` (`total`, `month`, `category`) VALUES ('{$vs['total']}', '{$vs['month']}', '{$vs['category']}');")) {
		budget_return("Budget successfully updated");
	} else {
		budget_return("ERROR. The budget was NOT updated");
	}
}

function varsec() {
	global $catnames;
	$vs = array();
	//BEGIN varsec
		if(!$catnames[$_POST['category']])
			return "Error I. Unable to proceed";
		else
			$vs['category'] = $_POST['category'];
		
		$vs['month'] = intval($_POST['month']);
		
		if(is_numeric($_POST['total']))
			$vs['total'] = $_POST['total'];
		else
			return "Error. The amount given was not a number. Maybe you tried to use a dollar sign?";
	//END varsec
	return $vs;
}
?>