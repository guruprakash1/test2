<?php
include('conn.php');
//include ('includes/header.php');
//include ('includes/access_login.php');
$user_id=$_SESSION['user_id'];
$err='';
if(isset($_REQUEST['submit'])){
	$current_pass = $_REQUEST['password'];
	$new_pass = $_REQUEST['new_password'];
	$con_pass = $_REQUEST['confirm_password'];
	$chkMyPassword = mysql_num_rows(mysql_query("SELECT password FROM users where id='$user_id' AND password='".md5($current_pass)."'"));
	//echo $chkMyPassword;exit;
	if($current_pass==''){
		$err="Enter Current Password";
	}else if(!$chkMyPassword){
		$err='Your current password is not valid';
	}else{
		$query = "UPDATE users SET password='".md5($new_pass)."' WHERE id='$user_id'";
		
		mysql_query($query);
		$_SESSION['success']='Password Changed successfully';
		header('location:index.php');
		exit;
	}
}
?>
<script>
function validate(){
	var current_pwd = document.getElementById('current_password');
	var new_password = document.getElementById('new_password');
	var confirm_password = document.getElementById('confirm_password');
	if(!current_pwd.value){
		alert('Enter current password');
		current_pwd.focus();
		return false;
	}
	else if(!new_password.value){
		alert('Enter New password');
		new_password.focus();
		return false;
	}
	else if(new_password.value.length <= 4){
		alert('Password length must be 5 character');
		new_password.focus();
		return false;
	}
	else if(!confirm_password.value){
		alert('Enter Confirm password');
		confirm_password.focus();
		return false;
	}else if(new_password.value != confirm_password.value){
		alert('Password do not match');
		confirm_password.focus();
		return false;
	}
}
</script>
 <link href="css/style.css" type="text/css" rel="stylesheet">
<div class="register">
  <form method="post" name="f1" onSubmit="return validate()">
    <h1>Change Password</h1>
    <table>
    <?php if($err){?>
    <tr>
        <td colspan="2" align="center"><span class="check"><?php echo  $err;?></span></td>
      </tr>
    <?php }?>
      <tr>
        <td>Current Password*:</td>
        <td><input type="password" name="password" id="current_password"></td>
      </tr>
      <tr>
        <td>New Password*:</td>
        <td><input type="password" name="new_password" id="new_password"></td>
      </tr>
      <tr>
        <td>Confirm Password*:</td>
        <td><input type="password" name="confirm_password" id="confirm_password"></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><input type="submit" name="submit" value="submit" class="sub">
          <input type="button" name="back" onclick="location.href='login.php'" value="Cancel" class="submit"> <br><br></td>
      </tr>
    </table>
  </form>
</div>

