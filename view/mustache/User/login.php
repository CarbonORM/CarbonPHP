<?php global $UserImage, $UserName, $FullName; ?>
<div class="login-box">
    <div class="login-logo">
        <a href="<?= SITE ?>" style="color: #ffffff; font-size: 150%"><b>Asset</b>Scheduler</a>
    </div><!-- /.login-logo -->
    <div class="login-box-body" style="background-color: #ECF0F1; color: #0c0c0c; border: medium">
        <p class="login-box-msg">Sign in to start your session</p>

        <div id="alert"></div>

        <?php if (empty($UserName)): ?>
            <form data-pjax action="<?= SITE ?>login/" method="post"> <!-- return false; -->
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" name="username"
                           placeholder="Username"
                           value="<?= (isset($_POST['username']) ? htmlentities($_POST['username']) : null); ?>">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" name="password" class="form-control" placeholder="Password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" name="RememberMe" value="1"> Remember Me!
                            </label>
                        </div>
                    </div><!-- /.col no-pjax -->
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                    </div><!-- /.col -->
                </div>
            </form>

            <div class="social-auth-links text-center">
                <p>- OR -</p>
                <div class="row">
                    <a href="<?= SITE ?>Cooks">
                        <div class="col-md-6">
                            <a href="<?=SITE?>Kitchen" class="btn btn-success btn-block btn-flat">Kitchen Staff</a>
                        </div>
                    </a>
                    <a href="<?= SITE ?>Cooks">
                        <div class="col-md-6">
                            <a href="<?=SITE?>Tables" class="btn btn-success btn-block btn-flat">Table Selection</a>
                        </div>
                    </a>
                </div>
            </div>

            <div class="social-auth-links text-center">
                <p>- OR -</p>
                <a class="btn btn-block btn-social btn-facebook btn-flat" href='<?php
                if (defined('FACEBOOK_APP_ID') && !empty(FACEBOOK_APP_ID)) {
                    print (new Facebook\Facebook([
                        'app_id' => FACEBOOK_APP_ID, // Replace {app-id} with your app id
                        'app_secret' => FACEBOOK_APP_SECRET,
                        'default_graph_version' => 'v2.2',
                    ]))->getRedirectLoginHelper()->getLoginUrl('https://stats.coach/Facebook/', [
                        'public_profile', 'user_friends', 'email',
                        'user_about_me', 'user_birthday',
                        'user_education_history', 'user_hometown',
                        'user_location', 'user_photos', 'user_friends']); } ?>'>
                    <i class="fa fa-facebook"></i> Sign in using Facebook</a>

                <a href="<?php
                //Call Google API
                #$client = new Google_Client();
                #$client->setAuthConfig(SERVER_ROOT.'Data/Indexes/tsconfig.json');
                #$client->setAccessType("offline");        // offline access
                #$client->setIncludeGrantedScopes(true);   // incremental auth
                #$client->addScope('email');
                //$gClient = new Google_Client();
                //$gClient->setApplicationName('Stats Coach');
                //$gClient->setClientId(GOOGLE_APP_ID);
                //$gClient->setClientSecret(GOOGLE_APP_SECRET);
                //$gClient->setRedirectUri('https://stats.coach/Google/');
                //$google_oauthV2 = new Google_Service_Oauth2($gClient);
                //$gClient->setIncludeGrantedScopes(true);   // incremental auth
                //$gClient->addScope('login');
                //print $client->createAuthUrl();
                ?>" class="btn btn-block btn-social btn-google btn-flat">
                    <i class="fa fa-google-plus"></i> Sign in using Google+</a>
            </div><!-- /.social-auth-links -->

            <br/>
            <div class="categories-bottom">
                <a href="<?= SITE . 'recover/'; ?>">Forgot password<br></a>
                <a href="<?= SITE . 'register/'; ?>" class="text-center">Register a new membership</a>
            </div>

        <?php else: ?>

            <!-- Automatic element centering -->
            <div class="lockscreen-wrapper">
                <!-- User name -->
                <div class="lockscreen-name" style="text-align: center; font-size: 200%"><b><?= $FullName ?></b>
                </div>

                <!-- START LOCK SCREEN ITEM -->
                <div class="lockscreen-item">
                    <!-- lockscreen image -->
                    <div class="lockscreen-image">
                        <img src="<?= $UserImage ?>" alt="User Image">
                    </div>
                    <!-- /.lockscreen-image -->

                    <!-- lockscreen credentials (contains the form) -->
                    <form data-pjax class="lockscreen-credentials" action="<?= SITE ?>login/" method="post">
                        <div class="input-group">
                            <input style="display: none" type="text" value="1" name="RememberMe" id="RememberMe">
                            <input style="display: none" type="text" class="form-control" name="username"
                                   placeholder="Username" value="<?= $UserName ?>">
                            <input type="password" name="password" class="form-control" placeholder="Password">

                            <div class="input-group-btn">
                                <button type="submit" class="btn"><i class="fa fa-arrow-right text-muted"></i></button>
                            </div>
                        </div>
                    </form>
                    <!-- /.lockscreen credentials -->

                </div>
                <!-- /.lockscreen-item -->
                <div class="help-block text-center">
                    Enter your password to retrieve your session
                </div>
                <div class="text-center">
                    <a href="<?= SITE ?>login/clear/">Or sign in as a different user</a>
                </div>
                <div class="lockscreen-footer text-center">
                    Copyright &copy; 2014-2017 <b><a href="http://lilRichard.com" class="text-black">Richard
                            Miles</a></b><br>
                    All rights reserved
                </div>
            </div>
            <!-- /.center -->
        <?php endif; ?>

    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<script>
    Carbon(() => {
        $.fn.load_iCheck('input');
        /*
        $.fn.load_backStreach("/Application/view/mustache/Img/augusta-master.jpg");
        let remove=()=>{$.fn.load_backStreach()};
        $(document).off("pjax:beforeSend", remove).on("pjax:beforeSend", remove)
        */
    });
</script>