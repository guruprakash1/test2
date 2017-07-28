<?php
include('conn.php');
if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    header("location:index.php");
}
$err = '';
if (isset($_REQUEST['login'])) {
    $email = $_REQUEST['email'];
    $password = $_REQUEST['password'];
    $qry = mysql_query("SELECT * FROM users WHERE (email='$email' or username='$email') AND password='" . md5($password) . "'");
//    echo mysql_num_rows($qry);exit;
    if (mysql_num_rows($qry)) {
        $res = mysql_fetch_array($qry);
        $_SESSION['user_id'] = $res['id'];
        header("location:register.php");
    } else {
        $err = "login failed";
    }
}
include ('includes/header.php');
?> 
<script>
    function validate() {
        var email = document.f1.email.value;
        if (email == '') {
            alert("Enter email");
            return false;
        }
    }
</script>

<div class="register">
    <form method="post" name="f1" onSubmit="return validate()">
        <table align="center"><br>
            <?php if ($err) { ?>
                <tr>
                    <td colspan="2" align="center" style="color:red;"><?php echo $err; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td>Email or Username</td>
                <td><input type="text" name="email" /></td>
            </tr>

            <td>Password</td>
            <td><input type="password" name="password"/></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2" align="center" style="color:red;"><input type="submit" name="login" class="login" value="Login"/>&nbsp;&nbsp;&nbsp;<a href="register.php" class="login" align="center" style="color:#0033FF;">Registration</a></td>
            </tr>
        </table>
    </form>
</div>
