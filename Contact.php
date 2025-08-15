<?php include("component/header.php") ?>
      <!-- Start Content section-->
<section data-image="../img/bg1.jpg" id="top" class="display-page img-parallax bg-mask background-image">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="content">
                    <!-- heading title-->
                    <h1>Contact</h1>
                    <!-- horizontal line--><span class="horizontal-line"></span><span></span>
                    <!-- description slider-->
                    <div class="description">
                        <p>We like to create a good and high-quality products. <br>We love clients who appreciate the value of our work, and who are willing to invest in themselves.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Section our location-->
<!--Section we are in numbers-->
<section class="contact-form">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-push-2">
                <div class="heading-title small-heading center">
                    <h2>Contact <span>with us</span></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <form class="contact-form-white" method="post" action="#">
                <div class="text-danger validation-summary-valid" data-valmsg-summary="true"><ul><li style="display:none"></li>
</ul></div>
                <div class="col-md-6">
                    <input type="text" placeholder="Name *" data-val="true" data-val-required="The Name field is required." id="Name" name="Name" value="">
                    <input type="text" placeholder="Email *" data-val="true" data-val-required="The Email field is required." id="Email" name="Email" value="">
                    <input type="text" placeholder="Phone" id="Phone" name="Phone" value="">
                    <input type="text" placeholder="Subject *" data-val="true" data-val-required="The Subject field is required." id="Subject" name="Subject" value="">
                </div>
                <div class="col-md-6">
                    <textarea placeholder="Message *" cols="3" rows="5" data-val="true" data-val-required="The Message field is required." id="Message" name="Message">
</textarea>
                    <p class="success-msg hidden notify">Your message has been send</p>
                    <p class="error-msg hidden notify">Error sending message</p>

                </div>
                <input type="submit" value="Send message" class="btn">
            <input name="__RequestVerificationToken" type="hidden" value="CfDJ8I8DDa5JhGZOqiGXG7WfnKm5NYVdbMfQJ4oLI1whkbxJWcemh8bgUNo5eCMN7amV4owKCJCfurmpOIdMtJxCAoJqubtpqmuFXOMfZd1GA5AwF2luKRFsJiKv6gANT9vJiDRrSumwA-e7xFvvAZ11qCI"></form>
        </div>
    </div>
</section>


  <?php include("component/footer.php") ?>