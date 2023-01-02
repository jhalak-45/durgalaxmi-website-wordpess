<?php get_header() ?>
<div class="outer">
    <div class="container-fluid page p-0">
        <img src="<?php the_post_thumbnail_url() ?>" height="auto" width="100%">
        <h1 class="h1 page-title text-capitalize  py-4   text-center "><?php the_title() ?></h1>

        <div class="page-content">

            <?php
            if (isset($_POST['submit'])) {
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $email = $_POST['email'];
                $phn = $_POST['phn'];
                //    $province =$_POST['province'];
                //    $dist =$_POST['dist'];
                $dob = $_POST['dob'];
                //    $ctn =$_POST['cn'];
                //    $gen =$_POST['gender'];
                $msg = $_POST['msg'];
                // $img =$_POST['img'];
                // $img = wp_upload_bits($_FILES["img"]["name"], null, file_get_contents($_FILES["img"]["tmp_name"]));




                global $wpdb;


                $sql = $wpdb->prepare(
                    " INSERT INTO `registration` (`id`, `fname`, `lname`, `address`, `number`,  `dob`, `msg`)VALUES (NULL, '$fname', '$lname', '$email','$phn', '$dob', '$msg ')"
                );

                $wpdb->query($sql);

                if ($sql) {
                    echo '<div class="alert align-center px-auto col-md-8 alert-primary alert-dismissible fade show" role="alert">Registration Success</div>';
                    // wp_redirect(site_url());
                    // exit;
                }
            }
            ?>
            <div class="container col-md-9">
                <form method="POST" enctype="multipart/form-data" class="form-control form shadow p-0">
                    <div class="navbar-brand col-md-12 text-center "><?php the_custom_logo() ?>
                        <h4 class="h4 text-center m-0"><?php bloginfo() ?></h4>
                        <h6 class="h6 text-center mt-0">Godawori -02 Attariya ,Kailali</h6>
                        <h6 class="h6 text-center">Phn. 094-420002, 509992 | info@durgalaxmimss.edu.np</h6>
                        <h5 class="h5  text-center text-capitalize  my-4  "> <b class="form-title py-2 rounded-1 px-3">Admission Form</b> </h5>

                    </div>
                    <div class="col-md-12">
                        <div class=" d-flex justify-content-start">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="">Select your Image:</label>
                                    <div class=" form-group justify-content-end px-auto">
                                        <input type="file"  name="img" class="form-control rounded-1 shadow-none" id="img">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row py-1 d-flex justify-content-center">
                            <div class=" col-md-6 form-group justify-content-center">
                                <label for="fname">First Name:</label>
                                <input type="text" class="form-control shadow-none rounded-1" id="fname" name="fname" placeholder="Enter your first name" required>
                            </div>
                            <div class="col-md-6  form-group justify-content-center">
                                <label for="lname">Last Name:</label>
                                <input type="text" class="form-control shadow-none rounded-1" id="fname" name="lname" placeholder="Enter your last name" required>
                            </div>
                        </div>


                        <div class="row py-1 d-flex justify-content-center">
                            <div class=" col-md-6 form-group justify-content-center">
                                <label for="email">Email Address:</label>
                                <input type="email" class="form-control shadow-none rounded-1" name="email" id="email" placeholder="Enter your valid email address" required>
                            </div>
                            <div class="col-md-6 form-group justify-content-center">
                                <label for="con">Contact Number:</label>
                                <input type="number" class="form-control shadow-none rounded-1" name="phn" id="con" placeholder="Enter your contact number" required>
                            </div>
                        </div>
                        <!-- <div class="row py-1">
                        <label for="district">Full Address:</label><br>

                  </div> -->

                        <div class="row py-1 d-flex justify-content-center">
                            <div class=" col-md-3 form-group justify-content-center">
                                <label for="dob">Birth Date:</label>
                                <input type="date" class="form-control shadow-none rounded-1" name="dob" id="dob" required>
                            </div>
                            <div class="col-md-9 form-group justify-content-center">
                                <label for="address">Full Address:</label>
                                <input type="text" class="form-control shadow-none rounded-1" name="address" id="address" placeholder="Enter your address" required>
                            </div>
                        </div>
                        <div class="row d-flex py-1 justify-content-center">
                            <div class=" col-md-12 form-group  ">
                                <label for="text">Leave your message:</label>
                                <textarea class="form-control shadow-none rounded-1 " name="msg" id="text" style="resize: none; height:170px;"></textarea>
                            </div>

                        </div>


                        <div class="row py-2">
                            <div class=" col-md-6 form-group  d-flex justify-content-center">
                                <!-- <input type="reset" class="form-control btn btn-danger  rounded-1 shadow-none" id="clear" Value="Clear"> -->
                            </div>
                            <div class="col-md-6 form-group  sm-mt-2  d-flex justify-content-center">
                                <input type="submit" value="Register" name="submit" class="form-control rounded-1 submit shadow-none btn btn-primary  font-weight-bold" id="reg">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php get_footer() ?>