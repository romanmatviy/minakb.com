<!DOCTYPE html>
<!--[if IE 8]> <html lang="<?=$_SESSION['language']?>" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="<?=$_SESSION['language']?>">
<!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <title>Підтвердити профіль користувача <?=SITE_NAME?></title>
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
    <div id="divLoading"></div>
    <!-- begin #page-loader -->
    <div id="page-loader" class="fade in"><span class="spinner"></span></div>
    <!-- end #page-loader -->

    <div class="login-cover">
        <div class="login-cover-image"><img src="<?=SERVER_URL?>style/admin/login-bg/bg-1.jpg" data-id="login-cover-image" alt="" /></div>
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

                <form action="<?=SITE_URL?>signup/confirmed" method="POST" class="margin-bottom-0" onsubmit="$('#divLoading').addClass('show');">
                    <div class="form-group m-b-20">
                        <input type="text" name="code" value="<?=$this->data->re_post('code')?>" class="form-control input-lg" placeholder="<?=$this->text('Код підтвердження профілю')?>" required />
                    </div>
                    <div class="login-buttons">
                        <button type="submit" class="btn btn-success btn-block btn-lg"><?=$this->text('Перевірити')?></button>
                    </div>
                    <div class="m-t-20">
                        <?=$this->text('Якщо лист із кодом підтвердження не надійшов')?>: <br>
                        1. <?=$this->text('зачекайте 5 хв, можливі затримки мережі')?> <br>
                        2. <?=$this->text('перевірте папку "Спам", ймовірно фільтри безпеки невірно розпізнали лист')?> <br>
                        3. <a href="<?=SITE_URL?>login/emailSend"><?=$this->text('надіслати лист із кодом підтрвердження заново')?></a>
                    </div>
                </form>
            </div>
        </div>
        <!-- end login -->

        <ul class="login-bg-list">
            <li class="active"><a href="#" data-click="change-bg"><img src="<?=SERVER_URL?>style/admin/login-bg/bg-1.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SERVER_URL?>style/admin/login-bg/bg-2.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SERVER_URL?>style/admin/login-bg/bg-3.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SERVER_URL?>style/admin/login-bg/bg-4.jpg" alt="" /></a></li>
            <li><a href="#" data-click="change-bg"><img src="<?=SERVER_URL?>style/admin/login-bg/bg-6.jpg" alt="" /></a></li>
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