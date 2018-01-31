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
            <!--img src="img/profile.jpg" alt="profile image"-->
        </div>
        <h3 class="profile-title"></h3>
        <p class="profile-description"></p>
    </div> <!-- top-section -->
    <div class="main-navigation">
        <ul class="navigation">
            <li><a href="/"><i class="fa fa-smile-o"></i>Welcome</a></li>
            <li><a href="/map"><i class="fa fa-globe"></i>Air Map</a></li>

            <?php if(isset($_SESSION["user_id"])) :?>
                <li><a href="/sensor"><i class="fa fa-bluetooth"></i>Sensor</a></li>
                <li><a href="/signin"><i class="fa fa-pencil"></i>Sign out</a></li>
            <?php else: ?>
                <li><a href="/signin"><i class="fa fa-pencil"></i>Sign in</a></li>
            <?php endif; ?>
            <li><a href="/developers"><i class="fa fa-link"></i>Contact us</a></li>
        </ul>
    </div> <!-- .main-navigation -->

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

