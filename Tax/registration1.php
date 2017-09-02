<?php
include('conn.php');
if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    header("location:index.php");
}
$fname = '';
$lname = '';
$username = '';
$email = '';
$password = '';
$cpassword = '';
$gender = '';
$hobby = array();
$aboutme = '';
$birthday = '';
$age = '';
$country = '';
$state = '';
$city = '';
$address = '';
$mobile = '';
$photo = '';
$designation = '';
$err = '';
if (isset($_POST['submit'])) {
    //print_r($_POST);exit;

    $explode = explode('.', strtolower($_FILES['profile']['name']));
    $ext = end($explode);
    $uniq_id = md5(uniqid());
    //echo $uniq_id;exit;
    $extentain = array('jpg', 'jpeg', 'png', 'gif');
    $fname = addslashes($_POST['fname']);
    $lname = addslashes($_POST['lname']);
    $username = addslashes($_POST['uname']);
    $email = addslashes($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpass'];
    $gender = $_POST['gender'];
    if (isset($_POST['checkbox'])) {
        $hobby = $_POST['checkbox'];
    }
    $pattern_phone = "/^([+0-9]{1,3})?([0-9]{10,11})$/i";
    $aboutme = $_POST['area'];
    $birthday = $_POST['dob'];
    $age = $_POST['age'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $address = addslashes($_POST['address']);
    $mobile = addslashes($_POST['mobile']);
    $designation = addslashes($_POST['designation']);

    if ($fname == '') {
        $err = "please Enter first name";
    }
    if ($lname == '') {
        $err = "please enter Last name";

        if ($username == '') {
            $err = "enter user name";
        }

        $sql = mysql_query("SELECT username FROM users WHERE username='$username'");
        $num_rows = mysql_num_rows($sql);
        //echo  $num_rows;exit;
        if ($num_rows >= 1) {
            $err = "User Id already exists";
        }
    } else if ($email == '') {
        $err = "please Enter a email";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "please Enter avalid email";
    }
    $query = mysql_query("SELECT * FROM users WHERE email='" . $email . "'");
    if (mysql_num_rows($query)) {
        $err = "email already exists";
    } else if ($password == '') {
        $err = "please enter  password";
    } else if ($cpassword == '') {
        $err = "please enter  confirm password";
    } else if ($password != $cpassword) {

        $err = "password and confirm password do not match ";
    } else if (!isset($_POST['checkbox'])) {
        $err = "please Enter hobby";
    } else if ($aboutme == '') {
        $err = "please write something about urself";
    } else if ($birthday == '') {
        $err = "please provide your date of birth";
    } else if ($age == '') {
        $err = "please provide your age";
    } else if ($country == '') {
        $err = "please provide your country name";
    } else if ($state == '') {
        $err = "please provide your state name";
    } else if ($city == '') {
        $err = "please provide your city name";
    } else if ($address == '') {
        $err = "please provide your address";
    } else if ($mobile == '') {
        $err = "please Enter a phone";
    } else if (!preg_match($pattern_phone, $mobile)) {
        $err = "Enter a valid phone No";
    } else if ($_FILES['profile']['name'] == '') {
        $err = "Please enter your photo";
    } else if (!in_array($ext, $extentain)) {
        $err = " Please enter jpg, png, gif image only";
    } else if ($designation == '') {
        $err = "please provide your  designation";
    } else if (!$err) {
        $renamePhoto = time() . rand(100, 999) . '.' . $ext;
        $path = 'files/profile/' . $renamePhoto;
        move_uploaded_file($_FILES['profile']['tmp_name'], $path);

        $myHobbies = implode('--', $hobby);
        $insert_query = mysql_query("INSERT INTO users SET uniq_id='$uniq_id', fname='" . $fname . "',
					lname='$lname',username='$username',
					email='$email', password='" . md5($password) . "',
					gender='$gender',hobby='$myHobbies',about_me='$aboutme',birthday='$birthday',
					age='$age',country='$country',state='$state',city='$city',	
					address='$address',mobile='$mobile', photo='$renamePhoto',
					designation='$designation',created=NOW()");

        if ($insert_query) {
            $_SESSION['success'] = "Record Inserted successfully.";
            header("location:registration1.php");
        }
    }
}
//include ('includes/header.php');			}
?>

<?php include('includes/header.php'); ?><head>
    <link href="css/style.css" type="text/css" rel="stylesheet">
</head>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script>
    $(function () {
        $("#datepicker").datepicker({dateFormat: 'yy-mm-dd'});
    });
</script>

<script>
    function validateXXX() {
        var fname = document.f1.fname.value;
        var lname = document.f1.lname.value;
        var uname = document.f1.uname.value;
        var email = document.f1.email.value;
        var pass = document.f1.password.value;
        var cpass = document.f1.cpass.value;
        var area = document.f1.area.value;
        var dob = document.f1.dob.value;
        var age = document.f1.age.value;
        var country = document.f1.country.value;
        var state = document.f1.state.value;
        var city = document.f1.city.value;
        var address = document.f1.address.value;
        var mobile = document.f1.mobile.value;
        var profile = document.f1.profile.value;
        var designation = document.f1.designation.value;
        var pattern = /^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/;
        // var x =/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (!fname) {
            alert("First name couldnot be blank");
            document.f1.fname.focus();
            return false;
        }
        if (fname.length < 5) {
            alert("First Name should not be less than 5 characters");
            document.f1.fname.focus();
            return false;
        }
        if (!lname) {
            alert("Last name couldnot be blank");
            document.f1.lname.focus();
            return false;
        }
        if (lname.length < 5) {
            alert("Last Name should not be less than 5 characters");
            document.f1.lname.focus();
            return false;
        }

        if (!uname) {
            alert("User name couldnot be blank");
            document.f1.uname.focus();
            return false;
        }
        if (uname.length < 4) {
            alert("User Name should not be less than 4 characters");
            document.f1.uname.focus();
            return false;
        }

        if (!email) {
            alert("email couldnot be blank");
            document.f1.email.focus();
            return false;
        }

        if (!x.test(email)) {
            alert("Please provide a valid email address");
            document.f1.email.focus();
            return false;
        }

        if (!pass) {
            alert("please provide password");
            document.f1.password.focus();
            return false;
        }

        if (pass.length < 5) {
            alert("password must be 5 character");
            document.f1.pass.focus();
            return false;
        }
        if (!cpass) {
            alert("again give same password");
            document.f1.cpass.focus();
            return false;
        }
        if (cpass.length < 5) {
            alert("confirmm password must be 5 character");
            document.f1.cpass.focus();
            return false;
        }
        if (pass != cpass) {
            alert("password donot matched");
            return false;
        }

        if (document.f1.gender[0].checked == false && document.f1.gender[1].checked == false) {
            alert("you have to choose your gender");
            return false;
        }

        var chk = document.getElementsByName('checkbox[]');
        var i;
        var count = 0;
        for (i = 0; i < chk.length; i++) {
            if (chk[i].checked == true) {
                count++;
            }
        }
        if (count === 0) {
            alert('select at least one hobby');
            return false;
        }

        if (!area) {
            alert("give details something about yourself");
            document.f1.area.focus();
            return false;
        }

        if (!dob) {
            alert("provide your date of birth");
            document.f1.dob.focus();
            return false;
        }
        if (!pattern.test(dob)) {
            alert(" provide valid date of birth only")
            document.f1.dob.focus();
            return false;
        }

        if (!age) {
            alert("provide your age");
            document.f1.age.focus();
            return false;
        }
        if (isNaN(age)) {
            alert(" provide valid age only")
            document.f1.age.focus();
            return false;
        }
        if (country == "") {
            alert("choose at least one country");
            document.f1.state.focus();
            return false;
        }

        if (state == "") {
            alert("choose at least one state");
            document.f1.state.focus();
            return false;
        }
        if (city == "") {
            alert("choose your city");
            document.f1.city.focus();
            return false;
        }
        if (address == "") {
            alert("Please enter your address");
            document.f1.address.focus();
            return false;
        }

        if (!mobile) {
            alert("provide your mobile number");
            document.f1.mobile.focus();
            return false;
        }
        if (isNaN(mobile)) {
            alert(" provide valid mobile number")
            document.f1.mobile.focus();
            return false;
        }
        if (mobile.length < 10) {
            alert("mobile number must be 10 digit only");
        }
        if (!profile) {
            alert("Upload your profile photo");
            return false;
        }

        if (!/(\.(gif|jpg|bmp|png))$/i.test(profile)) {
            alert("Imagee should be .(gif|jpg|bmp|png)type ");
            return false;
        }

        if (!designation) {
            alert("Please choose at least one designation");
            return false;
        }
    }

    function Chkemail() {
        var email = $('#email').val();
        if (email) {
            var xmlhttp;

            if (window.XMLHttpRequest)
            {// code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            }
            else
            {// code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function ()
            {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                {
                    alert(xmlhttp.responseText);
                    if (xmlhttp.responseText == 'error') {
                        $('#ermsg').show();

                    } else {
                        $('#msg').show();
                    }

//// Another way
//document.getElementById("msg").innerHTML=xmlhttp.responseText;
                }
            }
            xmlhttp.open("GET", "chkemail.php?email=" + email, true);
            xmlhttp.send();
        }
    }

    function JChkemail() {
        $('#ermsg').hide();
        $('#msg').hide();
        var email = $('#email').val();
        var x = /^[a-zA-Z0-9._-]+@[a-zA-Z]+\.[a-zA-Z]{3}$/;
        if (email) {
            if (!x.test(email)) {
                alert("Please provide a valid email address");
                document.f1.email.focus();
                return false;
            }

//$.get("chkemail.php?email="+email,function(data){

            $.post("chkemail.php", {'email': email}, function (data) {
                if (data) {
                    if (data == 'error') {
                        $('#ermsg').show();
                    }
                    else {
                        $('#msg').show();
                    }
                }
            });

        }
    }



    function Chkuname() {
        $('#ermsg1').hide();
        $('#msg1').hide();
        var username = $('#username').val();
//$.get("chkemail.php?email="+email,function(data){
//$.get("chkusername.php?username="+username,function(data){
        $.post("username.php", {'username': username}, function (data) {
            if (data) {
                if (data == 'error') {
                    $('#ermsg1').show();
                }
                else {
                    $('#msg1').show();
                }
            }
        });
    }

</script>


<div class="register">
    <form method="post" name="f1" enctype="multipart/form-data" onSubmit="return validate()">
        <table width="100%">
            <?php if ($success) { ?>
                <tr>
                    <td colspan="2" align="center"><span style="color:green; background-color:#00ffff; padding:5px; margin:5px; font-weight:bold; display:block; text-align:center;"><?php echo $success; ?></span></td>
                </tr>
            <?php } ?>
            <?php if ($err) { ?>
                <tr>
                    <td colspan="2" align="center"><span style="color:red; background-color:#993300; padding:5px; margin:5px; font-weight:bold; display:block; text-align:center;"><?php echo $err; ?></span></td>
                </tr>
            <?php } ?>
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
                <td><input type="text" name="fname"  value= "<?php echo stripslashes($fname); ?>"></td>
            </tr>
            <tr>
                <td class="label-td"><label>Lname*</label></td>
                <td><input type="text" name="lname"  value= "<?php echo stripslashes($lname); ?>"></td>
            </tr>
            <tr>
                <td class="label-td"><label>User name*</label>
                    <span style="color:#FF0000";font-weight:bold></span></td>
                <td><input type="text" name="uname"  id="username" value= "<?php echo stripslashes($username); ?>"onBlur="Chkuname()">
                    <span id="msg1" style="font-size:14px; color:green; display:none;">username available</span><span style="font-size:17px; color:#333399; display:none;" id='ermsg1'>Username exist</span>

                </td>
            </tr>   
            <tr>
                <td class="label-td"><label>Email*</label></td>
            <span style="color:#FF0000";font-weight:bold></span></td>
            <td><input type="text" name="email" id="email" value= "<?php echo stripslashes($email); ?>" onBlur="JChkemail()">
                <span id="msg" style="font-size:14px; color:green; display:none;">Email available</span><span style="font-size:17px; color:#333399; display:none;" id='ermsg'>Email exist</span>

            </td></tr>  
            <tr>
                <td class="label-td"><label>Password*</label></td>
                <td><input type="password" name="password" id="pass" value= "<?php echo stripslashes($password); ?>">
                </td>
            </tr>
            <tr>
                <td class="label-td"><label>ConfirmPassword*</label></td>
                <td><input type="password" name="cpass" id="pass" value="<?php echo stripslashes($cpassword); ?>"></td>
            </tr>
            <tr>
                <td class="label-td"><label>Gender*</label></td>
                <td><input type="radio" name="gender" checked value="male">Male&nbsp;<input type="radio" name="gender" value="female">Female</td>
            </tr>
            <tr>
                <td class="label-td"><label>Hobbies*</label></td>
                <td><input type="checkbox" id="box" name="checkbox[]" value="Playing cricket"<?php
                    if (in_array('Playing cricket', $hobby)) {
                        echo "checked";
                    }
                    ?>/>
                    Playing cricket
                    <input type="checkbox" name="checkbox[]" value="Reading stories"<?php
                    if (in_array('Reading stories', $hobby)) {
                        echo "checked";
                    }
                    ?>/>
                    Reading stories
                    <input type="checkbox"  name="checkbox[]" value="Watching TV"<?php
                    if (in_array('Watching TV', $hobby)) {
                        echo "checked";
                    }
                    ?>/>
                    Watching TV
                </td>
            </tr>
            <tr>
                <td class="label-td"><label>About Me*</label></td>
                <td class="label-td"><textarea name="area"><?php echo $aboutme; ?></textarea></td>
            </tr>
            <tr>
                <td class="label-td"><label>Dob*</label></td>
                <td><input type="text" name="dob" placeholder="DD-MM-YYYY" id="datepicker" value="<?php echo $birthday; ?>" autocomplete='off'></td>
            </tr>
            <tr>
                <td class="label-td"><label>Age*</label></td>
                <td><input type="text" name="age" value="<?php echo stripslashes($age); ?>"></td>
            </tr>
            <tr>
                <td class="label-td"><label>Country:</label></td>
                <td><select id="check" name="country">
                        <option value="">Please select your country</option>
                        <option value="INDIA"<?php if ($country == 'INDIA') echo "selected"; ?>>INDIA</option>
                        <option value="BANGLADESH" <?php if ($country == 'BANGLADESH') echo "selected"; ?>>BANGLADESH</option>
                        <option value="SRILANKA" <?php if ($country == 'SRILANKA') echo "selected"; ?>>SRILANKA</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td class="label-td"><label>State:</label></td>
                <td><select id="check" name="state">
                        <option value="">Please select your state</option>
                        <option value="ODISHA" <?php if ($state == 'ODISHA') echo "selected"; ?>>ODISHA</option>
                        <option value="BANGLORE" <?php if ($state == 'BANGLORE') echo "selected"; ?>>BANGLORE</option>
                        <option value="HYDERABAD" <?php if ($state == 'HYDERABAD') echo "selected"; ?>>HYDERABAD</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label-td"><label>City:</label></td>
                <td><select id="check" name="city">
                        <option value="">Choose Your city</option>
                        <option value="PURI" <?php if ($city == 'PURI') echo "selected"; ?>>PURI</option>
                        <option value="BBSR" <?php if ($city == 'BBSR') echo "selected"; ?>>BBSR</option>
                        <option value="JAJPUR" <?php if ($city == 'JAJPUR') echo "selected"; ?>>JAJPUR</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label-td"><label>Street address*</label></td>
                <td><input type="text" name="address" value="<?php echo stripslashes($address); ?>" ></td>
            </tr>
            <tr>
                <td class="label-td"><label>Mobile*</label></td>
                <td><input type="text" name="mobile" value="<?php echo stripslashes($mobile); ?>"></td>
            </tr>
            <tr>
                <td class="label-td"><label>Profile photo*</label></td>
                <td><input type="file" name="profile"></td>
            </tr>       
            <tr>
                <td class="label-td"><label>Designation*</label></td>
                <td><select id="check" name="designation">
                        <option value="">Please select your designation</option>
                        <option value="B.TECH" <?php if ($designation == 'B.TECH') echo "selected"; ?>>B.tech</option>
                        <option value="MCA" <?php if ($designation == 'MCA') echo "selected"; ?>>MCA</option>
                        <option value="MBA" <?php if ($designation == 'MBA') echo "selected"; ?>>MBA</option>
                    </select></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input type="submit" value="Submit" class="submit" name="submit" />
                    <input type="button" name="back" onclick="location.href = 'login.php'" value="Cancel" class="submit"> <br><br></td>

            </tr>
        </table>
    </form>
</div> 
<?php include('includes/footer.php'); ?>
		
