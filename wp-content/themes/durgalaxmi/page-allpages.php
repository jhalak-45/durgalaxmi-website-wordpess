<?php get_header() ?>
<div class="outer">
    <div class="container-fluid page p-0">
        <img src="<?php the_post_thumbnail_url() ?>" height="auto" width="100%">
        <h1 class="h1 page-title text-capitalize  py-4   text-center "><?php the_title() ?></h1>

        <div class="page-content">
            <?php the_content() ?>
        </div>
    </div>
</div>
<?php get_footer() ?>