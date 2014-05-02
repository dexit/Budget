<?php
if(!$_POST['cat1']) { ?>

	<form method="POST">
	Month: <input type="text" size="2" name="month" /><br/><br/>
	<?php
		
		for($i=1;$i<=100;$i++) {
			echo "Category: <input type='text' name='cat$i' size='45' /> &nbsp; &nbsp; &nbsp; &nbsp; Amount: $<input type='text' name='amt$i' size='5' /><br/>";
		}

	?>
	<br/>
	<input type="password" name="pass" />
	<input type="submit" value="Submit" />
	</form>

<?php
} else {
	if(sha1(md5($_POST['pass'])) != "dd5642287c7a8b1bee2b23410a5c4fcce1c01c2e")
		die("Invalid Password");
	if($_GLOBALS['con'] && mysql_ping($_GLOBALS['con'])) {
		mysql_close($_GLOBALS['con']);
	}
	$_GLOBALS['con'] = mysql_connect('localhost', 'stevish_budget', 'P&g*7wdy0{2o')
		or die("Could not connect");
	mysql_select_db('stevish_budget', $_GLOBALS['con'])
		or die("Could not select db. " . mysql_error());

	
	$month = intval($_POST['month']);
	for($i=1;$i<=100;$i++) {
		$cat = mysql_real_escape_string($_POST["cat$i"]);
		$amount = floatval($_POST["amt$i"]);
		if($cat) {
			mysql_query("INSERT INTO `budget` (`month`, `category`, `total`) VALUES ('$month', '$cat', '$amount');") or die(mysql_error());
		} else
			break;
	}
}
?>