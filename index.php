<?php
require_once('functions.php');

if($_POST['action'] == "switchmonth") {
	if(!$camefrom = $_POST['camefrom'])
		if(!$camefrom = $_SERVER['HTTP_REFERER'])
			$camefrom = 'http://s.stevish.com/budget/';
	$month = get_month_no(mktime(0, 0, 0, intval($_POST['sm_month']), 1, intval($_POST['sm_year'])));
	if(strpos($camefrom, '?'))
		$camefrom = strstr($camefrom, '?', 1);
	header("Location: $camefrom?viewmonth=$month");
}
$started = microtime(true);
if(stripos($_SERVER['HTTP_USER_AGENT'], 'android') !== false)
	$GLOBALS['mobile'] = true;
else
	$GLOBALS['mobile'] = false;

if($_COOKIE['budget']) {
	setcookie('budget', $_COOKIE['budget'], time()+60*60*24*365, '/budget/', 's.stevish.com', 1, 1);
	echo '<pre>:)</pre>';
} elseif($_POST['periodontal']) {
	setcookie('budget', senc($_POST['periodontal']), time()+60*60*24*365, '/budget/', 's.stevish.com', 1, 1);
	echo '<pre>:|</pre>';
} else
	echo '<pre>:(</pre>';
	
?><html><head>
<style type="text/css" media="screen">
	body {
		font-family: Calibri, Arial, sans;
	}
	td {
		padding-left: 10px;
		padding-right: 10px;
	}
	
	tr.odd {
		background-color: #eee;
	}
	
	tr.added {
		background-color: #ff0;
	}
	
	td.remaining {
		font-weight: bold;
		padding-left: 30px;
	}
	
	a.clickable {
		color: #00f;
		text-decoration: underline;
		cursor: pointer;
	}
	#message {
		line-height: 60px;
		padding: 10px 20px;
		background-color: #ff0;
		border: 1px solid #cc0;
	}
	#message.error {
		background-color: #f44;
		border: 1px solid #c00;
	}
	.graph {
		border-radius: 15px;
		overflow: hidden;
		border: 1px solid #333;
		box-shadow: 1px 1px 3px #555;
	}
	
</style>
<link rel="shortcut icon" href="budget.ico" type="image/x-icon">

</head><body><?php

$accounts = array(
	3 => 'Rachel\'s Visa',
	4 => 'Ally',
	1 => 'Mastercard',
	5 => 'Notes/Extra',
	6 => 'Cash',
	8 => 'NTM Account',
	7 => 'Paypal Account',
	2 => 'Air Academy',
);

if(!$_GET['viewmonth'])
	$_GET['viewmonth'] = get_month_no();
	
$viewmonth = intval($_GET['viewmonth']);

db_connect();
$catnames = array();
$bottomcatnames = array();
$result = mysql_query("SELECT * FROM `categories` ORDER BY `name` ASC");
while($row = mysql_fetch_array($result)) {
	$totalspent = $totalbudget = false;
	$totalspent = @mysql_result(mysql_query("SELECT SUM(`amount`) FROM `transactions` WHERE `category` = '{$row[0]}' AND `month` = '$viewmonth';"), 0);
	$totalbudget = @mysql_result(mysql_query("SELECT `total` FROM `budget` WHERE `category` = '{$row[0]}' AND `month` = '$viewmonth';"), 0);
	if(!$totalspent && !$totalbudget)
		$bottomcatnames[$row[0]] = $row[1];
	else
		$catnames[$row[0]] = $row[1];
}

foreach($bottomcatnames as $k => $v)
	$catnames[$k] = $v;

echo "<a href='index.php?viewmonth=" . $_GET['viewmonth'] . "'>Refresh</a><br/><br/>";

$postarray = false;
if($_POST['action'] == 'add') {
	if($_POST['nodupecheck'] == 'nodupecheck')
		$message = add_transaction(false);
	else
		$message = add_transaction();
	
	if($message === true) {
		echo "<span id='message'>The transaction was added successfully</span>";
	} elseif(!is_array($message)) {
		echo "<span id='message' class='error'>$message</span>";
		$postarray = clean_postarray($_POST);
	} else {
		verify_dupe($message);
	}
} elseif($_POST['action'] == 'carryover') {
	$message = carryover($_POST['carryover_month'], $_POST['carryover_cats']);
	if($message === true)
		echo "<span id='message'>The budgets were carried over successfully</span>";
	else
		echo "<span id='message' class='error'>$message</span>";
} elseif($_GET['message']) {
	$message = stripslashes(htmlspecialchars($_GET['message']));
	if(strtolower(substr($message, 0, 5)) == 'error')
		echo "<span id='message' class='error'>$message</span>";
	else
		echo "<span id='message'>$message</span>";
}



show_transaction_form($postarray);
show_budget($viewmonth);
show_transactions($viewmonth);
show_carryover_form();




function show_transaction_form($postarray = false) {
	db_connect();
	global $accounts, $catnames;
	$catoptions = $catoptions1 = $catoptions2 = $catoptions3 = $catoptions4 = '';
	if($_GET['viewmonth'])
		$month = intval($_GET['viewmonth']);
	else
		$month = get_month_no();
	list($date_from_month) = get_month_dates($month);
	$month_field = date('m', $date_from_month);
	$year_field = date('Y', $date_from_month);
	
	foreach($catnames as $id => $name) {
		$catoptions .= ($postarray['category'] == $id) ? '        <option value="' . $id . '" selected="selected">' . $name . "</option>\n" : '        <option value="' . $id . '">' . $name . "</option>\n";
		$catoptions1 .= ($postarray['category1'] == $id) ? '        <option value="' . $id . '" selected="selected">' . $name . "</option>\n" : '        <option value="' . $id . '">' . $name . "</option>\n";
		$catoptions2 .= ($postarray['category2'] == $id) ? '        <option value="' . $id . '" selected="selected">' . $name . "</option>\n" : '        <option value="' . $id . '">' . $name . "</option>\n";
		$catoptions3 .= ($postarray['category3'] == $id) ? '        <option value="' . $id . '" selected="selected">' . $name . "</option>\n" : '        <option value="' . $id . '">' . $name . "</option>\n";
		$catoptions4 .= ($postarray['category4'] == $id) ? '        <option value="' . $id . '" selected="selected">' . $name . "</option>\n" : '        <option value="' . $id . '">' . $name . "</option>\n";
	}
	$accountoptions = '';
	foreach($accounts as $k => $v) {
		if($postarray['account'] == $k)
			$accountoptions .= "        <option value='$k' selected='selected'>$v</option>\n";
		else
			$accountoptions .= "        <option value='$k'>$v</option>\n";
	}
?>
	<form method="POST"><br/>
		<input type="hidden" name="action" value="add" />
		Account: <select name="account"><?php echo $accountoptions; ?></select><br/>
		<input type="text" size="<?php echo $GLOBALS['mobile'] ? "2" : "1"; ?>" name="month" value="<?php echo ($postarray['month']) ? $postarray['month'] : $month_field; ?>" />/<input type="text" size="<?php echo $GLOBALS['mobile'] ? '2' : '1'; ?>" name="day" value="<?php echo ($postarray['day']) ? $postarray['day'] : date('d'); ?>" />/<input type="text" size="<?php echo $GLOBALS['mobile'] ? '4' : '3'; ?>" name="year" value="<?php echo ($postarray['year']) ? $postarray['year'] : $year_field; ?>" /><?php /*echo $GLOBALS['mobile'] ? '<br/>' : '';*/ ?>
		Payee:&nbsp;<input type="text" name="payee" <?php echo $GLOBALS['mobile'] ? 'size="18"' : ''; ?> value="<?php echo $postarray['payee']; ?>" /><?php echo /*$GLOBALS['mobile'] ? '<br/>' :*/ ' '; ?><select name="category"><?php echo $catoptions ?></select>
		<?php echo /*$GLOBALS['mobile'] ? '<br/>' : */'&nbsp;&nbsp;&nbsp; '; ?>$<input type="text" size="5" name="amount" value="<?php echo $postarray['amount']; ?>" /><br/>
		Notes: <input type="text" name="notes" value="<?php echo $postarray['notes']; ?>" /><br/>
		Password: <input type="password" <?php echo $GLOBALS['mobile'] ? 'size="8"' : ''; ?>name="periodontal" value="<?php echo $_COOKIE['budget'] ? clean_postarray(sdec($_COOKIE['budget'])) : ''; ?>" /> <input type="submit" value="Enter Transaction" /><br/><br/>
		<?php if(!$GLOBALS['mobile'] || $_GET['showfull'] == "yes") { ?>
			Split:<?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?><select name="category1"><?php echo $catoptions1 ?></select><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>$<input type="text" size="5" name="amount1"value="<?php echo $postarray['amount1']; ?>"  /><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>Notes: <input type="text" name="notes1" value="<?php echo $postarray['notes1']; ?>" /><br/>
			<select name="category2"><?php echo $catoptions2 ?></select><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>$<input type="text" size="5" name="amount2" value="<?php echo $postarray['amount2']; ?>" /><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>Notes: <input type="text" name="notes2" value="<?php echo $postarray['notes2']; ?>" /><br/>
			<select name="category3"><?php echo $catoptions3 ?></select><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>$<input type="text" size="5" name="amount3" value="<?php echo $postarray['amount3']; ?>" /><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>Notes: <input type="text" name="notes3" value="<?php echo $postarray['notes3']; ?>" /><br/>
			<select name="category4"><?php echo $catoptions4 ?></select><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>$<input type="text" size="5" name="amount4" value="<?php echo $postarray['amount4']; ?>" /><?php echo $GLOBALS['mobile'] ? '<br/>' : ' '; ?>Notes: <input type="text" name="notes4" value="<?php echo $postarray['notes4']; ?>" /><br/>
		<?php } else { ?>
			<a href='index.php?viewmonth="<?php echo $_GET['viewmonth']; ?>&showfull=yes'>Show full page (with split options)</a>"
		<?php } ?>
	</form>
<? }

function show_transactions($month) {
	if($GLOBALS['mobile'] && $_GET['showfull'] != "yes") {
		echo "<a href='index.php?viewmonth=" . $_GET['viewmonth'] . "&showfull=yes'>Show Full Page</a>";
		return false;
	}
	
	global $accounts, $catnames;
	db_connect(0);
	if(!$_POST['query'])
		$query = "SELECT * FROM `transactions` WHERE `month` = '$month' ORDER BY `date`, `group`";
	else 
		$query = stripslashes($_POST['query']);
	$result = mysql_query($query);
	$transactions = array();
	echo /*$GLOBALS['mobile'] ? '' : */'<table><tr><th>ID</th><th>Account</th><th>Date</th><th>Payee</th><th>Category</th><th>Amount</th><th>Notes</th></tr>';
	$odd = true;
	$i=0;
	while($row = mysql_fetch_assoc($result)) {
		if(!$row['group']) {
			if(is_array($transactions[$i][0]))
				$i++;
			$transactions[$i] = $row;
			$i++;
		} else {
			if(is_array($transactions[$i][0]) && $transactions[$i][0]['group'] == $row['group']) {
				$transactions[$i][] = $row;
			} elseif (is_array($transactions[$i][0])) {
				$i++;
				$transactions[$i] = array();
				$transactions[$i][] = $row;
			} else {
				$transactions[$i] = array();
				$transactions[$i][] = $row;
			}			
		}
		
	}

	foreach($transactions as $k => $v) {
		$added = false;
		if(is_array($v[0])) {
			$cats = $ids = $amounts = $notes = array();
			$row_content = "<td colspan='8'><table border='0'>";
			$total = 0;
			foreach($v as $vv) {
				if($vv['id'] == $GLOBALS['newtrans'])
					$added = true;
				$row_content .= '<tr><form action="edit_trans.php" method="POST"><td><input type="hidden" name="id" value="' . $vv['id'] . '" />' . $vv['id'] . '</td><td><select name="account">';
				foreach($accounts as $acc_k => $acc_v) {
					$row_content .= ($acc_k == $vv['account']) ? "<option selected='selected' value='$acc_k'>$acc_v</option>" : "<option value='$acc_k'>$acc_v</option>";
				}
				$row_content .= '</select></td><td><input type="text" name="month" value="' . date('m', $vv['date']) . '" size=1 />/<input type="text" name="day" value="' . date('d', $vv['date']) . '" size=1 />/<input type="text" name="year" value="' . date('Y', $vv['date']) . '" size=3 /></td><td><input type="text" name="payee" value="' . stripslashes($vv['payee']) .  '" /></td><td><select name="category">';
				foreach($catnames as $cat_k => $cat_v) {
					$row_content .= ($cat_k == $vv['category']) ? "<option selected='selected' value='$cat_k'>$cat_v</option>" : "<option value='$cat_k'>$cat_v</option>";
				}
				$row_content .= '</select></td><td>$<input type="text" size="6" name="amount" value="' . number_format($vv['amount'], 2) . '" /></td>';
				$row_content .= '<td><textarea name="notes">' . stripslashes($vv['notes']) . '</textarea></td><td><input type="submit" value="Save Changes" /></td></form></tr>';
				$total += $vv['amount'];
			}
			$row_content .= "<tr><td colspan='5'></td><td><strong>" . number_format($total, 2) . "</strong></td></tr></table>";
		} else {
			if($v['id'] == $GLOBALS['newtrans'])
				$added = true;
			$row_content = '<form action="edit_trans.php" method="POST"><td><input type="hidden" name="id" value="' . $v['id'] . '" />' . $v['id'] . '</td><td><select name="account">';
			foreach($accounts as $acc_k => $acc_v) {
				$row_content .= ($acc_k == $v['account']) ? "<option selected='selected' value='$acc_k'>$acc_v</option>" : "<option value='$acc_k'>$acc_v</option>";
			}
			$row_content .= '</select></td><td><input type="text" name="month" value="' . date('m', $v['date']) . '" size=1 />/<input type="text" name="day" value="' . date('d', $v['date']) . '" size=1 />/<input type="text" name="year" value="' . date('Y', $v['date']) . '" size=3 /></td><td><input type="text" name="payee" value="' . stripslashes($v['payee']) .  '" /></td><td><select name="category">';
			foreach($catnames as $cat_k => $cat_v) {
				$row_content .= ($cat_k == $v['category']) ? "<option selected='selected' value='$cat_k'>$cat_v</option>" : "<option value='$cat_k'>$cat_v</option>";
			}
			$row_content .= '</select></td><td><strong>$<input type="text" size="6" name="amount" value="' . number_format($v['amount'], 2) . '" /></strong></td>';
			$row_content .= '<td><textarea name="notes">' . stripslashes($v['notes']) . '</textarea></td><td><input type="submit" value="Save Changes" /></td></form>';
		}
		/*if($GLOBALS['mobile']) {
			$row_content = str_replace(array("</td>", "<td>"), array("<br/>", ""), $row_content);
			$row_content = str_replace("<br/><br/><br/>", "<br/><br/>", $row_content);
		}*/
		
		if($odd) {
			/*if($GLOBALS['mobile'])
				echo "$row_content<br/>";
			else */
				echo  $added ? "<tr class='odd added'>$row_content</tr>" : "<tr class='odd'>$row_content</tr>";
			$odd = false;
			
		} else {
			/*if($GLOBALS['mobile'])
				echo "$row_content<br/>";
			else*/
				echo $added ? "<tr class='added'>$row_content</tr>" : "<tr>$row_content</tr>";
			$odd = true;
		}
	}
	echo '</table>';

}

function add_transaction($dupecheck = true) {
	if(sha1(md5($_POST['periodontal'])) != "dd5642287c7a8b1bee2b23410a5c4fcce1c01c2e")
		return "Invalid Password";
	db_connect();
	if($_GET['viewmonth'] > 1)
		$curmonth = intval($_GET['viewmonth']);
	else
		$curmonth = get_month_no();
	$lastmonth = $curmonth - 1;
	//varsec
		if(!$date = mktime(0,0,0, $_POST['month'], $_POST['day'], $_POST['year']))
			return "Error adding date. Please check the date and try again";
		$month = get_month_no($date);
		$notes = mysql_real_escape_string($_POST['notes']);
		
		$result = mysql_query("SELECT `id` FROM `categories`");
		$isgoodcat = false;
		while($goodcat = mysql_fetch_row($result)) {
			if($goodcat[0] == $_POST['category']) {
				$isgoodcat = true;
				$category = $_POST['category'];
				break;
			}
		}
		if(!$isgoodcat) {
			return "Error: 4 (Stephen knows what this means)";
		}
		if($_POST['amount'] == '') {
			return "Please enter an amount";
		} elseif(is_numeric(preg_replace("/[^0-9\.\-]/", '', $_POST['amount']))) {
			$amount = preg_replace("/[^0-9\.\-]/", '', $_POST['amount']);
		} else {
			return 'The amount given was not a number. Maybe you tried to use a dollar sign?';
		}
		$payee = mysql_real_escape_string($_POST['payee']);
		if(!$payee)
			return "ERROR: Please set a payee";
		if(is_numeric($_POST['account']))
			$account = $_POST['account'];
		else
			return 'Error: A (Stephen knows what this means) (' . $_POST['account'] . ')';
		
		$multi = false;
		for($i = 1; $i < 5; $i++) {
			if($_POST['amount' . $i]) {
				$multi = true;
				if(is_numeric(preg_replace("/[^0-9\.\-]/", '', $_POST['amount' . $i]))) {
					$splitamount[$i] = preg_replace("/[^0-9\.\-]/", '', $_POST['amount' . $i]);
				} else {
					return 'An amount given was not a number. Maybe you tried to use a dollar sign?';
				}
				$splitnotes[$i] = mysql_real_escape_string($_POST['notes' . $i]);
				
				$result = mysql_query("SELECT `id` FROM `categories`");
				$isgoodcat = false;
				while($goodcat = mysql_fetch_row($result)) {
					if($goodcat[0] == $_POST['category' . $i] ) {
						$isgoodcat = true;
						$splitcategory[$i] = $_POST['category' . $i];
						break;
					}
				}
				if(!$isgoodcat)
					return "Error: 4s (Stephen knows what this means)";
			}
		}
	//END varsec
	
	//Check for Dupes
	if($dupecheck) {
		$result = @mysql_query("SELECT * FROM `transactions` WHERE `month` = '$month' AND `category` = '$category' AND `amount` = '$amount'");
		if(mysql_num_rows($result) > 0) {
			$return = array();
			while($row = mysql_fetch_assoc($result))
				$return[] = $row;
			return $return;
		}
	}
	
	//Enter Transaction
	if($multi) {
		$group = time();
		$transarray = array();
		$transarray[0] = array('account' => $account, 'date' => $date, 'payee' => $payee, 'amount' => $amount, 'category' => $category, 'notes' => $notes, 'group' => $group, 'month' => $month);
		for($i=1;$i<5;$i++) {
			if($splitamount[$i]) {
				$transarray[$i] = array('account' => $account, 'date' => $date, 'payee' => $payee, 'amount' => $splitamount[$i], 'category' => $splitcategory[$i], 'notes' => $splitnotes[$i], 'group' => $group, 'month' => $month);
			}
		}
		$count = 1;
		foreach($transarray as $a) {
			if(!mysql_query("INSERT INTO `transactions` (`account`, `date`, `payee`, `amount`, `category`, `notes`, `group`, `month`) VALUES ('" . $a['account'] . "', '" . $a['date'] . "', '" . $a['payee'] . "', '" . $a['amount'] . "', '" . $a['category'] . "', '" . $a['notes'] . "', '" . $a['group'] . "', '" . $a['month'] . "')")) {
				if($count == 1)
					return 'There was an error in the first final sql command: ' . mysql_error();
				else
					return "There was an error in command number $count, so the transaction was only halfway entered. Don't close this screen or enter more transactions until Stephen can look at it. I love you.";
			}
			$GLOBALS['newtrans'] = mysql_insert_id();
			$count++;
		}
		return true;
	
	} else {
		if(mysql_query("INSERT INTO `transactions` (`account`, `date`, `payee`, `amount`, `category`, `notes`, `group`, `month`) VALUES ('$account', '$date', '$payee', '$amount', '$category', '$notes', '0', '$month')")) {
			$GLOBALS['newtrans'] = mysql_insert_id();
			return true;
		} else
			return 'There was an error in the final sql command: ' . mysql_error();
	}
	
}

function show_budget($month) {
	global $catnames;
	list($date_from_month) = get_month_dates($month);
	echo "<form method='POST'><br/>Showing budget for <input size='4' name='sm_month' value='" . date('m', $date_from_month) . "' />/<input size='6' name='sm_year' value='" . date('Y', $date_from_month) . "' /><input type='hidden' name='action' value='switchmonth' /><input type='submit' value='Go To Date' /></form>"; 
	db_connect();
	echo "<table><tr><th>Category</th><th>Total</th><th>Credited</th><th>Spent</th><th>Left Over</th></tr>";
	$odd = true;
	$totaltotal = 0;
	$totalremaining = 0;
	foreach($catnames as $catid => $catname) {
		$teststart = microtime(true);
		$totalbudgeted = @mysql_result(mysql_query("SELECT `total` FROM `budget` WHERE `month` = '$month' AND `category` = '" . mysql_real_escape_string($catid) . "';"), 0);
		$r = mysql_query("SELECT `amount` FROM `transactions` WHERE `month` = '$month' AND `category` = '" . mysql_real_escape_string($catid) . "';");
		$thistotal = $thiscredited = 0;
		while($transrow = mysql_fetch_row($r)) {
			if ($transrow[0] > 0)
				$thistotal += $transrow[0];
			else
				$thiscredited -= $transrow[0];
		}
		$remaining = $totalbudgeted + $thiscredited - $thistotal;
		if($odd) {
			echo '<tr class="odd"><form action="edit_bud.php" method="POST">';
			$odd = false;
		} else {
			echo '<tr><form action="edit_bud.php" method="POST">';
			$odd = true;
		}
		$thiscredited_text = '';
		if($thiscredited > 0) {
			$thiscredited_text = '+ ' . $thiscredited;
		}
		echo make_graph($totalbudgeted, $thiscredited, $thistotal, stripslashes($catname)) . '<td><input type="hidden" name="month" value="' . $month . '" /><input type="hidden" name="category" value="' . $catid . '" /><input type="text" name="total" value="' . $totalbudgeted . '" size="5" /></td><td>' . $thiscredited_text . '</td><td>' . $thistotal . '</td></td><td class="remaining">';
		if($remaining < 0)
			echo '<span style="color: #f00;">$' . number_format($remaining, 2) . "</span></td><td><input type='submit' value='Save Changes' /></td>";
		else
			echo '$' . number_format($remaining, 2) . "</td><td><input type='submit' value='Save Changes' /></td>";
		echo "</form></tr>\n";
		
		$totaltotal += $thistotal;
		$totalremaining += $remaining;
		$totalcredited += $thiscredited;
		$totaltotalbudgeted += $totalbudgeted;
		$testend = microtime(true);
	}
	echo "</table>";
	echo "<br/><form action='add_cat.php' method='POST'><input type='text' name='name' /><input type='submit' value='Add New Category' /></form><br/>";
	echo "<br/>Total Budgeted: $totaltotalbudgeted &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total Added: $totalcredited &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total Spent: $totaltotal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total remaining: $totalremaining<br/>";

}

function verify_dupe($dupe_array) {
	global $accounts;
	$postarray = clean_postarray($_POST);
	echo '<h2>You are trying to add this transaction:</h2>';
	echo '<table><tr><th>Account</th><th>Date</th><th>Category</th><th>Amount</th></tr>';
	echo '<tr><td>' . $accounts[$postarray['account']] . '</td><td>' . $postarray['month'] . '/' . $postarray['day'] . '/' . $postarray['year'] . '</td><td>' . $postarray['category'] . '</td><td>$' . number_format($postarray['amount']) . '</td></tr>';
	echo "</table>";
	
	echo "<h2>But the following transaction(s) is(are) already in the system:</h2>";
	echo '<table><tr><th>ID</th><th>Account</th><th>Date</th><th>Category</th><th>Amount</th></tr>';
	foreach($dupe_array as $k => $v)
		echo '<tr><td>' . $v['id'] . '</td><td>' . $accounts[$v['account']] . '</td><td>' . date('m/d/Y', $v['date']) . '</td><td>' . $v['category'] . '</td><td>$' . number_format($v['amount']) . '</td></tr>';
	echo "</table>";
	
	echo '<h2>Do you still want to add this transaction?</h2>';
	echo '<form method="POST"><input type="hidden" name="nodupecheck" value="nodupecheck" />';
	
	foreach($postarray as $k => $v) {
		echo "<input type='hidden' name='$k' value='$v' />";
	}
	echo '<input type="submit" value="Yes" /></form><form method="POST"><input type="submit" value="No" /></form>';
	die();
}

function make_graph($total, $credited, $spent, $text = '') {
	if($GLOBALS['mobile']) {
		echo "<td>$text</td>";
		return;
	}
	echo '<td class="graph" style="width: 300px; height: 30px; position: relative; text-align: right; padding: 0;"><div style="position: relative; height: 30px;">';
	
	$color1 = '#0f0';
	$color2 = '#090';
	$scale = 300 / ($total + $credited);
	$totalwidth = $total * $scale;
	$creditedwidth = $credited * $scale;
	if($spent > $total) {
		$color1 = '#ff0'; //Yellow
		$color2 = '#990';
	}
	if($spent > ($total + $credited)) {
		$color1 = '#f00'; //Red
		$color2 = '#900';
		$leftwidth = ($spent - ($total + $credited)) * $scale;
	} else
		$leftwidth = ($total + $credited - $spent) * $scale;
	
	echo '<div style="background-color: ' . $color1 . '; background-image: -webkit-linear-gradient(top, ' . $color1 . ', ' . $color2 . '); height: 30px; width: ' . $leftwidth . '; position: absolute; left: 0; top: 0;"></div>';
	echo "<div style='position: absolute; right: 10px; top: 5px;'>$text</div>";
	echo '<div style="height: 10px; width: ' . $creditedwidth . ';position: absolute; left: 0; bottom: 0; background-color: #000;"></div>';	
	echo '</div></td>';

}

function carryover($from_month, $cat_array) {
	//Varsec
	global $catnames;
	foreach($cat_array as $k => $cat) {
		if(!$catnames[$cat])
			unset($cat_array[$k]);
	}
	if(count($cat_array) == 0)
		return("Error, no categories selected. Please selet the categories you want to carry over");
	$from_month = intval($from_month);
	if(sha1(md5(sdec($_COOKIE['budget']))) != "dd5642287c7a8b1bee2b23410a5c4fcce1c01c2e")
		return "Error: Invalid Password. Make sure you have a smiley face before you edit anything (to get a smily face, just put in the right password in the 'add transaction' section a few times)";
	
	//Do it!
	$datemonth = date();
	if(count($cat_array) > 1)
		$group = time();
	else
		$group = 0;
	$account = 5;
	$payee1 = "Next Month";
	$payee2 = "Last Month";
	$to_month = $from_month + 1;
	list($date2) = get_month_dates($to_month);
	$date1 = $date2 - 86300;
	foreach($cat_array as $cat) {
		$spent = mysql_result(mysql_query("SELECT sum(amount) FROM `transactions` WHERE `month` = '$from_month' AND `category` = '$cat'"), 0);
		$budgeted = mysql_result(mysql_query("SELECT `total` FROM `budget` WHERE `month` = '$from_month' AND `category` = '$cat'"), 0);
		$leftover = $budgeted - $spent;
		$leftover_neg = -1 * $leftover;
		if($leftover != 0) {
			if(!mysql_query("INSERT INTO `transactions` (`account`, `date`, `payee`, `amount`, `category`, `notes`, `group`, `month`) VALUES ('$account', '$date1', '$payee1', '$leftover', '$cat', '', '{$group}a', '$from_month')"))
				return "Couldn't insert the 'from' transaction";
			if(!mysql_query("INSERT INTO `transactions` (`account`, `date`, `payee`, `amount`, `category`, `notes`, `group`, `month`) VALUES ('$account', '$date2', '$payee2', '$leftover_neg', '$cat', '', '{$group}b', '$to_month')"))
				return "Couldn't insert the 'to' transaction";
		}
		
	}
	return true;
}

function show_carryover_form() {
	global $catnames, $viewmonth;
	if(!$GLOBALS['mobile'] || $_GET['showfull'] == "yes") {
		echo '<br/><h2>Carry budgets over to next month</h2>Happens NOW. IRREVERSABLE!<br/><form method="POST">';
		foreach($catnames as $cat_id => $cat_name) {
			echo "<input type='checkbox' name='carryover_cats[]' value='$cat_id' /> $cat_name<br/>\n";
		}
		echo '<input type="hidden" name="action" value="carryover" /><input type="hidden" name="carryover_month" value="' . $viewmonth . '" /><input type="submit" value="Do Carryover" /></form>';
	}
}

$ended = microtime(true);
$time = $ended - $started;
$testtime = $testend - $teststart;
echo "<br/><br/>Generated in $time seconds (Test: $testtime)";
?>

</body></html>