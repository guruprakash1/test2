<?php
	include('conn.php');
	if($_REQUEST['username']){
		$username = $_REQUEST['username'];
		
		$sqlEmail = mysql_query("select username from users where username='".$username."'");
		//if(mysql_num_rows($sqlEmail)){ 
//			echo "<font color='red'>Error</font>";
//            
//		}else{
//		}
		
		// Another Way
		if(mysql_num_rows($sqlEmail)){ 
			echo "error";  
		}else{ 
			echo "success";
		}
	}
	
	
?>
