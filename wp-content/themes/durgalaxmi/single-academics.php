<?php get_header() ?>
<div class="outer">
    <div class="container-fluid page p-0">
        <img src="<?php the_post_thumbnail_url() ?>" height="auto" width="100%">
        <!-- <h1 class="h1 page-title text-capitalize  py-4   text-center "><?php the_title() ?></h1> -->

        <div class="page-content row">
            <div class="col-md-9 col-sm-6 col-xs-7 content">
                <h3 class="single-page-title py-3 text-capitalize"><?php the_title() ?></h3>

                <?php the_content() ?>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-5 single-page-sidebar">
                <?php $wp_query = new WP_Query(array(
                    'post_type' => 'academics',
                    'post_per_page' => 20,

                )); ?>
                <div class="lists">
                    <h2 class="single-post-title">
                        Academics
                    </h2>
                    <?php if ($wp_query->have_posts()) : ?>
                        <?php while ($wp_query->have_posts()) : $wp_query->the_post() ?>
                            <h5 class="post-lists active">
                                <a href="<?php the_permalink() ?>">
                                    <?php the_title() ?>
                                </a>
                            </h5>

                        <?php endwhile ?>
                    <?php endif ?>
                </div>
            </div>
        </div>

    </div>
</div>

</div>

</div>
</div>
<?php get_footer() ?>