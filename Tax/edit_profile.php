<?php
//error_reporting(0);
include('conn.php');
include ('includes/access_login.php');
$id = $_SESSION['user_id'];
$getDetail = mysql_fetch_array(mysql_query("Select * from users WHERE id='$id'"));
$fname = $getDetail['fname'];
$lname = $getDetail['lname'];
$username = $getDetail['username'];
$email = $getDetail['email'];
$gender = $getDetail['gender'];
$mobile = $getDetail['mobile'];
$aboutme = $getDetail['about_me'];
$dob = $getDetail['birthday'];
$age = $getDetail['age'];
$country = $getDetail['country'];
$address = $getDetail['address'];
$photo = $getDetail['photo'];
$state = $getDetail['state'];
$country = $getDetail['country'];
$designation = $getDetail['designation'];
$city = $getDetail['city'];
$hobby = explode('-', $getDetail['hobby']);
$err = '';

if (isset($_POST['submit'])) { //echo "<pre>"; print_r($_FILES); exit;
    //$ext = substr(strrchr($_FILES['photo']['name'],'.'),1);
    $explode = explode('.', strtolower($_FILES['profile']['name']));
    $ext = end($explode);
    $extentain = array('jpg', 'jpeg', 'png', 'gif');
    $fname = addslashes($_POST['fname']);
    $lname = addslashes($_POST['lname']);
    $username = addslashes($_POST['uname']);
    $email = addslashes($_POST['email']);

    $gender = $_POST['gender'];
    if (isset($_POST['checkbox'])) {
        $hobby = $_POST['checkbox'];
    }
    //$pattern_phone = "/^([+0-9]{1,3})?([0-9]{10,11})$/i";
    $aboutme = $_POST['area'];
    $birthday = $_POST['dob'];
    $age = $_POST['age'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $address = addslashes($_POST['address']);
    $mobile = addslashes($_POST['mobile']);
    $designation = addslashes($_POST['designation']);
    $myHobbies = implode('-', $hobby);

    if ($photo) {
        if ($getDetail['photo']) {
            unlink('files/profile/' . $getDetail['photo']);
        }
        $renamePhoto = time() . rand(100, 999) . '.' . $ext;
        $path = 'files/profile/' . $renamePhoto;
        move_uploaded_file($_FILES['profile']['tmp_name'], $path);
        //echo "UPDATE users SET name='".$name."', email='$email', phone='$phone', website='$website', photo='$renamePhoto', modified=NOW() WHERE id='$id'"; exit;
        $insert_query = mysql_query("UPDATE  users SET fname='" . $fname . "',
					lname='$lname',username='$username',
					email='$email',
					gender='$gender',hobby='$myHobbies',about_me='$aboutme',birthday='$birthday',
					age='$age',country='$country',state='$state',city='$city',	
					address='$address',mobile='$mobile', photo='$renamePhoto',
					designation='$designation',created=NOW(),modified=NOW() WHERE id='$id'");
    } else {
        $insert_query = mysql_query("UPDATE  users SET fname='" . $fname . "',
					lname='$lname',username='$username',
					email='$email',
					gender='$gender',hobby='$myHobbies',about_me='$aboutme',birthday='$birthday',
					age='$age',country='$country',state='$state',city='$city',	
					address='$address',mobile='$mobile', photo='$renamePhoto',
					designation='$designation',created=NOW(),modified=NOW() WHERE id='$id'");
    }

    if ($insert_query) {
        $_SESSION['success'] = 'Profile Updated Successfully';
        header("location:index.php");
    }
}
include ('includes/header.php');
?>
<head>
    <link href="css/style.css" type="text/css" rel="stylesheet">
</head>
<div class="register">
    <form method="post" name="f1" enctype="multipart/form-data" onSubmit="return validate()">
        <span class="check"><?php echo $err; ?></span>
        <h1>Edit Profile</h1>
        <table width="100%">
            <?php if ($err) { ?>
                <tr>
                    <td colspan="2" align="center"><span style="color:red; background-color:#993300; padding:5px; margin:5px; font-weight:bold; display:block; text-align:center;"><?php echo $err; ?></span></td>
                </tr>
            <?php } ?>
            <?php if ($success) { ?>
                <tr>
                    <td colspan="2" align="center"><span style="color:green; background-color:#3333CC; padding:5px; margin:5px; font-weight:bold; display:block; text-align:center;"><?php echo $success; ?></span></td>
                </tr>
            <?php } ?> 
            <tr>
            <tr>
                <td >FirstName</td>
                <td><input type="text" name="fname"  value= "<?php echo stripslashes($fname); ?>"></td>
            </tr>
            <tr>
                <td >LastName*</td>
                <td><input type="text" name="lname"  value= "<?php echo stripslashes($lname); ?>"></td>
            </tr>
            <tr>
                <td>User name*</td>
                <td><input type="text" name="uname"  value= "<?php echo stripslashes($username); ?>"></td>
            </tr>
            <tr>
                <td >Email*</td>
                <td><input type="text" name="email"  value= "<?php echo stripslashes($email); ?>"></td>
            </tr>

            <tr>
                <td >Gender*</td>
                <td><input type="radio" name="gender" checked value="male">Male&nbsp;<input type="radio" name="gender" value="female">Female</td>
            </tr>
            <tr>
                <td >Hobbies*</td>
                <td><input type="checkbox" id="box" name="checkbox[]" value="Playing cricket"<?php if (in_array('Playing cricket', $hobby)) {
                echo "checked";
            } ?>/>
                    Playing cricket
                    <input type="checkbox" name="checkbox[]" value="Reading stories"<?php if (in_array('Reading stories', $hobby)) {
                echo "checked";
            } ?>/>
                    Reading stories
                    <input type="checkbox"  name="checkbox[]" value="Watching TV"<?php if (in_array('Watching TV', $hobby)) {
                echo "checked";
            } ?>/>
                    Watching TV
                </td>
            </tr>
            <tr>
                <td>About Me*</td>
                <td ><textarea name="area"><?php echo stripslashes($aboutme); ?></textarea></td>
            </tr>
            <tr>
                <td >Dob*</td>
                <td><input type="text" name="dob" placeholder="YYYY-MM-DD" value="<?php echo stripslashes($dob); ?>"></td>
            </tr>
            <tr>
                <td >Age*</td>
                <td><input type="text" name="age" value="<?php echo stripslashes($age); ?>"></td>
            </tr>
            <tr>
                <td >Country:</td>
                <td><select id="check" name="country">
                        <option value="">Please select your country</option>
                        <option value="INDIA"<?php if ($country == 'INDIA') echo "selected"; ?>>INDIA</option>
                        <option value="BANGLADESH"<?php if ($country == 'BANGLADESH') echo "selected"; ?>>BANGLADESH</option>
                        <option value="SRILANKA"<?php if ($country == 'SRILANKA') echo "selected"; ?>>SRILANKA</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>State:</td>
                <td><select id="check" name="state">
                        <option value="">Please select your state</option>
                        <option value="ODISHA" <?php if ($state == "ODISHA") echo "selected" ?>>ODISHA</option>
                        <option value="BANGLORE"<?php if ($state == "BANGLORE") echo "selected" ?>>BANGLORE</option>
                        <option value="HYDERABAD"<?php if ($state == "HYDERABAD") echo "selected" ?>>HYDERABAD</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>City:</td>
                <td><select id="check" name="city">
                        <option value="" >Choose Your city</option>
                        <option value="PURI"<?php if ($city == "PURI") echo "selected" ?>>PURI</option>
                        <option value="BBSR"<?php if ($city == "BBSR") echo "selected" ?>>BBSR</option>
                        <option value="JAJPUR"<?php if ($city == "JAJPUR") echo "selected" ?>>JAJPUR</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td >Street address*</td>
                <td><input type="text" name="address" value="<?php echo stripslashes($address); ?>"></td>
            </tr>
            <tr>
                <td >Mobile*</td>
                <td><input type="text" name="mobile" value="<?php echo stripslashes($mobile); ?>"></td>
            </tr>
            <tr>
                <td>Profile photo*</td>
                <td><input type="file" name="profile"> <?php if ($photo) { ?>
                        <img src="files/profile/<?php echo $photo; ?>" alt="" width="50">
<?php } ?></td>
            </tr>       
            <tr>
                <td class="label-td"><label>Designation*</label></td>
                <td><select name="designation" id="check">
                        <option value="">Please select your designation</option>
                        <option value="B.tech"<?php if ($designation == "B.tech") echo "selected"; ?>>B.tech</option>
                        <option value="MCA"<?php if ($designation == "MCA") echo "selected"; ?>>MCA</option>
                        <option value="MBA"<?php if ($designation == "MBA") echo "selected"; ?>>MBA</option>
                    </select></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Submit" class="submit" name="submit" /></td>
            </tr>
        </table>
    </form>
</div>
<?php include('includes/footer.php'); ?>
