<?php get_header() ?>
<div class="outer">
    <div class="container-fluid page p-0">
        <?php if(is_page('login')|| is_page('register') || is_page('members')|| is_page('profile')|| is_page('user'))
        {
             get_template_part('page-users');
        } 
        
        else{
            get_template_part('page-allpages');

        }
        ?>
    </div>
</div>
<?php get_footer() ?>