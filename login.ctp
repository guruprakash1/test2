<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo "Login"; ?></title>
        <base href="<?= HTTP_ROOT; ?>" target="">
        <link href="img/fav.png" type="images" rel="shortcut icon" />
        <meta name="viewport" content="width=device-width, user-scalable=no" />
        <link href='https://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,800italic' rel='stylesheet' type='text/css'>         
        <?php
        echo $this->Html->css(['frontend/style', 'frontend/developer-style']);
        echo $this->Html->script(['jQuery-2.1.4.min']);
        echo $this->element('script_file');
        ?> 
    </head>
    <body>
        <div class="login-box welcome-login2">
            <h2><a href="<?= HTTP_ROOT; ?>"><img src="img/logo-2.png" /></a></h2>
            <a href="<?php echo HTTP_ROOT . 'fblogin'; ?>" class="facebook-box"><img src="img/facebook-box.png" alt="Login with facebook"/></a>
            <a href="<?php echo HTTP_ROOT . 'twitterlogin'; ?>" class="facebook-box"><img src="img/twitter-login.jpg" alt="Login with twitter" /></a>
            <div class="clear"></div>
            <h3><span>or</span></h3>
            <div class="login-box-2">
                <?php echo $this->Form->create(null, ['id' => 'loginForm', 'onsubmit' => 'return ajaxLogin(this);']); ?>
                <div class="success" id="login-status-message"></div>

                <?php echo $this->Form->input('username', ['type' => "text", 'placeholder' => "Username or Email", 'label' => false, 'id' => 'login-username']); ?>  
                <?php echo $this->Form->input('password', ['type' => "password", 'placeholder' => "Password", 'label' => false, 'id' => 'login-password']); ?>

                <input type="radio" name="rememberme" id="rememberme" class="css-checkbox" /><label for="rememberme" class="css-label"> Remember me </label>
                <?= $this->Form->submit('LOGIN') ?>  
                <?= $this->Form->end() ?>
                <a href="<?= HTTP_ROOT . 'forgot-password'; ?>">Forgot Password?</a>        
            </div>
            <p>Don't have an account? <a href="<?= HTTP_ROOT . 'register'; ?>" id="sign-3">Sign up now!</a></p>
            <div class="clear"></div>
        </div>
        <?php
        echo $this->Html->script(array('jquery.validate', 'jquery.validation.functions'));
        ?> 
        <script type="text/javascript">
            $(function () {
                $("#login-username").validate({
                    expression: "if (VAL) return true; else return false;",
                    message: "Plese enter your username or password to login"
                });
                $("#login-password").validate({
                    expression: "if (VAL) return true; else return false;",
                    message: "<?php echo __('Password is required!!'); ?>"
                });
            });
        </script>
        <script>
            function ajaxLogin(form) {
                if (!$("#login-username").val()) {
                    $("#login-username").focus();
                    return false;
                }
                if (!$("#login-password").val()) {
                    $("#login-password").focus();
                    return false;
                }
                var formData = $(form).serialize();
                $("#login-status-message").html("<div class='check-login'>Authenticating....</div>").show().delay(3000).fadeOut();
                $.post(siteUrl + "users/ajaxLogin", formData, function (data) {
                    if (data.status == 'success') {
                        $("#login-status-message").html("<p style='padding:10px 0 10px 38px !important'>Login successfull.Redirecting...</p>").show().delay(10000);//.fadeOut();
                        window.location = data.url;
                    } else if (data.status == 'error') {
                        $("#login-status-message").html("<span>" + data.msg + ".</span>").show().delay(3000).fadeOut();
                    }
                }, 'json');
                return false;
            }
        </script>       
    </body>
</html>