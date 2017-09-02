<?php
// Create connection
session_start();
$conn = mysql_connect("localhost","root","");
mysql_select_db('test');

$success='';
if(isset($_SESSION['success'])){
	$success = $_SESSION['success'];
	unset($_SESSION['success']);
}
?>
