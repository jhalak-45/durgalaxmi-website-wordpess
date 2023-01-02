<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="description" content="<?php bloginfo() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=true">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <title><?php bloginfo() ?></title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" /> -->

    <!-- <link rel="stylesheet" href="<?php echo get_theme_file_uri() ?>/css/bootstrap.min.css"> -->
    <!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <?php wp_head() ?>
</head>

<body <?php body_class() ?>>
    <div class="outer bg-danger">
        <div class="top-header container-fluid bg-danger">
            <div class="row px-auto text-center">
                <div class="col-lg-5 bx-o">
                    <h6 class="h6">
                        <i class='bx bxs-graduation'></i> Durgalaxmi Model Secondary School, Godawori-2 Attariya, Kailali
                    </h6>
                </div>
                <div class="col-lg-2 bx-o">
                    <h6 class="h6">
                        <i class='bx bxs-phone'></i> +977-092848484
                    </h6>
                </div>
                <div class="col-lg-3 bx-o">
                    <h6 class="h6">
                        <i class='bx bx-envelope'></i> info@durgalaxmimss.gov.np
                    </h6>
                </div>
                <div class="col-lg-1 bx-o">
                    <a class=" social-link" href="">
                        <i class='bx bxl-facebook'></i>
                    </a>
                    <a class="social-link " href="">
                        <i class='bx bxl-twitter'></i>
                    </a>
                </div>
                <div class="col-lg-1 p-0 loginbtn">
                    <a href="<?php echo site_url() ?>/login" class="social-link " style="font-size: 14px; "><i class='bx bxs-lock-alt text-light'></i> Login</a>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-light bg-light  sticky-top p-0">
            <div class="container-fluid mt-0 sticky-top  nav text-capitalize">
                <a class="navbar-brand" href="<?php site_url() ?>"><?php the_custom_logo() ?></a>
                <button class="navbar-toggler border-0  " type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon outline-none"> </span>
                </button>
                <div class="collapse  navbar-collapse " id="navbarNav">
                    <?php
                    wp_nav_menu(array(
                        'theme_location'  => 'primary',
                        'container_class' => ' p-1 p-lg-0 ',
                        'container_id'    => 'primarymenu',
                        'menu_class'      => 'navbar-nav',
                        'sub_menu_class' => "submenu",
                        'a_class' => 'nav-link p-2 text-capitalize',
                        'li_class' => 'nav-item ml-1',
                        'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
                        'walker'          => new bootstrap_5_wp_nav_menu_walker(),

                    ));
                    ?>
                </div>
        </nav>

    </div>