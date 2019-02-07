<!DOCTYPE html>
<html>
<?php
include_once SERVER_ROOT . APP_VIEW . 'Layout/Head.php';
$logged_in = $_SESSION['id'] ?? false;
?>
<!-- Full Width Column -->
<body class="skin-purple sidebar-mini sidebar-collapse fixed" style="background-color: #ECF0F1">

<div class="wrapper" style="background: rgba(0,0,0,0.7)">
    <?php include_once SERVER_ROOT . APP_VIEW . 'Layout/Styles.php'; ?>

    <!-- Main Header -->
    <header class="main-header">
        <!-- Logo -->
        <a href="/" class="logo" id="mytitle">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>C</b>6</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>Carbon</b>PHP & <b>C</b>6</span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <?php if ($_SESSION['id'] ?? false): ?>
                        <li><a href="<?= SITE ?>Profile">Profile</a></li>
                        <li><a href="<?= SITE ?>LiveChat">Live Chat</a></li>
                    <?php else: ?>
                        <!--li><a href="<?= SITE ?>login">Login</a></li-->
                    <?php endif; ?>
                    <li><a href="http://Miles.Systems/">Miles.Systems</a></li>
                    <li><a href="http://Stats.Coach/">Stats.Coach</a></li>
                </ul>
            </div>
        </nav>
    </header><!-- Left side column. contains the logo and sidebar -->

    <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

            <!-- search form -->
            <form method="get" class="sidebar-form" id="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search..." id="search-input">
                    <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
                </div>
            </form>
            <!-- /.search form -->

            <!-- Sidebar Menu -->
            <ul class="sidebar-menu tree" data-widget="tree">
                <li class="header">TABLE OF CONTENTS</li>
                <li>
                    <a href="<?= SITE ?>CarbonPHP"><i class="fa fa-microchip"></i> <span>CarbonPHP</span></a>
                </li>
                <li>
                    <a href="<?= SITE ?>Dependencies"><i class="fa fa-handshake-o"></i>
                        <span>Dependencies</span></a></li>
                <li class="treeview">
                    <a href="#"><i class="fa fa-th"></i> <span>Quick Start</span>
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="<?= SITE ?>Environment"><i class="fa fa-file-code-o"></i>Environment</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Installation"><i
                                        class="fa fa-code-fork"></i><span>Installation</span></a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>FileStructure"><i class="fa fa-folder"></i>File Structure</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Options"><i class="fa fa-filter"></i>Options &amp; Index</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Bootstrap"><i class="fa fa-road"></i>Bootstrap</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Wrapper"><i class="fa fa-window-restore"></i>Wrapper</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Parallel"><i class="fa fa-arrows-alt"></i>Parallel Processing</a>
                        </li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#"><i class="fa fa-code"></i> <span>PHP Applications</span>
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="<?= SITE ?>Overview"><i class="fa fa-universal-access"></i> Overview</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Route"><i class="fa fa-paper-plane-o"></i> Route</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Request"><i class="fa fa-barcode"></i> Request</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Entities"><i class="fa fa-pencil-square-o"></i> Database &amp; Entities</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Session"><i class="fa fa-users"></i> Session</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Singleton"><i class="fa fa-superpowers"></i> Singleton</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>Server"><i class="fa fa-server"></i> Server</a>
                        </li>
                        <li>
                            <a href="<?= SITE ?>View"><i class="fa fa-desktop"></i> View</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?= SITE ?>OSSupport"><i class="fa fa-chrome"></i><span>Browser & OS Support</span></a>
                </li>
                <li>
                    <a href="<?= SITE ?>UIElements"><i class="fa fa-diamond"></i><span>UI Elements</span></a>
                </li>
                <li>
                    <a href="<?= SITE ?>Implementations"><i class="fa fa-bookmark-o"></i><span>Implementations</span></a>
                </li>
                <li>
                    <a href="<?= SITE ?>Support"><i class="fa fa-question-circle-o"></i><span>Support</span></a>
                </li>
                <li>
                    <a href="<?= SITE ?>License"><i class="fa fa-file-text-o"></i> <span>License</span></a>
                </li>
                <li>
                    <a href="<?= SITE ?>AdminLTE"><i class="fa fa-star-o"></i><span>AdminLTE</span></a>
                </li>
            </ul>
            <!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
    </aside>
    <script>//--  Sidebar Search Engine
        Carbon(() => {
            let $menu = $('li');

            let activity = function () {
                $("li a").filter(function () {
                    $menu.removeClass('active');
                    return this.href === location.href.replace(/#.*/, "");
                }).parent().addClass("active");
            };

            activity();

            $menu.click(function () {
                $menu.removeClass('active');
                $(this).addClass('active');
            });

            $('#mytitle').click(function () {
                $menu.removeClass('active');
            });

            $('#sidebar-form').on('submit', function (e) {
                e.preventDefault();
            });

            $('.sidebar-menu li.active').data('lte.pushmenu.active', true);

            $('#search-input').on('keyup', function () {
                let term = $('#search-input').val().trim(),
                    sidebar = $('.sidebar-menu li');

                if (term.length === 0) {
                    sidebar.each(function () {
                        $(this).show(0);
                        $(this).removeClass('active');
                        if ($(this).data('lte.pushmenu.active')) {
                            $(this).addClass('active');
                        }
                    });
                    return;
                }

                sidebar.each(function () {
                    if ($(this).text().toLowerCase().indexOf(term.toLowerCase()) === -1) {
                        $(this).hide(0);
                        $(this).removeClass('pushmenu-search-found', false);

                        if ($(this).is('.treeview')) {
                            $(this).removeClass('active');
                        }
                    } else {
                        $(this).show(0);
                        $(this).addClass('pushmenu-search-found');

                        if ($(this).is('.treeview')) {
                            $(this).addClass('active');
                        }

                        let parent = $(this).parents('li').first();
                        if (parent.is('.treeview')) {
                            parent.show(0);
                        }
                    }

                    if ($(this).is('.header')) {
                        $(this).show();
                    }
                });

                $('.sidebar-menu li.pushmenu-search-found.treeview').each(function () {
                    $(this).find('.pushmenu-search-found').show(0);
                });
            });

        })
    </script>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="background: transparent">
        <!--  style="background: transparent"  Add this to use the backstretch fn-->
        <div id="alert"></div>
        <!-- content -->
        <div class="col-md-offset-1 col-md-10">
            <div id="pjax-content">
                <?= \CarbonPHP\View::$bufferedContent ?? '' ?>
            </div>
        </div>
        <!-- /.content -->

        <div class="clearfix"></div>
        <!-- /.container -->
    </div>


    <!-- /.content-wrapper -->
    <footer class="main-footer bg-black" style="border-top-color:black">
        <div class="container">
            <div class="pull-right hidden-xs">
                <a href="<?= SITE ?>Privacy/" class="text-purple">Privacy Policy</a> <b>Version</b> <?= SITE_VERSION ?>
            </div>
            <strong>Copyright &copy; 2014-2017 <a href="https://miles.systems/" class="text-purple">Richard
                    Miles</a>.</strong>
            <!--script type="text/javascript" src="https://cdn.ywxi.net/js/1.js" async></script-->
        </div>
        <!-- /.container -->
    </footer>
</div>
<?php
include_once SERVER_ROOT . APP_VIEW . 'Layout/Scripts.php';
?>
</body>
</html>
