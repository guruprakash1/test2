<?php
	include("conn.php");
	include ('includes/access_login.php');
	$user_id=$_SESSION['user_id'];
	$getprofile=mysql_fetch_array(mysql_query("SELECT * FROM users WHERE id='$user_id'"));
	//print_r($getprofile);
include ('includes/header.php');
//<div class="wrapper">
?><head>
<link href="css/style.css" type="text/css" rel="stylesheet">
</head>
<?php
	if($success){?>
    <div style="color:green; font-weight:bold; text-align:center;"><?php echo $success;?></div>
    <?php }?>
F_Name: <?php echo $getprofile['fname'];?><br> 
L_Name: <?php echo $getprofile['lname'];?><br> 
User_Name: <?php echo $getprofile['username'];?><br>
Email: <?php echo $getprofile['email'];?><br>
gender:<?php echo $getprofile['gender'];?><br>
hobby :<?php echo $getprofile['hobby'];?><br>
About me :<?php echo $getprofile['about_me'];?><br>
Birthday:<?php echo $getprofile['birthday'];?><br>
age:<?php echo $getprofile['age'];?><br>
Country:<?php echo $getprofile['country'];?><br>
state:<?php echo $getprofile['state'];?><br>
ciyy:<?php echo $getprofile['city'];?><br>
address:<?php echo $getprofile['address'];?><br>
Mobile: <?php echo $getprofile['mobile'];?><br>
photo:<?php echo $getprofile['photo'];?><br>
designation:<?php echo $getprofile['designation'];?><br>
Profile Picture: <img src="files/profile/<?php echo $getprofile['photo'];?>" alt="" width="100" /><br>
Join Date  : <?php echo date('d-M-Y h:i:sa', strtotime($getprofile['created']));?><br>

<br>
<br>
<a href="edit_profile.php">Edit Profile</a> &nbsp; | &nbsp;<a href="forgotpassword.php">Change Password</a>
</div>

<?php
include ('includes/footer.php');
?>
