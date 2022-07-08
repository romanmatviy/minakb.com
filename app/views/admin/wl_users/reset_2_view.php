<!DOCTYPE html>
<!--[if IE 8]> <html lang="uk" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="uk">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Відновлення паролю | <?=SITE_NAME?></title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,700,300,600,400&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<link href="<?=SITE_URL?>assets/jquery-ui/themes/base/minified/jquery-ui.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>style/font-awesome-4.0.3/font-awesome.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>style/admin/animate.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>style/admin/style.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>style/admin/style-responsive.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>style/admin/theme/default.css" rel="stylesheet" id="theme" />
	<!-- ================== END BASE CSS STYLE ================== -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="<?=SITE_URL?>assets/pace/pace.min.js"></script>
	<!-- ================== END BASE JS ================== -->
</head>
<body class="pace-top bg-white">
	<!-- begin #page-loader -->
	<div id="page-loader" class="fade in"><span class="spinner"></span></div>
	<!-- end #page-loader -->
	
	<!-- begin #page-container -->
	<div id="page-container" class="fade">
	    <!-- begin login -->
        <div class="login login-with-news-feed">
            <!-- begin news-feed -->
            <div class="news-feed">
                <div class="news-image">
                    <img src="<?=SITE_URL?>style/admin/login-bg/bg-6.jpg" data-id="login-cover-image" alt="" />
                </div>
                <div class="news-caption">
                    <h4 class="caption-title"><i class="fa fa-diamond text-success"></i> <?=SITE_NAME?></h4>
                    <p> <a href="<?=SITE_URL?>"> <?=SITE_URL?> </a> </p>
                </div>
            </div>
            <!-- end news-feed -->
            <!-- begin right-content -->
            <div class="right-content">
                <!-- begin login-header -->
                <div class="login-header">
                    <div class="brand">
                        <span class="logo"></span> Відновлення паролю
                        <small> Панель керування сайтом </small>
                    </div>
                    <div class="icon">
                        <i class="fa fa-sign-in"></i>
                    </div>
                </div>
                <!-- end login-header -->
                <!-- begin login-content -->
                <div class="login-content">

	                <?php if(!empty($errors)) : ?>
	                	<div class="alert alert-danger fade in m-b-15">
							<strong>Помилка!</strong>
							<?=$errors?>
							<span class="close" data-dismiss="alert">&times;</span>
						</div>
					<?php endif; ?>

					<div class="alert alert-success fade in m-b-15">
						<strong>Інформація!</strong>
						Ключ відновлення вірний! Введіть новий пароль:
						<span class="close" data-dismiss="alert">&times;</span>
					</div>

                    <form action="<?=SITE_URL?>reset/setnewpassword" method="POST" class="margin-bottom-0">
                    	<input type="hidden" name="id" value="<?=$user->id?>">
                    	<input type="hidden" name="secret_key" value="<?=$user->reset_key?>">
                    	<div class="form-group m-b-15">ID: <?=$user->id?></div>
                    	<div class="form-group m-b-15">Ім'я: <b><?=$user->name?></b></div>
                    	<div class="form-group m-b-15">Поштова скринька: <?=$user->email?></div>
                    	<div class="form-group m-b-15">Код діє до: <?=date("Y.n.d H:i:s", $user->reset_expires)?></div>
                        <div class="form-group m-b-15">
                            <input type="password" name="password" class="form-control input-lg" placeholder="Новий пароль" required />
                        </div>
                        <div class="form-group m-b-15">
                            <input type="password" name="re-password" class="form-control input-lg" placeholder="Повтор паролю" required />
                        </div>
                        <div class="m-t-10 m-b-20">
                            Ваш унікальний пароль, який використовується для входу на сайт. Повинно містити літери від а-я, a-z та числа. Довжина поля від 5 до 20 символів.
                        </div>
                        <div class="login-buttons">
                            <button type="submit" class="btn btn-success btn-block btn-lg">Встановити новий пароль</button>
                        </div>
                        <hr />
                        <p class="text-center text-inverse">
                            &copy; White Lion CMS All Right Reserved 2015
                        </p>
                        <p class="text-center text-inverse">
                            &copy; Color Admin All Right Reserved 2015
                        </p>
                    </form>
                </div>
                <!-- end login-content -->
            </div>
            <!-- end right-container -->
        </div>
        <!-- end login -->
	</div>
	<!-- end page container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="<?=SITE_URL?>assets/jquery/jquery-1.9.1.min.js"></script>
	<script src="<?=SITE_URL?>assets/jquery/jquery-migrate-1.1.0.min.js"></script>
	<script src="<?=SITE_URL?>assets/jquery-ui/ui/minified/jquery-ui.min.js"></script>
	<script src="<?=SITE_URL?>assets/bootstrap/js/bootstrap.min.js"></script>
	<!--[if lt IE 9]>
		<script src="<?=SITE_URL?>assets/crossbrowserjs/html5shiv.js"></script>
		<script src="<?=SITE_URL?>assets/crossbrowserjs/respond.min.js"></script>
		<script src="<?=SITE_URL?>assets/crossbrowserjs/excanvas.min.js"></script>
	<![endif]-->
	<script src="<?=SITE_URL?>assets/slimscroll/jquery.slimscroll.min.js"></script>
	<script src="<?=SITE_URL?>assets/jquery-cookie/jquery.cookie.js"></script>
	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
	<script src="<?=SITE_URL?>js/admin/apps.min.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->

	<script>
		$(document).ready(function() {
			App.init();
		});
	</script>
</body>
</html>