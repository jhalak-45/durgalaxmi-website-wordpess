<div class="row">
    <div class="col-11">
        <h3 class="title">Let's,Browse</h4>
            <h2 class="sub-title">Latest Notices</h2>
    </div>
    <div class="col-1">
        <a href="<?php echo site_url() ?>/category/notice" class="">
            <i class="bx bx-right-arrow-alt rounded-circle bg-primary py-2 px-2" style='color:#ffffff; font-size:30px;'></i> </a>
    </div>
</div>
<div class="courses d-flex justify-content-center py-3" style="flex-wrap:wrap ;">
    <?php
    $wp_query = new WP_Query(array(
        'post_type' => 'post',
        'category_name'=>'notice',
        'posts_per_page' => 3,
    ));
    ?>
    <?php if ($wp_query->have_posts()) : ?>
        <?php while ($wp_query->have_posts()) : $wp_query->the_post();
        ?>
            <div class="card my-2 mx-3 border-0 rounded-2" style="width: 20rem;" data-aos="fade-up">
                <img src="<?php the_post_thumbnail_url() ?>" class="card-img-top" height="190px" width="100%">
                <div class="card-body">
                    <h5 class="card-title text-capitalize"><?php the_title(); ?></h5>
                    <p class="text-center">
                        <a href="<?php the_permalink() ?>" class="text-center " style="text-decoration: none; color:gray;">Readmore.. <i class='bx bx-right-arrow-alt' style='color:#fe1169'></i></a>
                    </p>
                </div>
            </div>

        <?php endwhile; ?>
    <?php endif ?>
</div>