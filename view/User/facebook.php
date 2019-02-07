<?php
global $facebook, $request;

if ($request == "SignUp"):
    $alert = "It appears you do not already have an account with us.";
else:
    $alert = "You're Facebook email address matches an account that has not previously been linked to ";
endif;

?>

<div id='facebook' class="login-box">
    <div class="login-logo">
        <a href="<?= SITE ?>" style="color: #ffffff; font-size: 150%"><b>Stats</b>.Coach</a>
    </div><!-- /.login-logo -->
    <div class="login-box-body" style="background-color: #ECF0F1; color: #0c0c0c; border: medium">
        <p class="login-box-msg"><?= $alert ?></p>
        <div class="box-body">
            <a id="linkfacebook">
                <div class="callout callout-success">
                    <h4>Link My Facebook Account</h4>

                    <p>Take me to Stats.Coach</p>
                </div>
            </a>
            <a href="https://stats.coach/login/">
                <div class="callout callout-danger">
                    <h4>Don't link my Facebook</h4>

                    <p>Return to login page!</p>
                </div>
            </a>
        </div>
    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<div id="RegisterCB" class="login-box" style="display: none">
    <div class="login-logo">
        <a href="<?= SITE ?>" style="color: #ffffff; font-size: 150%"><b>Stats</b>.Coach</a>
    </div><!-- /.login-logo -->
    <div class="login-box-body" style="background-color: #ECF0F1; color: #0c0c0c; border: medium">
        <div class="box-body">

            <div id="alert"></div>

            <form data-pjax action="https://Stats.Coach/Facebook/SignUp/" method="post">

                <div class="form-group has-feedback">
                    <input type="text" class="form-control" placeholder="First Name" name="firstname"
                           value="<?= $facebook['first_name'] ?? '' ?>">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" placeholder="Last Name" name="lastname"
                           value="<?= $facebook['last_name'] ?? '' ?>">
                    <span class="glyphicon glyphicon-console form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="email" class="form-control" placeholder="Email" name="email"
                           value="<?= $facebook['email'] ?? '' ?>">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" placeholder="Username" name="username"
                           value="<?= $facebook['username'] ?? '' ?>">
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
                        <option disabled <?= (($facebook["gender"]??false) ? null : 'selected') ?>>Gender</option>
                        <option value="male" <?= (($facebook["gender"]??false) == 'male' ? 'selected' : null) ?>>Male</option>
                        <option value="female" <?= (($facebook["gender"]??false) == 'female' ? 'selected' : null) ?>>Female
                        </option>
                    </select>
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
        </div>
    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->


<script>
    Carbon((e) => {
        $('#linkfacebook').click(() => {
            $('#facebook').hide();
            $('#RegisterCB').show();
        });

        $.fn.load_iCheck('input');
    });
</script>
