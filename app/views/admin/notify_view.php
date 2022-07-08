<?php if(!empty($_SESSION['notify']->errors)) { ?>
   <div class="alert alert-danger fade in">
        <span class="close" data-dismiss="alert">×</span>
        <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Помилка!'?></h4>
        <p><?=$_SESSION['notify']->errors?></p>
    </div>
<?php } elseif(!empty($_SESSION['notify']->warning)) { ?>
    <div class="alert alert-warning fade in">
        <span class="close" data-dismiss="alert">×</span>
        <i class="fa fa-exclamation-triangle fa-2x pull-left" aria-hidden="true"></i>
        <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Увага!'?></h4>
        <p><?=$_SESSION['notify']->warning?></p>
    </div>
<?php } elseif(!empty($_SESSION['notify']->success)) { ?>
    <div class="alert alert-success fade in">
        <span class="close" data-dismiss="alert">×</span>
        <i class="fa fa-check fa-2x pull-left"></i>
        <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Успіх!'?></h4>
        <p><?=$_SESSION['notify']->success?></p>
    </div>
<?php } unset($_SESSION['notify']); ?>