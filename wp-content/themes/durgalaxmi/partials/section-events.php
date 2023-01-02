<div class="row">
    <div class="col-11">
        <h3 class="title">Lets, Browse</h4>
            <h2 class="sub-title">Latest Events</h2>
    </div>
    <div class="col-1">
        <a href="<?php echo site_url() ?>/category/event" class="">
            <i class="bx bx-right-arrow-alt rounded-circle bg-primary py-2 px-2" style='color:#ffffff; font-size:30px;'></i> </a>
    </div>
</div>
<div class="courses d-flex justify-content-start py-3" style="flex-wrap:wrap ;">
    <?php
    $wp_query = new WP_Query(array(
        'post_type' => 'post',
        'category_name' => 'event',
        'posts_per_page' => 4,
    ));
    ?>
    <?php if ($wp_query->have_posts()) : ?>
        <?php while ($wp_query->have_posts()) : $wp_query->the_post();
        ?>
            <div class="card border-0 rounded-1 bg-light px-2 my-2 pb-0 mx-1 event " data-aos="fade-up">
                <div class="row flex-wrap px-1 bg-transparent  d-flex justify-content-center">
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
                            <span><p class="text-muted"><i class='bx bx-calendar-event'></i> <?php echo get_the_date()?></p></span>
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