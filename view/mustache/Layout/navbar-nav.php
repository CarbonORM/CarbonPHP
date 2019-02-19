<?php
    global $user;
    $m = new Mustache_Engine();
    $my = $my ?? $user[$_SESSION['id']];
?>

<ul class="nav navbar-nav">
    <!-- Messages: style can be found in dropdown.less-->
    <li id="NavMessages" class="dropdown messages-menu">
        <?=$m->render(file_get_contents(APP_VIEW . 'Messages/navigation.hbs'), (new \Model\Messages())->navigation()) ?>
    </li>
    <!-- Notifications: style can be found in dropdown.less -->
    <li id="NavNotifications" class="dropdown notifications-menu">
        <?=$m->render(file_get_contents(APP_VIEW . 'Notifications/notifications.hbs'), []) ?>
    </li>
    <!-- Tasks: style can be found in dropdown.less -->
    <li id="NavTasks" class="dropdown tasks-menu">
        <?=$m->render(file_get_contents(APP_VIEW . 'Tasks/tasks.hbs'), []) ?>
    </li>
    <!-- User Account: style can be found in dropdown.less -->

    <li class="dropdown user user-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="<?= $my['user_profile_pic'] ?>" class="user-image" alt="User Image"/>
            <span class="hidden-xs"><?= $my['user_first_last']  ?></span>
        </a>
        <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
                <img src="<?= $my['user_profile_pic'] ?>" class="img-circle" alt="User Image"/>
                <p>
                    <?= $my['user_first_last']  ?> - <?= $my['user_sport'] ?>
                    <small>Member since <?= date( 'm/d/Y', $my['user_creation_date'] ) ?></small>
                </p>
            </li>
            <!-- Menu Body -->
            <li class="user-body">
                <div class="col-xs-4 text-center">
                    <a href="#">Followers</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a href="<?=SITE?>Rounds/">Rounds</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a href="#">Following</a>
                </div>
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
                <div class="pull-left">
                    <a href="<?= SITE ?>Profile/" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                    <a href="<?= SITE ?>Logout/" class="btn btn-default btn-flat">Sign out</a>
                </div>
            </li>
        </ul>
    </li>
    <!-- Control Sidebar Toggle Button -->
    <!--li>
        <a href="" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
    </li-->
</ul>
