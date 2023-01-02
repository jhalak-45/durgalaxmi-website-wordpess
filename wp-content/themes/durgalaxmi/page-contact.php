<?php get_header() ?>
<div class="outer">
    <div class="container-fluid page p-0">
        <img src="<?php the_post_thumbnail_url() ?>" height="auto" width="100%">
        <h1 class="h1 page-title text-capitalize  py-4   text-center "><?php the_title() ?> Us</h1>

        <div class="page-content">
            <div class="container col-md-9">

                <?php
                if (isset($_POST['send'])) {
                    $fname = $_POST['fname'];
                    $lname = $_POST['lname'];
                    $email = $_POST['email'];
                    $phn = $_POST['phn'];
                    $address = $_POST['address'];

                    $sub = $_POST['sub'];
                    $msg = $_POST['msg'];

                    global $wpdb;

                    $sql = $wpdb->prepare(
                        "INSERT INTO `contact` (`sn`, `fname`, `lname`, `email`, `address`, `phone`, `subect`, `message`, `postdate`) VALUES (NULL, '$fname', '$lname', '$email', '$address', '$phn', '$sub', '$msg', current_timestamp());"
                    );
                    $wpdb->query($sql);
                    // if ($sql) {
                    //     echo '<div class="alert  align-center d-flex justify-content-center  text-center px-auto col-md-12 alert-success alert-dismissible fade show py-1 rounded-0" role="alert"> <i class="bx bxs-check-circle mr-2 py-1" style="font-size:20px;"></i> Contact success! , we will reply with in few minutes.</div>';
                    // } else {
                    //     echo '<div class="alert d-flex justify-content-center align-center text-center px-auto col-md-12 alert-danger alert-dismissible fade show" role="alert"><i class="bx bxs-calendar-exclamation"></i> Contact failed !, Please try again!!.</div>';
                    // }
                    if(!is_wp_error($sql)){
                        echo '<div class="alert  align-center d-flex justify-content-center  text-center px-auto col-md-12 alert-success alert-dismissible fade show py-1 rounded-0" role="alert"> <i class="bx bxs-check-circle mr-2 py-1" style="font-size:20px;"></i> Contact success! , we will reply with in few minutes.</div>';
                        // wp_redirect(site_url().'/contact') ;
                        
                    }
                    else{
                        return $sql->get_error_message();
                    }
                } ?>
                <form method="POST" enctype="multipart/form-data" onsubmit="return false;" class="form-control form py-4 px-1 shadow p-0">
                    <div class="col-md-12">
                        <div class="row py-1 d-flex justify-content-center">
                            <div class=" col-md-6 form-group  justify-content-center">
                                <label for="fname">First Name <i class="text-danger"> * </i>:</label>
                                <input type="text" class="form-control shadow-none rounded-1" id="fname" name="fname" placeholder="Enter your first name" required>
                            </div>
                            <div class="col-md-6  form-group justify-content-center">
                                <label for="lname">Last Name <i class="text-danger"> * </i>:</label>
                                <input type="text" class="form-control shadow-none rounded-1" id="fname" name="lname" placeholder="Enter your last name" required>
                            </div>
                        </div>


                        <div class="row py-1 d-flex justify-content-center">
                            <div class=" col-md-6 form-group">
                                <label for="email">Email Address <i class="text-danger"> * </i>:</label>
                                <input type="email" class="form-control shadow-none rounded-1" name="email" id="email" placeholder="Enter your  email address" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="con">Contact Number <i class="text-danger"> * </i>:</label>
                                <input type="number" class="form-control shadow-none rounded-1" name="phn" id="con" placeholder="Enter your contact number" required>
                            </div>
                        </div>
                        <div class="row py-1 d-flex justify-content-center">
                            <div class="col-md-6 form-group ">
                                <label for="address">Full Address <i class="text-danger"> * </i>:</label>
                                <input type="text" class="form-control shadow-none rounded-1" name="address" id="address" placeholder="Enter your address" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="sub">Subject <i class="text-danger"> * </i> :</label>
                                <input type="text" class="form-control shadow-none rounded-1" name="sub" id="sub" placeholder="Enter your subject" required>
                            </div>
                        </div>
                        <div class="row d-flex py-1 justify-content-center">
                            <div class=" col-md-12 form-group  ">
                                <label for="text">Leave your message:</label>
                                <textarea class="form-control shadow-none rounded-1 " placeholder="Enter your Message" name="msg" id="text" style="resize: none; height:170px;"></textarea>
                            </div>

                        </div>


                        <div class="row py-2 d-flex justify-content-end">
                            <div class=" col-md-8 mr-2  ">
                            </div>
                            <div class="col-md-4 form-group  mr-2 sm-mt-2  d-flex justify-content-center">
                                <input type="submit" value="Send" name="send" class="form-control rounded-1 submit shadow-none btn btn-primary  font-weight-bold" id="send">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php get_footer() ?>