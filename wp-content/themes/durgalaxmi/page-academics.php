<?php get_header() ?>
<div class="outer">
    <div class="container-fluid p-0">
        <div class="courses py-5 px-2  d-flex justify-content-center" style="flex-wrap: wrap; background-color:ghostwhite">
            <?php
            $wp_query = new WP_Query(array(
                'post_type' => 'academics',
                'post_per_page' => 10,
            ));
            ?>
            <?php if ($wp_query->have_posts()) : ?>
                <?php while ($wp_query->have_posts()) : $wp_query->the_post();
                ?>
                    <div class="card  my-2 mx-3 border-0 rounded-2 " data-aos="fade-up" style="width: 20rem;">
                        <img src="<?php the_post_thumbnail_url() ?>" class="card-img-top" height="190px" width="100%">
                        <div class="card-body">
                            <h5 class="card-title text-capitalize"><?php the_title(); ?></h5>
                            <p class="card-text" style="color:gray;">
                                <?php the_excerpt() ?>
                            </p>
                            <p class="text-center">
                                <a href="<?php the_permalink() ?>" class="text-center " style="text-decoration: none; color:darkgray;">Readmore.. <i class='bx bx-right-arrow-alt' style='color:#fe1169'></i></a>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif ?>
        </div>
    </div>
</div>
<?php get_footer() ?>