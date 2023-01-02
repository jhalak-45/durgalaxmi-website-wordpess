<?php get_header() ?>
<div class="outer">
    <div class="container-fluid page p-0">
        <div class="single-page p-0">
            <?php if (in_category('news')|| in_category('downloads')||in_category('events')) {
                get_template_part('page-notice');
            } 
            ?>
        </div>
    </div>
</div>
<?php get_footer() ?>