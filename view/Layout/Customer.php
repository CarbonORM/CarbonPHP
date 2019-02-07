<?php
global $user;
$logged_in = $_SESSION['id'] ?? false;
?>


<header class="main-header">
    <!-- Logo -->
    <a href="<?= SITE ?>Home/" class="logo hidden-md-down">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>R</b>R</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>Restau</b>Rants<b>#<?= $_SESSION['table'] ?? false ?></b></span>
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
            <?php if ($logged_in) {
                include 'navbar-nav.php';
            } else { ?>
                <ul class="nav navbar-nav">
                    <li><a href="<?= SITE . 'Table' . DS . $_SESSION['table'] . DS . 'login' ?>">Login</a></li>
                    <li><a href="<?= SITE . 'Register' ?>">Register</a></li>
                </ul>
            <?php } ?>

        </div>
    </nav>
</header>

<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <?php
        if ($logged_in): ?>
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
        <?php endif; ?>
        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input id="search-input" type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree" id="left-sidebar-menu">
            <li>
                <a href="<?= SITE . 'Table' . DS . $_SESSION['table'] . DS . 'Bill' ?>">
                    <i class="fa fa-edit"></i><span>View Check</span>
                </a>
            </li>

            <li class="treeview"><a href="#"><i class="fa fa-globe"></i><span>Drinks</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>

                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Mr. Pibb </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Sprite </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Mountain Dew </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Mug Root Beer </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Pepsi </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Dr Pepper </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coke </a></li>
                </ul>

            </li>

            <li class="treeview"><a href="#"><i class="fa fa-beer"></i><span>Beer</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>

                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Bud light. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> 100. Dale's Pale Ale. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Brooklyn Brewery Lager. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Surly Brewing Darkness. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> New Belgium Fat Tire. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Gigantic IPA. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> NoDa Hop Drop n Roll. </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Sam Adams Boston Lager. </a></li>
                </ul>

            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-flag-o"></i> <span>Appetizers</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                </ul>
            </li>


            <!-- Entrees -->

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-fire"></i> <span>Entrees</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                </ul>
            </li>

            <!-- Deserts -->

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-birthday-cake"></i> <span>Deserts</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Coming Soon </a></li>
                </ul>
            </li>
            <li>
                <a href="<?= SITE . 'Table' . DS . $_SESSION['table'] . DS . 'Games' ?>">
                    <i class="fa fa-gamepad"></i><span>Arcade</span>
                </a>
            </li>
            <?php if ($logged_in) : ?>
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
            <?php endif; ?>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

<script>//--  Sidebar Search Engine
    Carbon(() => {
        let $menu = $('#left-sidebar-menu' + ' li');

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

        $('#left-sidebar-menu li.active').data('lte.pushmenu.active', true);

        $('#search-input').on('keyup', function () {
            let term = $('#search-input').val().trim(),
                sidebar = $('#left-sidebar-menu li');

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

            $('#left-sidebar-menu li.pushmenu-search-found.treeview').each(function () {
                $(this).find('.pushmenu-search-found').show(0);
            });
        });

    })
</script>
