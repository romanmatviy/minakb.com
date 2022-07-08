<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <title>Реєстрація <?=SITE_NAME?></title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <link rel="shortcut icon" href="<?=SERVER_URL?>favicon.ico">

    <!-- ================== BEGIN BASE CSS STYLE ================== -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="<?=SERVER_URL?>assets/jquery-ui/themes/base/minified/jquery-ui.min.css" rel="stylesheet" />
    <link href="<?=SERVER_URL?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?=SERVER_URL?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
    <link href="<?=SERVER_URL?>style/admin/animate.min.css" rel="stylesheet" />
    <link href="<?=SERVER_URL?>style/admin/style.min.css" rel="stylesheet" />
    <link href="<?=SERVER_URL?>style/admin/style-responsive.min.css" rel="stylesheet" />
    <link href="<?=SERVER_URL?>style/admin/theme/default.css" rel="stylesheet" id="theme" />
    <!-- ================== END BASE CSS STYLE ================== -->

    <!-- ================== BEGIN BASE JS ================== -->
    <script src="<?=SERVER_URL?>assets/pace/pace.min.js"></script>
    <!-- ================== END BASE JS ================== -->
</head>
<body class="pace-top">
    <!-- begin #page-loader -->
    <div id="page-loader" class="fade in"><span class="spinner"></span></div>
    <!-- end #page-loader -->

    <script>
        window.fbAsyncInit = function() {
            <?php $this->load->library('facebook'); ?>
            FB.init({
              appId      : '<?=$this->facebook->getAppId()?>',
              cookie     : true,
              xfbml      : true,
              version    : 'v3.1'
            });
        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function facebookSignUp() {
            FB.login(function(response) {
                if (response.authResponse) {
                    $("#divLoading").addClass('show');
                    var accessToken = response.authResponse.accessToken;
                    FB.api('/me?fields=email', function(response) {
                        if (response.email && accessToken) {
                            $('#authAlert').addClass('collapse');
                            $.ajax({
                                url: '<?=SITE_URL?>signup/facebook',
                                type: 'POST',
                                data: {
                                    accessToken: accessToken,
                                    ajax: true
                                },
                                complete: function() {
                                    $("div#divLoading").removeClass('show');
                                },
                                success: function(res) {
                                    if (res['result'] == true) {
                                        window.location.href = '<?=SITE_URL?>profile';
                                    } else {
                                        $('#authAlert').removeClass('collapse');
                                        $("#authAlertText").text(res['message']);
                                    }
                                }
                            })
                        } else {
                            $("div#divLoading").removeClass('show');
                            $("#clientError").text('Для авторизації потрібен e-mail');
                            setTimeout(function(){$("#clientError").text('')}, 5000);
                            FB.api("/me/permissions", "DELETE");
                        }
                    });
                } else {
                    $("div#divLoading").removeClass('show');
                }

            }, { scope: 'email' });
        }
    </script>

    <div class="login-cover">
        <div class="login-cover-image"><img src="<?=SITE_URL?>style/admin/login-bg/bg-1.jpg" data-id="login-cover-image" alt="" /></div>
        <div class="login-cover-bg"></div>
    </div>
    <!-- begin #page-container -->
    <div id="page-container" class="fade">
        <!-- begin login -->
        <div class="login login-v2" data-pageload-addclass="animated fadeIn">
            <!-- begin brand -->
            <div class="login-header">
                <div class="brand">
                    <span class="logo"></span> <?=SITE_NAME?>
                </div>
                <div class="icon">
                    <i class="fa fa-sign-in"></i>
                </div>
            </div>
            <!-- end brand -->
            <div class="login-content">
                <h2 style="color:#fff; text-align: center"><?=$this->text('Реєстрація')?></h2>
                <div class="m-t-20 text-center">
                    <button type="button" onclick="facebookSignUp()" class="btn btn-success btn-block btn-lg m-b-20"><i class="fa fa-facebook"></i> <?=$this->text('Швидка реєстрація')?> facebook</button>
                    <big><?=$this->text('АБО')?></big>
                    <a href="<?=SITE_URL?>signup/email" class='btn btn-warning btn-block btn-lg m-t-20'><i class="fa fa-envelope"></i> <?=$this->text('Реєстрація через email')?></a>
                </div>
                <div class="m-t-20">
                    <?=$this->text('Вже зареєстровані')?>? <a href="<?=SITE_URL?>login"><?=$this->text('Увійти')?></a>. <br>
                    <?=$this->text('Повернутися на')?> <a href="<?=SITE_URL?>"><?=$this->text('головну сторінку')?></a>.
                </div>
            </div>
        </div>
        <!-- end login -->

        <ul class="login-bg-list">
            <li class="active"><a href="#" data-click="change-bg"><img src="<?=SITE_URL?>style/admin/login-bg/bg-1.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SITE_URL?>style/admin/login-bg/bg-2.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SITE_URL?>style/admin/login-bg/bg-3.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SITE_URL?>style/admin/login-bg/bg-4.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SITE_URL?>style/admin/login-bg/bg-6.jpg" alt="" /></a></li>
        </ul>
    </div>
    <!-- end page container -->

    <!-- ================== BEGIN BASE JS ================== -->
    <script src="<?=SERVER_URL?>assets/jquery/jquery-1.9.1.min.js"></script>
    <script src="<?=SERVER_URL?>assets/jquery/jquery-migrate-1.1.0.min.js"></script>
    <script src="<?=SERVER_URL?>assets/jquery-ui/ui/minified/jquery-ui.min.js"></script>
    <script src="<?=SERVER_URL?>assets/bootstrap/js/bootstrap.min.js"></script>
    <!--[if lt IE 9]>
        <script src="<?=SERVER_URL?>assets/crossbrowserjs/html5shiv.js"></script>
        <script src="<?=SERVER_URL?>assets/crossbrowserjs/respond.min.js"></script>
        <script src="<?=SERVER_URL?>assets/crossbrowserjs/excanvas.min.js"></script>
    <![endif]-->
    <script src="<?=SERVER_URL?>assets/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="<?=SERVER_URL?>assets/jquery-cookie/jquery.cookie.js"></script>
    <!-- ================== END BASE JS ================== -->

    <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="<?=SERVER_URL?>assets/color-admin/login-v2.min.js"></script>
    <script src="<?=SERVER_URL?>assets/color-admin/apps.min.js"></script>
    <!-- ================== END PAGE LEVEL JS ================== -->

    <script>
        $(document).ready(function() {
            App.init();
            LoginV2.init();
        });
    </script>
</body>
</html>