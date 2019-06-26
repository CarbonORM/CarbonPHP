
<div class="login-box">
    <div class="register-logo">
        <a href="<?= SITE ?>" style="color: #ffffff; font-size: 150%"><b>Asset</b>Scheduler</a>
    </div><!-- /.login-logo -->



    <div class="login-box-body">
        <p class="login-box-msg">Recover Username & Password</p>

        <form data-pjax action="<?= SITE ?>Recover/" method="post">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="user_email" placeholder="Email" >
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Recover</button>
                </div><!-- /.col -->
            </div>
        </form>
        <br>

        <div id="alert"></div>


        <a href="<?= SITE ?>Login/">Already Have an account? Login Here </a><br>
        <a href="<?=SITE?>Register/" class="text-center">Register a new membership</a>

    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });

    $(document).on('submit', 'form[data-pjax]', function (event) {
        $.pjax.submit(event, '#ajax-content')
    });

</script>
