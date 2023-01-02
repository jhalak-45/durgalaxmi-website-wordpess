<div id="carouselExampleCaptions" class="carousel  slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button  type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button  type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <?php
    $the_query = new WP_Query(
        array(
            'post_type' => 'post',
            // 'post_category' => 'notice',
            'post_per_page' => 3,
            'post_page'=>$paged,
        )
    );

    ?>
    <div class="carousel-inner p-0 d-flex">
        <!--  data-bs-interval="1000" for slider -->
        <?php if ($the_query->have_posts()) : ?>
            <?php while ($the_query->have_posts()) :  $the_query->the_post(); ?>
                <div class="carousel-item active" data-bs-interval="3000">
                    <img src="<?php echo  the_post_thumbnail_url() ?>" class="d-block w-100" height="600px" width="100%">
                    <div class="carousel-caption d-none d-md-block">
                        <h3 class="text-light h3"><?php the_title() ?></h3>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</div>


<button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
</button>
</div>