<html>
<head>
<link href="../css/style.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div class="main-div">
<div class="header">
 <h1 align="center" style="color:blue;margin-left:30px; background-color:#33CC66; width:500px;
margin:0 auto;">This is a heading.</h1>
<div class="menu">
	<ul>
    	<?php if(isset($_SESSION['user_id'])){?>
            <li><a href="index.php">Home</a></li>
            <li><a href="edit_profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php }else{?>
            <li><a href="index.php">Home</a></li>
            <li><a href="registration1.php">Register</a></li>
            <li><a href="login.php">Login</a></li>
        <?php }?>
    </ul>
</div>
</div>