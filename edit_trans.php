<?php
require_once('functions.php');
db_connect();
if(!$camefrom = $_POST['camefrom'])
	if(!$camefrom = $_SERVER['HTTP_REFERER'])
		$camefrom = 'https://s.stevish.com/budget/';
		
if(sha1(md5(sdec($_COOKIE['budget']))) != "dd5642287c7a8b1bee2b23410a5c4fcce1c01c2e")
	budget_return("Error: Invalid Password. Make sure you have a smiley face before you edit anything (to get a smily face, just put in the right password in the 'add transaction' section a few times)");

if(!is_array($vs = varsec())) {
	budget_return($vs);
}

//Do Edit
if($GLOBALS['db']->query("UPDATE `transactions` SET `date` = '{$vs['date']}', `payee` = '{$vs['payee']}', `amount` = '{$vs['amount']}', `category` = '{$vs['category']}', `notes` = '{$vs['notes']}', `month` = '{$vs['month']}', `account` = '{$vs['account']}', `cleared` = '{$vs['cleared']}' WHERE `id` = '{$vs['id']}' LIMIT 1;"))
	budget_return("Entry successfully edited");
else
	budget_return("ERROR. The entry was NOT edited");
	
function varsec() {
	//BEGIN varsec
		if(!is_numeric($_POST['id']))
			return "Error I. Unable to proceed ";
		else
			$vs['id'] = $_POST['id'];
		if(!$vs['date'] = mktime(0,0,0, $_POST['month'], $_POST['day'], $_POST['year']))
			return "Error adding date. Please check the date and try again";
		$vs['month'] = get_month_no($vs['date']);
		$vs['notes'] = $GLOBALS['db']->real_escape_string($_POST['notes']);
		
		$curmonth = $vs['month'];
		$lastmonth = $curmonth - 1;
		
		$result = $GLOBALS['db']->query("SELECT `id` FROM `categories`");
		$isgoodcat = false;
		while( $goodcat = $result->fetch_row() ) {
			if($goodcat[0] == stripslashes($_POST['category'])) {
				$isgoodcat = true;
				$vs['category'] = $_POST['category'];
				break;
			}
		}
		if(!$isgoodcat)
			return "Error: 4 (Stephen knows what this means)";
		if(!$_POST['amount'])
			return "Error. Please enter an amount that is not zero";
		elseif(is_numeric($_POST['amount']))
			$vs['amount'] = $_POST['amount'];
		else
			return 'Error. The amount given was not a number. Maybe you tried to use a dollar sign?';
		$vs['payee'] = $GLOBALS['db']->real_escape_string($_POST['payee']);
		if(!$vs['payee'])
			return "ERROR: Please set a payee";
		if(is_numeric($_POST['account']))
			$vs['account'] = $_POST['account'];
		else
			return 'Error: A (Stephen knows what this means) (' . $_POST['account'] . ')';
		if ( false == $_POST['cleared'] ) {
			$vs[ 'cleared' ] = '0';
		} else {
			$vs[ 'cleared' ] = '1';
		}
	//END varsec
	return $vs;
}

function show_form($id) { //Depracated?
	$transinfo = $GLOBALS['db']->query("SELECT * FROM `transactions` WHERE `id` = '$id' LIMIT 1;")->fetch_assoc();
	
	if($transinfo['group']) {
		//Split Transaction
		$transinfo = $GLOBALS['db']->query("SELECT * FROM `transactions` WHERE `group` = '" . $transinfo['group'] . "';")->fetch_all( MYSQLI_ASSOC );
		echo "<form method='post'>";
		$month = date('m', $transinfo[0]['date']);
		$day = date('d', $transinfo[0]['date']);
		$year = date('Y', $transinfo[0]['date']);
		foreach($transinfo as $k => $v) {
			if($k == 0) {
				echo "Date"; 
			}
		}
	} else {
		//Single Transaction
	}
}
?>