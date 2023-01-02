<?php get_header() ?>
<div class="container-fluid p-0">
    <?php if (function_exists('z_taxonomy_image_url')) {
        $img = z_taxonomy_image_url();
    }
    ?>
    <img src="<?php echo $img ?>" height="auto" width="100%">
    <h1 class="h1 page-title text-capitalize mb-0  py-4   text-center "><?php single_cat_title() ?></h1>


    <div class="courses d-flex mt-0 bg-light px-1 justify-content-start py-3" style="flex-wrap:wrap ;">
        <?php
        $wp_query = new WP_Query(array(
            'post_type' => 'post',
            'category_name' => 'result',
            'posts_per_page' => 12,
        ));
        ?>
        <?php if ($wp_query->have_posts()) : ?>
            <?php while ($wp_query->have_posts()) : $wp_query->the_post();
            ?>
                <div class="card border-0 rounded-1 px-2 my-2 pb-0 mx-1 event " data-aos="fade-up">
                    <div class="row flex-wrap px-1 d-flex justify-content-center">
                        <div class="col-md-3 py-2 px-1 col-lg-3 col-sm-12">
                            <a href="<?php the_permalink() ?>">
                                <?php the_post_thumbnail('thumbnail') ?>
                            </a>
                        </div>
                        
                        <div class="col-md-9 col-sm-12 col-lg-9 px-1 py-2">
                            <div class="card-body">
                                <a href="<?php the_permalink() ?>">
                                    <h5 class="card-title text-capitalize"><?php the_title(); ?></h5>
                                </a>
                                <span>
                                    <p class="text-muted"><i class='bx bx-calendar-event'></i> <?php echo get_the_date() ?></p>
                                </span>
                                <p class="text-left">
                                    <a href="<?php the_permalink() ?>" class="text-center " style="text-decoration: none; color:gray;">Readmore.. <i class='bx bx-right-arrow-alt' style='color:#fe1169'></i></a>
                                </p>
                            </div>
                        </div>
                    </div>


                </div>

            <?php endwhile; ?>
        <?php endif ?>
    </div>
</div>
<?php get_footer() ?>