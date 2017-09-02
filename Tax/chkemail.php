<?php
	include('conn.php');
	if($_REQUEST['email']){
		$email = $_REQUEST['email'];
		
		$sqlEmail = mysql_query("select email from users where email='".$email."'");
		/*if(mysql_num_rows($sqlEmail)){ 
			echo "<font color='red'>Email Aready exist</font>";
            
		}else{ ?>
			<font color="green">Email Address Available.</font>
            <?php
		}*/
		
		//// Another Way
		if(mysql_num_rows($sqlEmail)){ 
			echo "error";  
		}else{ 
			echo "success";
		}
	}
	
	
?>
