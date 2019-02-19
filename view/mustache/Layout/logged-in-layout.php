<?php
global $user;
$my = $my ?? $user[$_SESSION['id']]; ?>

<header class="main-header">
    <!-- Logo -->
    <a href="<?= SITE ?>Home/" class="logo hidden-md-down">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>C</b>6</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>Carbon</b>6</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>


        <div class="navbar-custom-menu">

            <?php include 'navbar-nav.php' ?>

        </div>
    </nav>
</header>

<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar" style="height: auto;">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $my['user_profile_pic'] ?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p><?= $my['user_first_last'] ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <!-- search form -->
        <form action="<?= SITE ?>Search/" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search..."
                       onkeyup="$.fn.startApplication('<?= SITE ?>Search/'+this.value)">
                <span class="input-group-btn">
                                <button name="search" id="search-btn" class="btn btn-flat">
                                    <i class="fa fa-search"></i>
                                </button>
                              </span>
            </div>
        </form>
        <!-- /.search form -->

        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu tree" data-widget="tree">
            <li class="header">MAIN NAVIGATION</li>

            <li class="treeview menu-open">
                <a href="#">
                    <i class="fa fa-dashboard"></i> <span>Overview</span> <i
                        class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu" style="display: block;">
                    <li class="active">
                        <a href="<?= SITE ?>" onclick=""><i class="fa fa-circle-o"></i><?= $my['user_full_name'] ?>
                        </a>
                    </li>
                </ul>
            </li>

            <!--
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-calendar"></i> <span>Event Schedule</span>
                    <small class="label pull-right bg-red"></small>
                </a>
            </li>
            -->

            <li>
                <a href="#">
                    <i class="fa fa-edit"></i><span>Do stuff</span>
                </a>
            </li>

            <li class="treeview"><a href="#"><i class="fa fa-pie-chart"></i><span>You are now</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-table"></i> <span>Action</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                </ul>
            </li>


            <!-- Messages -->

            <li>
                <a href="<?=SITE?>Messages/">
                    <i class="fa fa-envelope"></i> <span>Messages</span>
                    <!--small class="label pull-right bg-yellow">1</small-->
                </a>
            </li>

            <!-- Drills -->

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-folder"></i> <span>Stats.Coach</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a href="https://stats.coach"><i class="fa fa-circle-o"></i> Is open source</a></li>
                    <li><a href="https://stats.coach"><i class="fa fa-circle-o"></i> Uses Carbon6</a></li>
                    <li><a href="https://stats.coach"><i class="fa fa-circle-o"></i> Github</a></li>
                    <li><a href="https://stats.coach"><i class="fa fa-circle-o"></i> Visit Stats.Coach</a></li>
                </ul>
            </li>


            <li class="treeview">
                <a href="#">
                    <i class="fa fa-laptop"></i>
                    <span>Account Overview</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <!--
                    <li><a href="#"><i class="fa fa-circle-o"></i> Tournament Finder</a></li>
                    -->
                    <li><a href="<?= SITE ?>Profile/" onclick=""><i class="fa fa-circle-o"></i> Profile
                            Settings</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Lipsum</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Epselon</a></li>
                </ul>
            </li>


            <li class="treeview">
                <a href="#">
                    <i class="fa fa-share"></i><span>Sports <small>(coming soon)</small></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Basketball</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Volleyball</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Soccer</a></li>

                </ul>
            </li>

            <li><a href="http://CarbonPHP.com"><i class="fa fa-book"></i><span>Documentation</span></a></li>

            <li class="header">2016 Overall Leaderboard</li>
            <li><a href="#"><i class="fa fa-circle-o text-red"></i> <span>Individual</span></a></li>
            <li><a href="#"><i class="fa fa-circle-o text-yellow"></i> <span>Team Standings</span></a></li>
            <li><a href="#"><i class="fa fa-circle-o text-aqua"></i> <span>Division</span></a></li>

        </ul>
    </section>
    <!-- /.sidebar -->
</aside>



