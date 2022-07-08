<!DOCTYPE html>
<!--[if IE 8]> <html lang="<?=$_SESSION['language']?>" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="<?=$_SESSION['language']?>">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Реєстрація | <?=SITE_NAME?></title>
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

    <?php if($_SESSION['option']->facebook_initialise) { ?>
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
    <?php } ?>
</head>
<body class="pace-top bg-white">
    <div id="divLoading"></div>

	<!-- begin #page-loader -->
	<div id="page-loader" class="fade in"><span class="spinner"></span></div>
	<!-- end #page-loader -->

	<!-- begin #page-container -->
	<div id="page-container" class="fade">
	    <!-- begin register -->
        <div class="register register-with-news-feed">
            <!-- begin news-feed -->
            <div class="news-feed">
                <div class="news-image">
                    <img src="<?=SITE_URL?>style/admin/login-bg/bg-9.jpg" alt="" />
                </div>
                <div class="news-caption">
                    <h4 class="caption-title"><i class="fa fa-edit text-success"></i> <?=$this->text('Реєстрація')?> <?=SITE_NAME?></h4>
                    <p><?=$this->text('Створіть Ваш особистий кабінет')?>.</p>
                </div>
            </div>
            <!-- end news-feed -->
            <!-- begin right-content -->
            <div class="right-content">
                <!-- begin register-header -->
                <h1 class="register-header">
                    <?=$this->text('Реєстрація')?>
                    <small><?=$this->text('Створіть Ваш особистий кабінет')?>.</small>
                </h1>
                <!-- end register-header -->

                <!-- begin register-content -->
                <div class="register-content">

                    <?php if(!empty($_SESSION['notify']->errors)): ?>
                       <div class="alert alert-danger fade in">
                            <span class="close" data-dismiss="alert">×</span>
                            <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Помилка!'?></h4>
                            <p><?=$_SESSION['notify']->errors?></p>
                        </div>
                    <?php elseif(!empty($_SESSION['notify']->success)): ?>
                        <div class="alert alert-success fade in">
                            <span class="close" data-dismiss="alert">×</span>
                            <i class="fa fa-check fa-2x pull-left"></i>
                            <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Успіх!'?></h4>
                            <p><?=$_SESSION['notify']->success?></p>
                        </div>
                    <?php endif; unset($_SESSION['notify']); ?>

                    <form action="<?=SITE_URL?>signup/process" method="POST" class="margin-bottom-0" onsubmit="$('#divLoading').addClass('show');">
                        <label class="control-label"><?=$this->text('Ім\'я')?></label>
                        <div class="row row-space-10">
                            <div class="col-md-6 m-b-15">
                                <input name="first_name" type="text" value="<?=$this->data->re_post('first_name')?>" class="form-control" placeholder="<?=$this->text('Ім\'я')?>" required />
                            </div>
                            <div class="col-md-6 m-b-15">
                                <input name="last_name" type="text" value="<?=$this->data->re_post('last_name')?>" class="form-control" placeholder="<?=$this->text('Прізвище')?>" required />
                            </div>
                        </div>
                        <label class="control-label">Email</label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
                                <input name="email" type="email" value="<?=$this->data->re_post('email')?>" class="form-control" placeholder="Email address" required />
                            </div>
                        </div>
                        <label class="control-label"><?=$this->text('Контактний номер')?></label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
                                <input name="phone" type="text" value="<?=$this->data->re_post('phone')?>" class="form-control" placeholder="+380*********" required minlength="19"/>
                            </div>
                        </div>
                        <label class="control-label">Пароль</label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
                                <input name="password" type="password" value="<?=$this->data->re_post('password')?>" class="form-control" placeholder="Password" required />
                                <small><?=$this->text('Ваш унікальний пароль, який використовується для входу на сайт. Може містити літери від а..я, a..z та числа. Довжина пороля від 5 до 20 символів')?>.</small>
                            </div>
                        </div>
                        <label class="control-label"><?=$this->text('Повторити пароль')?></label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
                                <input name="re-password" type="password" class="form-control" placeholder="Password" required />
                            </div>
                        </div>
                        <?php /*
                        <div class="checkbox m-b-30">
                            <label>
                                <input type="checkbox" /> By clicking Sign Up, you agree to our <a href="#">Terms</a> and that you have read our <a href="#">Data Policy</a>, including our <a href="#">Cookie Use</a>.
                            </label>
                        </div> */ ?>
                        <div class="register-buttons">
                            <button type="submit" class="btn btn-primary btn-block btn-lg"><?=$this->text('Зареєструватися')?></button>
                        </div>
                        <?php if($_SESSION['option']->facebook_initialise) { ?>
                            <div class="m-t-20 text-center">
                                <big><?=$this->text('АБО')?></big>
                                <div class="login-buttons m-t-10">
                                    <button type="button" onclick="facebookSignUp()" class="btn btn-success btn-block btn-lg"><i class="fa fa-facebook"></i> <?=$this->text('Швидка реєстрація')?> facebook</button>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="m-t-20 m-b-40 p-b-40">
                            <?=$this->text('Вже зареєстровані')?>? <a href="<?=SITE_URL?>login"><?=$this->text('Увійти')?></a>. <?=$this->text('Перейти на')?> <a href="<?=SITE_URL?>"><?=$this->text('головну сторінку')?></a>.
                        </div>
                        <hr />
                        <p class="text-center text-inverse">
                            &copy; White Lion CMS All Right Reserved <?=date('Y')?>
                        </p>
                        <p class="text-center text-inverse">
                            &copy; Color Admin All Right Reserved <?=date('Y')?>
                        </p>
                    </form>
                </div>
                <!-- end register-content -->
            </div>
            <!-- end right-content -->
        </div>
        <!-- end register -->

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
    <script type="text/javascript" src="<?=SERVER_URL?>assets/jquery.mask.min.js"></script>
	<!-- ================== END BASE JS ================== -->

	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="<?=SERVER_URL?>assets/color-admin/apps.min.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->

	<script>
		$(document).ready(function() {
			App.init();
            $('input[name=phone]').mask('+38 (000) 000-00-00');
		});
	</script>
</body>
</html>