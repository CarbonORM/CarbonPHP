<!-- Automatic element centering -->
<div class="lockscreen-wrapper bg-gray" style="border-radius: 10px">
    <!-- User name -->
    <div class="lockscreen-name" style="text-align: center; font-size: 200%; padding-top: 40px;"><b>Jane Doe</b>
    </div>

    <!-- START LOCK SCREEN ITEM -->
    <div class="lockscreen-item">
        <!-- lockscreen image -->
        <div class="lockscreen-image">
            <img src="<?=SITE.APP_VIEW?>Img\defaults\madi.png" alt="User Image">
        </div>
        <!-- /.lockscreen-image -->

        <!-- lockscreen credentials (contains the form) -->
        <form data-pjax class="lockscreen-credentials">
            <div class="input-group">
                <input style="display: none" type="text" value="1" name="RememberMe" id="RememberMe">
                <input style="display: none" type="text" class="form-control" name="username"
                       placeholder="Username">
                <input type="password" name="password" class="form-control" placeholder="Password">

                <div class="input-group-btn">
                    <button class="btn"><i class="fa fa-arrow-right text-muted"></i></button>
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
        <a>Or sign in as a different user</a>
    </div>
    <div class="lockscreen-footer text-center" style="padding-bottom: 20px">
        Copyright &copy; 2014-2017 <b><a href="http://lilRichard.com" class="text-black">Richard
                Miles</a></b><br>
        All rights reserved
    </div>
</div>