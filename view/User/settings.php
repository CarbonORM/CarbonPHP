<!-- Content Header (Page header) -->

<section class="content-header" style="color: ghostwhite">
    <h1>
        User Settings
    </h1>
    <ol class="breadcrumb">
        <li><a href="#" style="color: ghostwhite"><i class="fa fa-dashboard"></i><?=$this->user->user_full_name?></a></li>
        <li><a href="#" style="color: ghostwhite">Profile Settings</a></li>
    </ol>
</section>

<!-- Main content -->

<section class="content" >
    <div id="alert"></div>

    <div class="row">
        <!-- Info / Settings -->
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class=""><a href="#settings" data-toggle="tab" aria-expanded="true">Settings</a></li>
                </ul>
                <div class="tab-content">
                    <!-- SETTINGS TAB -->
                    <div class="tab-pane" id="settings">

                        <!-- Form Start -->
                        <form class="form-horizontal" action="<?=SITE?>Profile/" method="post" enctype="multipart/form-data">


                            <div class="form-group col-md-12">

                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <img class="profile-user-img img-responsive img-circle" src="<?=$this->user->user_profile_pic ?>"
                                             alt="User profile picture">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputFile" class="col-sm-3 control-label">File input</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" type="file" id="InputFile" name="FileToUpload">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputName" class="col-sm-3 control-label">First Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="inputName"
                                               placeholder="<?= $this->user->user_first_name ?>" name="first_name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputName" class="col-sm-3 control-label">Last Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="inputName"
                                               placeholder="<?=$this->user->user_last_name ?>" name="first_name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail" class="col-sm-3 control-label">Email</label>
                                    <div class="col-sm-8">
                                        <input type="email" class="form-control" id="inputEmail"
                                               placeholder="<?= $this->user->user_email ?>" name="email">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputName" class="col-sm-3 control-label">Username</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="inputName" disabled="disabled"
                                               placeholder="<?= $this->user->user_username ?>" name="username">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inputSkills" class="col-sm-3 control-label">Password</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="inputSkills"
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
                                    <label for="inputExperience" class="col-sm-3 control-label">Biography</label>
                                    <div class="col-sm-8">
                                    <textarea class="form-control" id="inputExperience"
                                              placeholder="Experience"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-10">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="1"> I agree to the <a href="#">terms and
                                                conditions</a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-10">
                                    <button type="submit" class="btn btn-danger">Submit</button>
                                </div>
                            </div>

                        </form>



                    </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
            </div><!-- /.nav-tabs-custom -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</section>


<!-- /.content -->


<script>
    $(document).on('submit', 'form[data-pjax]', function(event) {
        $.pjax.submit(event, '#pjax-container')
    })
</script>