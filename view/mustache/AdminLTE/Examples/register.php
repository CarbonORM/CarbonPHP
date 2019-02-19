<div class="register-box" >
    <div class="register-logo">
        <a href="" style="color: #ffffff; font-size: 150%"><b>Carbon</b> 6</a>
    </div><!-- /.login-logo -->

    <div class="register-box-body">
        <div id="alert"></div>

        <p class="login-box-msg">Register a new membership</p>
        <form data-pjax action="<?= SITE ?>Register/" method="post">

            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="First Name" name="firstname">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Last Name" name="lastname">
                <span class="glyphicon glyphicon-console form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="email" class="form-control" placeholder="Email" name="email">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Username" name="username">
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
                    <option disabled>Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
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
        <a href="" class="text-center">I already have a membership</a>
    </div><!-- /.form-box -->
</div><!-- /.register-box -->

<script>
    Carbon(() => {
        $.fn.load_iCheck('input');
        $.fn.load_backStreach("https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?ixlib=rb-0.3.5&s=872a83ba6a07ac43b3e7176337665316&auto=format&fit=crop&w=1950&q=80");
        let remove=()=>$.fn.load_backStreach("<?=SITE . APP_VIEW?>Img/Carbon-green.png");
        $(document).off("pjax:beforeSend", remove).on("pjax:beforeSend", remove)
    });
</script>
