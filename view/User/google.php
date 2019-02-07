<?php
global $google;

if ($google == "SignUp"){
    $alert = "It appears you do not already have an account with us.";
} else {
    $alert = "You're Facebook email address matches an account that has not previously been linked to ";
}


?>

<div class="login-box">
    <div class="login-logo">
        <a href="<?= SITE ?>" style="color: #ffffff; font-size: 150%"><b>Stats</b>.Coach</a>
    </div><!-- /.login-logo -->
    <div class="login-box-body" style="background-color: #ECF0F1; color: #0c0c0c; border: medium">
        <p class="login-box-msg"><?=$alert?></p>
        <form data-pjax action="<?= SITE ?>login/" method="post">
            <div class="box-body">
                <a href="https://stats.coach/Google/<?=$google?>/">
                    <div class="callout callout-success">
                        <h4>Link My Google Account</h4>
                        <p>Take me to Stats.Coach</p>
                    </div></a>
                <a href="https://stats.coach/login/">
                    <div class="callout callout-danger">
                        <h4>Don't link Google</h4>
                        <p>Return to login page!</p>
                    </div></a>


            </div>
        </form>

    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->