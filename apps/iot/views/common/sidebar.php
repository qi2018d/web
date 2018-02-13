<!-- upper bar for small screen -->
<!--div class="responsive-header visible-xs visible-sm">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="top-section">
                    <div class="profile-image">
                        <img src="img/profile.jpg" alt="profile image">
                    </div>
                    <div class="profile-content">
                        <h3 class="profile-title">Volton</h3>
                        <p class="profile-description">Digital Photographer</p>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" class="toggle-menu"><i class="fa fa-bars"></i></a>
        <div class="main-navigation responsive-menu">
            <ul class="navigation">
                <li><a href="#top"><i class="fa fa-home"></i>Home</a></li>
                <li><a href="/signin"><i class="fa fa-user"></i>Sign in</a></li>
                <li><a href="#projects"><i class="fa fa-newspaper-o"></i>My Gallery</a></li>
                <li><a href="#contact"><i class="fa fa-envelope"></i>Contact Me</a></li>
            </ul>
        </div>
    </div>
</div-->

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar-menu hidden-xs hidden-sm">
    <div class="top-section">
        <div class="profile-image">
            <!--img src="img/cloud-100.png" alt="profile image"-->
        </div>
        <h3 class="profile-title"></h3>
        <p class="profile-description"></p>
    </div> <!-- top-section -->
    <div class="main-navigation">
        <ul class="navigation">
            <li class="sidebar-li"><a href="/" id="home-menu"><i class="fas fa-angle-double-right"></i>Welcome</a></li>
            <li class="sidebar-li"><a href="/map" id="map-menu"><i class="fas fa-globe"></i>Air Map</a></li>

            <?php if(isset($_SESSION["user_id"])) :?>
                <li class="sidebar-li"><a href="/user" id="user-menu"><i class="fas fa-user"></i>User</a></li>
                <li class="sidebar-li"><a href="/sensor" id="sensor-menu"><i class="fab fa-bluetooth-b"></i>&nbsp;&nbsp;Sensor</a></li>
                <li class="sidebar-li"><a href="/charts" id="sensor-menu"><i class="fas fa-chart-line"></i>Charts</a></li>
                <li class="sidebar-li"><a style="cursor: pointer;" id="signout-menu"><i class="fas fa-sign-out-alt"></i>Sign out</a></li>
            <?php else: ?>
                <li class="sidebar-li"><a href="/signin" id="signin-menu"><i class="fas fa-sign-in-alt"></i>Sign in</a></li>
            <?php endif ?>
            <li class="sidebar-li"><a href="/developers" id="developer-menu"><i class="fas fa-link"></i>Contact us</a></li>
        </ul>
    </div> <!-- .main-navigation -->
    <script>
        $("#signout-menu").click(function(){

            $.ajax({
                type: "GET",
                dataType: "json",
                url: "/api/user/signout",
                contentType: "application/json",
                success: function(result){
                    // if sign-in success
                    if(result.status === true){
                        window.location.href = '/';
                    }
                }
            });
        });
    </script>

    <!--div class="social-icons">
        <ul>
            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
            <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
            <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
            <li><a href="#"><i class="fa fa-youtube"></i></a></li>
            <li><a href="#"><i class="fa fa-rss"></i></a></li>
        </ul>
    </div> <!-- .social-icons -->

</div> <!-- .sidebar-menu -->
<script src="/js/sidebar.js"></script>

