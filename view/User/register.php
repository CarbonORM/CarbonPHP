<?php global $firstName, $lastName, $email, $username, $gender, $teamCode, $teamName, $schoolName, $userType;  ?>
<div class="register-box" >
    <div class="register-logo">
        <a href="<?= SITE ?>" style="color: #ffffff; font-size: 150%"><b>Asset</b>Scheduler</a>
    </div><!-- /.login-logo -->

    <div class="register-box-body">
        <div id="alert"></div>

        <p class="login-box-msg">Register a new membership</p>
        <form data-pjax action="<?= SITE ?>Register/" method="post">

            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="First Name" name="firstname" value="<?= $firstName ?>">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Last Name" name="lastname" value="<?= $lastName ?>">
                <span class="glyphicon glyphicon-console form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="email" class="form-control" placeholder="Email" name="email" value="<?= $email ?>">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Username" name="username" value="<?= $username ?>">
                <span class="glyphicon glyphicon-knight form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Password" name="password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Password" name="password2">
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>
            <div class="form-group">
                <select class="form-control" name="gender" required>
                    <option disabled <?= ($gender ? null : 'selected') ?>>Gender</option>
                    <option value="male" <?= ($gender === 'male' ? 'selected' : null) ?>>Male</option>
                    <option value="female" <?= ($gender === 'female' ? 'selected' : null) ?>>Female</option>
                </select>
            </div>

            <div id="extended-signup">
            </div>

            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label>
                            <input type="checkbox" name="Terms" value="1"> I agree to the <a href="#">terms</a>
                        </label>
                    </div>
                </div><!-- /.col -->
                <div class="col-xs-4">
                    <button type="submit" name="submit" class="btn btn-primary btn-block btn-flat">Register</button>
                </div><!-- /.col -->
            </div>
        </form>

        <div class="social-auth-links text-center">
            <p>- OR -</p>
            <a href="<?php
            if (defined('FACEBOOK_APP_ID') && !empty(FACEBOOK_APP_ID)) print (new Facebook\Facebook( [
                'app_id' => FACEBOOK_APP_ID, // Replace {app-id} with your app id
                'app_secret' => FACEBOOK_APP_SECRET,
                'default_graph_version' => 'v2.2',
            ] ))->getRedirectLoginHelper()->getLoginUrl( 'https://stats.coach/Facebook/SignUp/', [
                'public_profile', 'user_friends', 'email',
                'user_about_me', 'user_birthday',
                'user_education_history', 'user_hometown',
                'user_location', 'user_photos', 'user_friends'] ); ?>" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign up using
                Facebook</a>
            <a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i> Sign up using Google+</a>
        </div>


        <br>
        <a href="<?= SITE ?>login/" class="text-center">I already have a membership</a>
    </div><!-- /.form-box -->
</div><!-- /.register-box -->
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<script>
    function extend_registration(value = null) {
        if (value===null||value===undefined) return false;
        let node = document.getElementById('extended-signup');
        switch (value) {
            case 'Athlete':
                node.innerHTML =
                    '<div class="form-group has-feedback"><input type="text" class="form-control" placeholder="Team Code (optional)" name="teamCode" value="<?= $teamCode ?>"> \
                                <span class="form-control-feedback""></span></div>';
                break;
            case 'Coach':
                node.innerHTML =
                    '<div class="form-group has-feedback"><input type="text" class="form-control" placeholder="Team Name" name="teamName" value="<?= $teamName ?>"> \
                                <span class="form-control-feedback""></span></div> \
                                <div class="form-group has-feedback"><input type="text" class="form-control" placeholder="School (optional)" name="schoolName" value="<?= $schoolName ?>"> \
                                <span class="form-control-feedback"></span></div>';
                break;
        }
    }
    extend_registration('<?= $userType ?>');




    Carbon(() => {
        $.fn.load_iCheck('input');
    });
</script>
