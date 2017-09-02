<html>
	<head>
     <link href="css/style.css" type="text/css" rel="stylesheet">
     		<script>
			function validate(){
				var fname=document.f1.fname.value;
				var lname=document.f1.lname.value;
				var uname=document.f1.uname.value;
				var email=document.f1.email.value;
				var pass=document.f1.password.value;
			    var cpass=document.f1.cpass.value; 
				var area=document.f1.area.value;
				var dob=document.f1.dob.value;
				var age=document.f1.age.value;
				var country=document.f1.country.value;
				var state=document.f1.state.value;
				var city=document.f1.city.value;
				var address=document.f1.address.value;
				var mobile=document.f1.mobile.value;
				var profile=document.f1.profile.value;
				var designation=document.f1.designation.value;
			    var x =/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
					if(!fname){
						alert("First name couldnot be blank");
						document.f1.fname.focus();
						return false;
					}
					if(fname.length<5){
						alert("First Name should not be less than 5 characters");
						document.f1.fname.focus();
						return false;
					}
					if(!lname){
						alert("Last name couldnot be blank");
						document.f1.lname.focus();
						return false;
					}
					if(lname.length<5){
						alert("Last Name should not be less than 5 characters");
						document.f1.lname.focus();
						return false;
					}
					
					if(!uname){
						alert("User name couldnot be blank");
						document.f1.uname.focus();
						return false;
					}
					if(uname.length<10){
						alert("User Name should not be less than 10 characters");
						document.f1.uname.focus();
						return false;
					}
					
					if(!email){
						alert("email couldnot be blank");
						document.f1.email.focus();
						return false;
					}
					
					if(!x.test(email)) {
						alert("Please provide a valid email address");
						document.f1.email.focus();
						return false;
					}
					
					if(!pass){
						alert("please provide password");
						document.f1.password.focus();
						return false;
					}
					
					if(pass.length<5){
						alert("password must be 5 character");
						document.f1.pass.focus();	
						return false;
					}
					if(!cpass){
						alert("again give same password"); 
						document.f1.cpass.focus();	
						return false;
						}
					if(cpass.length<5){
						alert("confirmm password must be 5 character");
						document.f1.cpass.focus();	
						return false;
						}	
					if(pass!=cpass){
						alert("password donot matched");
					    return false;
						}
					
					if(document.f1.gender[0].checked==false && document.f1.gender[1].checked==false){
						alert("you have to choose your gender"); 
						return false;
		            }	
					
					var chk = document.getElementsByName('checkbox[]');
					var i; var count = 0;
				    for(i=0;i<chk.length;i++){
				   		if(chk[i].checked == true){
							count++;
						}
				   }
				   if(count === 0){
				   	alert('select at least one hobby'); 
					return false;
					}
					
					if(!area){
						alert("give details something about yourself");
						document.f1.area.focus();
						return false;
					}
					
					if(!dob){
						alert("provide your date of birth");
						document.f1.dob.focus();
						return false;
						}
					if(isNaN(dob)){
					alert(" provide valid dob only")
					document.f1.dob.focus();
					return false;
					}
					
					if(!age){
						alert("provide your age");
						document.f1.age.focus();
						return false;
					}
					if(isNaN(age)){
					alert(" provide valid age only")
					document.f1.age.focus();
					return false;
					}
					if(country==""){
						alert("choose at least one country");
						document.f1.state.focus();
						return false;
						}
					
					if(state==""){
						alert("choose at least one state");
						document.f1.state.focus();
						return false;
						}
					if(city==""){
						alert("choose your city");
						document.f1.city.focus();
						return false;
						}
					if(address==""){
						alert("Please enter your address");
						document.f1.address.focus();
						return false;
					}
					
					if(!mobile){
						alert("provide your mobile number");
						document.f1.mobile.focus();
						return false;
					}
					if(isNaN(mobile)){
					alert(" provide valid mobile number")
					document.f1.mobile.focus();
					return false;
					}
					if(!profile){
					alert("Upload your profile photo");
					return false;
					}
					
					if (!/(\.(gif|jpg|bmp|png))$/i.test(profile)) {
					     alert("Imagee should be .(gif|jpg|bmp|png)type ");
				        return false;
						}
					
					if(!designation){
					alert("Please choose at least one designation");
					return false;
					}
			}
			
			</script>
    </head>
		<body>
        <div class="register">
 		 <form method="post" name="f1" enctype="multipart/form-data" onSubmit="return validate()">
       	 	<table>
            <tr>
       		 <td colspan="2">&nbsp;</td>
     		</tr>
     	    <tr>
            <td colspan="2"><h1>Registration Form</h1></td>
            </tr>
            <tr>
            <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <tr>
                <td class="label-td"><label>Fname*</label></td>
                <td><input type="text" name="fname"></td>
              </tr>
              <tr>
                <td class="label-td"><label>Lname*</label></td>
                <td><input type="text" name="lname"></td>
              </tr>
              <tr>
                <td class="label-td"><label>User name*</label></td>
                <td><input type="text" name="uname"></td>
              </tr>
              <tr>
                <td class="label-td"><label>Email*</label></td>
                <td><input type="text" name="email"></td>
              </tr>
			  <tr>
				<td class="label-td"><label>Password*</label></td>
				<td><input type="password" name="password" id="pass"/></td>
			  </tr>
			  <tr>
				<td class="label-td"><label>ConfirmPassword*</label></td>
				<td><input type="password" name="cpass" id="pass"/></td>
			  </tr>
              <tr>
                <td class="label-td"><label>Gender*</label></td>
                <td><input type="radio" name="gender">Male&nbsp;<input type="radio" name="gender">Female</td>
              </tr>
              <tr>
       			<td class="label-td"><label>Hobbies*</label></td>
                <td><label><input type="checkbox" id="box" name="checkbox[]" value=""/>
                      Playing cricket
                      <input type="checkbox" name="checkbox[]" value=""/>
                      Reading stories
                      <input type="checkbox"  name="checkbox[]" value=""/>
                      Watching TV
                </td>
              </tr>
              <tr>
                <td class="label-td"><label>About Me*</label></td>
                <td class="label-td"><textarea name="area"></textarea></td>
              </tr>
              <tr>
                <td class="label-td"><label>Dob*</label></td>
                <td><input type="text" name="dob" placeholder="YYYY-MM-DD"></td>
              </tr>
              <tr>
                <td class="label-td"><label>Age*</label></td>
                <td><input type="text" name="age"></td>
              </tr>
              <tr>
                <td class="label-td"><label>Country:</label></td>
                 <td><select id="check" name="country">
                        <option value="">Please select your country</option>
                        <option value="ODISHA">INDIA</option>
                        <option value="BANGLORE">BANGLADESH</option>
                        <option value="HYDERABAD">SRILANKA</option>
                      </select>
                  </td>
              </tr>
              
             <tr>
                <td class="label-td"><label>State:</label></td>
                 <td><select id="check" name="state">
                        <option value="">Please select your state</option>
                        <option value="ODISHA">ODISHA</option>
                        <option value="BANGLORE">BANGLORE</option>
                        <option value="HYDERABAD">HYDERABAD</option>
                      </select>
                  </td>
              </tr>
              <tr>
                 <td class="label-td"><label>City:</label></td>
                 <td><select id="check" name="city">
                        <option value="">Choose Your city</option>
                        <option value="ODISHA">PURI</option>
                        <option value="BANGLORE">BBSR</option>
                        <option value="CALCUTTA">JAJPUR</option>
                      </select>
                  </td>
              </tr>
              <tr>
                <td class="label-td"><label>Street address*</label></td>
                <td><input type="text" name="address"></td>
              </tr>
              <tr>
                <td class="label-td"><label>Mobile*</label></td>
                <td><input type="text" name="mobile"></td>
              </tr>
              <tr>
                <td class="label-td"><label>Profile photo*</label></td>
                <td><input type="file" name="profile"></td>
              </tr>       
              <tr>
                <td class="label-td"><label>Designation*</label></td>
                <td><select id="check" name="designation">
                        <option value="">Please select your designation</option>
                        <option value="ODISHA">B.tech</option>
                        <option value="BANGLORE">MCA</option>
                        <option value="HYDERABAD">MBA</option>
                      </select></td>
              </tr>
              <tr>
                <td colspan="2"><input type="submit" value="Submit" class="submit" /></td>
                
              </tr>
           </table>
		 </form>
		</div> 
		</body>
	</head>
</html>