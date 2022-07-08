<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Увійти в систему | <?=SITE_NAME?></title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,700,300,600,400&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<link href="<?=SITE_URL?>assets/jquery-ui/themes/base/minified/jquery-ui.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="<?=SITE_URL?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
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
                    <img src="<?=SITE_URL?>style/admin/login-bg/bg-7.jpg" data-id="login-cover-image" alt="" />
                </div>
                <div class="news-caption">
                    <h4 class="caption-title"><i class="fa fa-diamond text-success"></i> <?=SITE_NAME?> </h4>
                    <p> <a href="<?=SITE_URL?>"> <?=SITE_URL?> </a> </p>
                </div>
            </div>
            <!-- end news-feed -->
            <!-- begin right-content -->
            <div class="right-content">
                <!-- begin login-header -->
                <div class="login-header">
                    <div class="brand">
                        <span class="logo"></span> Вхід в систему
                        <small>Панель керування сайтом</small>
                    </div>
                    <div class="icon">
                        <i class="fa fa-sign-in"></i>
                    </div>
                </div>
                <!-- end login-header -->
                <!-- begin login-content -->
                <div class="login-content">

	                <?php if(!empty($_SESSION['notify']->errors)): ?>
			           <div class="alert alert-danger fade in">
			                <span class="close" data-dismiss="alert">×</span>
			                <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Помилка!', 0)?></h4>
			                <p><?=$_SESSION['notify']->errors?></p>
			            </div>
			        <?php elseif(!empty($_SESSION['notify']->success)): ?>
			            <div class="alert alert-success fade in">
			                <span class="close" data-dismiss="alert">×</span>
			                <h4><i class="fa fa-check"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Успіх!', 0)?></h4>
			                <p><?=$_SESSION['notify']->success?></p>
			            </div>
			        <?php endif; unset($_SESSION['notify']); ?>

                    <form action="<?=SITE_URL?>login/process" method="POST" class="margin-bottom-0" id="formLogin">
                        <?php if(isset($_GET['redirect']) || $this->data->re_post('redirect')) { ?>
			                <input type="hidden" name="redirect" value="<?=$this->data->re_post('redirect', $this->data->get('redirect'))?>">
			            <?php } ?>
                        <div class="form-group m-b-15">
                        	<input type="email" name="email" value="<?=$this->data->re_post('email')?>" placeholder="Email" required  class="form-control input-lg" />
                        </div>
                        <div class="form-group m-b-15">
                            <input type="password" name="password" class="form-control input-lg" placeholder="Password" required />
                        </div>
                        <div class="login-buttons">
                        	<?php $this->load->library('recaptcha');
                        	if($this->recaptcha->public_v3) {
                        		$this->recaptcha->form_v3($this->text('Увійти'), 'formLogin', "btn btn-success btn-block btn-lg");
                        	} else { 
                        		$this->recaptcha->form(); ?>
                        		<button type="submit" class="btn btn-success btn-block btn-lg"><?=$this->text('Увійти', 4)?></button>
                        	<?php } ?>
                        </div>
                        <div class="m-t-20 m-b-40 p-b-40">
                            <a href="<?=SITE_URL?>reset" class="text-success">Забув пароль</a>.
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
	<script src="<?=SITE_URL?>assets/jquery/jquery-1.11.1.min.js"></script>
	<script src="<?=SITE_URL?>assets/jquery/jquery-migrate-1.2.1.min.js"></script>
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
	<script src="<?=SITE_URL?>assets/color-admin/apps.min.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->

	<script>
		$(document).ready(function() {
			App.init();
		});
	</script>
</body>
</html>