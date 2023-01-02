<?php get_header() ?>
<div class="outer-front-top">
    <div class="container-fluid  front-page px-0">
        <section class="carousel p-0">
            <?php get_template_part('partials/section', 'carousel'); ?>
        </section>
        <section class="school-intro container-fluid">
            <div class="row p-3 bg-transparent">
                <?php
                $args = array(
                    'post_type' => 'about',
                    'title' => 'introduction',
                );

                $your_query = new WP_Query($args);
                while ($your_query->have_posts()) : $your_query->the_post();
                ?>
                    <div class="col-md-6 school-image" data-aos="fade-down">
                        <img src="<?php the_field('school_image'); ?>" height="auto" width="100%">
                    </div>
                    <div class="col-md-6 school-introduction" data-aos="fade-up">
                        <h3 class="h3 title">About Us</h3>
                        <h2 class="sub-title">Introduction</h2>
                        <div class="content">
                            <?php
                            //assign content of a custom field
                            $content = get_the_content();

                            echo wp_trim_words($content, 90, '.<br><a class="btn-readmore" href="' . get_permalink() . '">Read More</a>');
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
        <section class="section-courses container-fluid">
            <?php get_template_part('partials/section', 'courses'); ?>
        </section>
        <section class="section-events container-fluid " style="background-color:#f2f2f2;">
            <?php get_template_part('partials/section', 'events'); ?>

        </section>
        <section class="section-events container-fluid " style="background-color:#f2f3f4;">
            <?php get_template_part('partials/section', 'news'); ?>

        </section>
    </div>

</div>

<?php get_footer() ?>