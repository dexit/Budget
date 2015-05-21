<?php
require_once('functions.php');
db_connect();
if(!$camefrom = $_POST['camefrom'])
	if(!$camefrom = $_SERVER['HTTP_REFERER'])
		$camefrom = 'https://s.stevish.com/budget/';
		
if(sha1(md5(sdec($_COOKIE['budget']))) != "dd5642287c7a8b1bee2b23410a5c4fcce1c01c2e")
	budget_return("Error: Invalid Password. Make sure you have a smiley face before you edit anything (to get a smily face, just put in the right password in the 'add transaction' section a few times)");

$catnames = array();
$result = $GLOBALS['db']->query("SELECT * FROM `categories`");
while( $row = $result->fetch_row() ) {
	$catnames[$row[0]] = $row[1];
}

$name = $GLOBALS['db']->real_escape_string(htmlspecialchars($_POST['name']));
if(!$name)
	budget_return("Error. Please enter a category name before clicking \"Add Category\"");

if($GLOBALS['db']->query("INSERT INTO `categories` (`name`) VALUE ('$name');")) 
	budget_return("Category added successfully");
else
	budget_return("ERROR. The category was NOT added");
?>