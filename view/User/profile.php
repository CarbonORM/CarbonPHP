<?php // This page is dynamic for any user

global $user, $user_id;

$my = $my ?? $user[$_SESSION['id']];

$myAccountBool = empty($user_id) || $user_id === $_SESSION['id'];

$profile = (!$myAccountBool ? $user[$user_id] : $my);

?>

<!-- Content Header (Page header) -->
<section class="content-header" style="color: ghostwhite">
    <h1>
        <?= $profile['user_first_last'] ?>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#" style="color: ghostwhite"><i class="fa fa-dashboard"></i>Home</a></li>
        <li class="active" style="color: ghostwhite"><a href="#" style="color: ghostwhite">Profile</a></li>
    </ol>
    <p></p>
</section>
<!-- Main content -->

<section class="content">
    <div id="alert"></div>
    <div class="row">
        <div class="col-md-12">
            <!-- Profile Image -->
            <div class="box box-primary" data-widget="">
                <div class="box-body box-profile">
                    <img class="profile-user-img img-responsive img-circle"
                         src="<?= $profile['user_profile_pic'] ?>" alt="User profile picture">
                    <h3 class="profile-username text-center">
                        <?= $profile['user_first_last'] ?>
                    </h3>
                    <p class="text-muted text-center">Golfer</p>
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Followers</b>
                            <a class="pull-right"><?= count($profile['followers']) ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Following</b>
                            <a class="pull-right"><?= count($profile['following']) ?></a>
                        </li>
                    </ul>
                    <?php if (!$myAccountBool) {
                        $following = in_array($user_id, $my['following'], false); ?>
                        <a style="display: <?= $following ? 'none' : 'block' ?>"
                           onclick="follow()"
                           class="btn btn-primary btn-block" id="FollowUser"><b>Follow :)</b></a>
                        <a style="display: <?= $following ? 'block' : 'none' ?>"
                           onclick="unfollow()"
                           class="btn btn-success btn-block" id="UnfollowUser"><b>Unfollow :(</b></a>
                        <script>
                            function follow() {
                                $.fn.startApplication('<?= SITE ?>Follow/<?= $user_id ?>/');
                                document.getElementById('FollowUser').style.display = 'none';
                                document.getElementById('UnfollowUser').style.display = 'block';
                            }

                            function unfollow() {
                                $.fn.startApplication('<?= SITE ?>Unfollow/<?= $user_id ?>/');
                                document.getElementById('FollowUser').style.display = 'block';
                                document.getElementById('UnfollowUser').style.display = 'none';
                            }
                        </script>
                    <?php } else { ?>
                        <a href="<?= SITE ?>Messages/" class="btn btn-success btn-block">Messages</a>
                    <?php } ?>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
            <?php if (!$myAccountBool) {
                print '<div class="box box-success direct-chat direct-chat-success"></div>';
            } ?><!-- DIRECT CHAT SUCCESS -->


            <!-- Display User Info -->
            <div class="col-md-auto">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <i class="fa fa-user"></i>
                        <h3 class="box-title">Profile</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <dl class="dl-horizontal">
                            <dt>About Me</dt>
                            <dd><?= $profile['user_about_me'] ?? 'Come on, nothing??' ?></dd>
                            <br>
                            <dt>Birthday</dt>
                            <dd><?= $profile['user_birthday'] ?? '' ?></dd>
                            <br>
                            <dt>Education History</dt>
                            <dd><?= $profile['user_education_history'] ?></dd>
                            <br>
                            <dt>Mutual Friends</dt>
                            <dd><?= $profile['user_facebook_id'] ?? 'Connect to Facebook'; ?></dd>
                        </dl>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.user info -->
            <?php if ($myAccountBool): ?>
                <div class="col-md-auto">
                    <!-- Horizontal Form -->
                    <div class="box box-info" id="ProfileSettings">
                        <div class="box-header with-border">
                            <h3 class="box-title">Profile Settings</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                            class="fa fa-minus"></i></button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i
                                            class="fa fa-remove"></i></button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form data-pjax class="form-horizontal" action="<?= SITE ?>Profile/" method="post"
                              enctype="multipart/form-data">

                            <div class="box-body">
                                <div class="form-group col-md-12">

                                    <div class="form-group">
                                        <label for="exampleInputFile" class="col-sm-3 control-label">Profile
                                            Picture</label>
                                        <div class="col-sm-8">
                                            <input class="form-control" type="file" id="InputFile"
                                                   name="FileToUpload">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="first" class="col-sm-3 control-label">First Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="first"
                                                   placeholder="<?= $profile['user_first_name'] ?>"
                                                   name="first_name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName" class="col-sm-3 control-label">Last Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="lastName"
                                                   placeholder="<?= $profile['user_last_name'] ?>" name="last_name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName" class="col-sm-3 control-label">Birthday:</label>
                                        <div class="col-sm-8">
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input name="datepicker" type="text" class="form-control pull-right"
                                                       id="datepicker"
                                                       value="<?= $profile['user_birthday'] ?>">
                                            </div>
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <div class="form-group <?= $profile['user_email_confirmed'] === 0 ? 'has-error' : 'has-success' ?>">
                                        <label for="inputEmail" class="col-sm-3 control-label">Email</label>
                                        <div class="col-sm-8">
                                            <input type="email" class="form-control" id="inputEmail"
                                                   placeholder="<?= $profile['user_email'] ?>" name="email">
                                            <span
                                                    class="help-block"><?= $profile['user_email_confirmed'] === 1 ?
                                                    'Email Verified' :
                                                    'Please check this email to activate your account!' ?></span>

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="username" class="col-sm-3 control-label">Username</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="username"
                                                   disabled="disabled"
                                                   placeholder="<?= $profile['user_username'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="col-sm-3 control-label">Password</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="password"
                                                   placeholder="Protected" name="password">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="inputGender" class="col-sm-3 control-label">Gender</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="inputGender" name="gender">
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="inputExperience" class="col-sm-3 control-label">About Me</label>
                                        <div class="col-sm-8">
                                                <textarea name="about_me" class="form-control" id="inputExperience"
                                                          placeholder="<?= $profile['user_about_me'] ?>"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-10">
                                        <div class="checkbox">
                                            <label>
                                                <input name='Terms' type="checkbox" value="1"> I agree to the
                                                <a href="<?= SITE ?>Privacy/">terms and conditions</a>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- /.box-body -->
                            <div class="box-footer">
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal">
                                    Delete Account
                                </button>
                                <button type="reset" class="btn btn-default">Reset Form</button>
                                <button type="submit" name="terms" class="btn btn-info pull-right">Submit</button>
                            </div>

                            <!-- Modal -->
                            <div class="modal fade" id="myModal" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <h4 class="modal-title" id="myModalLabel">Please confirm your account
                                                deletion</h4>
                                        </div>
                                        <div class="modal-body">
                                            Your account will no longer be visible or accessible. Please ensure you
                                            export
                                            and download all of your information and golf data.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-dismiss="modal">Take me
                                                back
                                            </button>
                                            <a href="<?= site ?>Profile/DeleteAccount/" type="button"
                                               class="btn btn-danger">Delete Account</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- /.box-footer -->
                        </form>
                    </div>
                    <!-- /.box -->
                </div>
            <?php endif; ?>

        </div>

    </div><!-- /.row -->
</section>

<script>Carbon((e) => {
        $.fn.load_datepicker('#datepicker');
        <?php if (!$myAccountBool):?>
        $.fn.startApplication('<?= SITE . 'Messages/' . $user_id ?>/');
        <?php endif; ?>
    })</script>

<!-- /.content -->

