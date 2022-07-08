<div class="row">
<div class="col-md-7">
    <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
        <div class="panel-heading">
            <div class="panel-heading-btn">
                <a href="<?= $comment->parent == 0 ? SITE_URL.'admin/wl_comments' : SITE_URL.'admin/wl_comments/'.$comment->parent ?>" class="btn btn-warning btn-xs">Повернутись</a>
                <button onclick="$('#uninstall-form').slideToggle()" class="btn btn-danger btn-xs">Видалити коментар</button>
            </div>
            <h4 class="panel-title">Редагувати коментар</h4> 
        </div>
        <div id="uninstall-form" class="alert alert-danger fade in" style="display:none;">
            <i class="fa fa-trash fa-2x pull-left"></i>
            <form action="<?=SITE_URL?>admin/wl_comments/delete" method="POST">
                <p>Ви впевнені що бажаєте видалити коментар?</p>
                <input type="hidden" name="id" value="<?=$comment->id?>">
                <input type="submit" value="Видалити" class="btn btn-danger">
                <button type="button" style="margin-left:25px" onclick="$('#uninstall-form').slideUp()" class="btn btn-info">Скасувати</button>
            </form>
        </div>
        <div class="panel-body <?=($comment->status==4)?'alert-danger':''?><?=($comment->status == 2 || $comment->status == 3)?'alert-warning':''?>">
            <form action="<?=SITE_URL?>admin/wl_comments/save" method="POST" class="form-horizontal">
                <input type="hidden" name="id" value="<?=$comment->id?>">
                <div class="row m-b-5" >
                    <label class="col-md-3 control-label">Автор</label>
                    <div class="col-md-9">
                        <a href="<?=SITE_URL?>admin/wl_users/<?=$comment->user_email?>"><?=$comment->user_name .'. '. $comment->user_email?></a>
                        <span class="pull-right"><?=date("d.m.Y H:i", $comment->date_add)?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Відгук додано</label>
                    <div class="col-md-5">
                        <input type="text" name="date_add" class="form-control datepicker" value="<?=date("d.m.Y", $comment->date_add)?>" required>
                    </div>
                    <div class="col-md-4">
                        <input type="time" name="time_add" class="form-control" value="<?=date("H:i", $comment->date_add)?>" required>
                    </div>
                </div>
                <div class="row m-b-10" >
                    <label class="col-md-3 control-label">Сторінка</label>
                    <div class="col-md-9">
                        <a href="<?=SITE_URL.$comment->link?>#comment-<?=$comment->id?>"><?=$comment->page_name?></a>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Оцінка від користувача</label>
                    <div class="col-md-9">
                        <input type="number" name="rating" class="form-control" min="0" max="5" value="<?=$comment->rating?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Відгук</label>
                    <div class="col-md-9">
                        <textarea name="comment" class="form-control" rows="4" ><?=$comment->comment?></textarea>
                    </div>
                </div>
                <?php if($comment->images) { ?>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Зображення до коментаря
                        <br>
                        (Кожний файл з нового рядка)
                        </label>
                        <div class="col-md-9">
                            <?=IMG_PATH.'comments/'.$comment->id.'/'?>
                            <textarea name="images" class="form-control" rows="4" ><?php
                            $comment->images = explode('|||', $comment->images);
                            echo implode('&#10;', $comment->images); ?></textarea>
                        </div>
                    </div>
                <?php } ?>
                <div class="form-group" >
                    <label class="col-md-3 control-label">Статус</label>
                    <div class="col-md-9">
                        <select name="status" class="form-control">
                            <?php $statuses = array(1 => 'Активний', 2 => 'Новий активний', 3 => 'Чекає на модерацію', 4 => 'Відключено');
                            foreach ($statuses as $key => $name) {
                                $selected = ($key == $comment->status) ? 'selected' : '';
                                echo "<option value='{$key}' {$selected}>{$name}</option>";
                            }
                             ?>
                        </select>
                    </div>
                </div>
                <?php if($comment->manager) { ?>
                    <div class="row m-b-5" >
                        <label class="col-md-3 control-label">Менеджер</label>
                        <div class="col-md-9">
                            <a href="<?=SITE_URL?>admin/wl_users/<?=$comment->manager_email?>"><?=$comment->manager_name .'. '. $comment->manager_email?></a>
                            <span class="pull-right"><?=date("d.m.Y H:i", $comment->date_manage)?></span>
                        </div>
                    </div>
                <?php } ?>
                <div class="form-group">
                    <div class="col-md-3"></div>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-sm btn-success" >Редагувати і зберегти</button>
                    </div>
                </div>
            </form>
            <?php if($comment->images) {
                echo "<h4>Зображення до коментаря:</h4>";
                echo('<p id="lightgallery">');
                foreach ($comment->images as $image) {
                    echo '<a href="'.IMG_PATH.'comments/'.$comment->id.'/'.$image.'" title="'.$image.'"><img src="'.IMG_PATH.'comments/'.$comment->id.'/m_'.$image.'"></a> ';
                }
                echo('</p>'); ?>

                <link rel="stylesheet" type="text/css" href="/assets/lightGallery/css/lightgallery.css">

                <?php $_SESSION['alias']->js_load[] = "assets/lightGallery/js/lightgallery.js";
                    $_SESSION['alias']->js_init[] = "$('#lightgallery').lightGallery();";
            } ?>
        </div>
    </div>
</div>
<?php 
if($comment->parent) {
    if($father = $this->db->getAllDataById('wl_comments', $comment->parent)) { ?>
    <div class="col-md-5">
        <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
            <div class="panel-heading">
                <h4 class="panel-title">Батьківський коментар (#<?=$father->id?>)</h4>
            </div>
            <div class="panel-body">
                <p><?=nl2br($father->comment)?></p>
            </div>
        </div>
    </div> 
 <?php } 
}  
if($comment->parent == 0) { ?>
<div class="col-md-5">
    <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
        <div class="panel-heading">
            <h4 class="panel-title">Відповіді на даний коментар</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <br>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">
                    <button id="reply" class="btn btn-sm btn-success" onclick="$('#replyBlock').slideToggle()">Залишити відповідь</button>
                </div>
                <br> 
                <div id="replyBlock" hidden>
                    <form action="<?=SITE_URL?>admin/wl_comments/reply" method="POST" >
                        <input type="hidden" name="alias" value="<?= $comment->alias ?>">
                        <input type="hidden" name="content" value="<?= $comment->content ?>">
                        <input type="hidden" name="parent" value="<?= $comment->id ?>">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">
                            <h4 class="title">Відповідь на коментар</h4>
                        </div>    
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">
                            <textarea name="comment" style="width: 100%;" placeholder=" Ваша відповідь " required rows="4"></textarea>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-right " style="margin-bottom: 20px;">
                            <input type="submit" class="btn btn-sm btn-success" value="Надіслати">
                        </div>    
                    </form>
                </div>
            </div>
                 
            <?php if($comment->reply) {
                if($replays = $this->wl_comments_model->get(array('parent' => $comment->id))) {
                    foreach ($replays as $replay) { ?>
                        <div class="row <?=($replay->status == 4)?'alert-danger':''?>" style="margin-top: 10px" >
                            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5 ">
                                <h4><a href="<?=SITE_URL?>admin/wl_comments/<?=$replay->id?>"><i class="fa fa-pencil"></i></a> <?=$replay->user_name?></h4>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-6 text-right">
                                <p style="font-size: 10px;">Відповів на коментар: <?=date("d.m.Y H:i", $replay->date_add)?></p>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-12 " >
                                <p><?=nl2br($replay->comment)?></p>
                            </div>
                        </div>
            <?php } } } ?>
        </div>
    </div>    
</div>

<?php } ?>
</div>

<link rel="stylesheet" href="/assets/bootstrap-datepicker/css/datepicker3.css">
<?php $_SESSION['alias']->js_load[] = '/assets/bootstrap-datepicker/js/bootstrap-datepicker.js';
$_SESSION['alias']->js_init[] = "$('.datepicker').datepicker({ format: 'dd.mm.yyyy', language: 'uk', autoclose: true })";
 ?>
