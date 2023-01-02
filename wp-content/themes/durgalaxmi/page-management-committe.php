<?php get_header() ?>
<div class="outer">
    <div class="container-fluid p-0">
       
        <div class="courses py-5 bg-light px-2 d-flex justify-content-start" style="flex-wrap: wrap;">
            <?php
            $wp_query = new WP_Query(array(
                'post_type' => 'management_committe',
                'post_per_page' => 30,
            ));
            ?>
            <?php if ($wp_query->have_posts()) : ?>
                <?php while ($wp_query->have_posts()) : $wp_query->the_post();
                ?>
                    <!-- <button type="button"> -->
                    <div class="card m-1 border-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="width: 18rem;" data-aos="fade-up">
                        <h5 class="member-post bg-danger py-2  mb-0 text-light text-center"><?php the_field('member_post') ?></h5>
                        <img src="<?php the_field('member_image') ?>" class="img-fluid mt-0" height="190px" width="100%">
                        <div class="card-body mt-0 p-0">
                            <h5 class="card-title text-capitalize bg-primary text-light py-3  text-center"><?php the_title(); ?></h5>

                        </div>
                    </div>
                    <!-- </button> -->

                    <!-- Modal -->
                    <div class="modal fade mt-5" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-capitalize" id="staticBackdropLabel"><?php the_title() ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>


                                <div class="card  modal-body">
                                    <div class="row">
                                        <div class="image col-md-6">
                                            <img src="<?php the_field('member_image') ?>" class="img-fluid mt-0" height="190px" width="100%">

                                        </div>
                                        <div class="col-md-6">
                                            <p class="card-text">
                                                <?php the_field('member_description') ?>
                                            </p>

                                        </div>
                                    </div>


                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif ?>
        </div>


    </div>
</div>
<?php get_footer() ?>