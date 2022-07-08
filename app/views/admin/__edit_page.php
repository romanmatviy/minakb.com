<div class="panel-body">
    <ul class="nav nav-tabs">
        <?php
        if(isset($PRE_TAB_NAME) && isset($PRE_TAB_PATH))
        {
            $PRE_TAB_NAME_keys = array_keys($PRE_TAB_NAME);
            foreach ($PRE_TAB_NAME as $key => $name) {
                echo("<li ");
                if($PRE_TAB_NAME_keys[0] == $key)
                    echo ('class="active"');
                echo("><a href=\"#tab-{$key}\" data-toggle=\"tab\" aria-expanded=\"true\">{$name}</a></li>");
            }
        }
        if($_SESSION['language']) {
            foreach ($_SESSION['all_languages'] as $lang) {
                echo("<li ");
                if(!isset($PRE_TAB_NAME) && $_SESSION['language'] == $lang)
                    echo ('class="active"');
                echo("><a href=\"#tab-{$lang}\" data-toggle=\"tab\" aria-expanded=\"true\">{$lang}</a></li>");
            }
        }
        else
        {
            echo('<li');
            if(!isset($PRE_TAB_NAME))
                    echo (' class="active"');
            echo('><a href="#tab-ntkd" data-toggle="tab" aria-expanded="true">Назва та опис</a></li>');
        }
        if(isset($_SESSION['option']->folder) && $_SESSION['option']->folder != '') {
        ?>
            <li><a href="#tab-photo" data-toggle="tab" aria-expanded="true">Фото</a></li>
        <?php }

        if($_SESSION['alias']->content >= 0) { ?>
            <li><a href="#tab-video" data-toggle="tab" aria-expanded="true">Відео</a></li>
            <?php if(isset($_SESSION['option']->folder) && $_SESSION['option']->folder != '') { ?>
                <li><a href="#tab-audio" data-toggle="tab" aria-expanded="true">Аудіо</a></li>
                <li><a href="#tab-files" data-toggle="tab" aria-expanded="true">Файли</a></li>
            <?php }
        }

        if(isset($AFTER_TAB_NAME) && isset($AFTER_TAB_PATH))
        {
            foreach ($AFTER_TAB_NAME as $key => $name) {
                echo("<li><a href=\"#tab-{$key}\" data-toggle=\"tab\" aria-expanded=\"true\">{$name}</a></li>");
            }
        }
        ?>
        <li><a href="#tab-statistic" data-toggle="tab" aria-expanded="true">Статистика</a></li>
    </ul>

    <div class="tab-content">
        <?php 
        if(isset($PRE_TAB_NAME_keys) && isset($PRE_TAB_PATH))
        {
            foreach ($PRE_TAB_PATH as $key => $path) {
                echo('<div class="tab-pane fade ');
                if($PRE_TAB_NAME_keys[0] == $key)
                    echo ('active in');
                echo('" id="tab-'.$key.'">');
                require $path;
                echo('</div>');
            }
        }
        $pageNames = array();
        if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $language) { ?>
            <div class="tab-pane fade <?=(!isset($PRE_TAB_NAME) && $_SESSION['language'] == $language) ? 'active in' : ''?>" id="tab-<?=$language?>">
                <?php require 'wl_ntkd/__tab_ntkdt.php'; ?>
            </div>
        <?php } } else { ?>
      		<div class="tab-pane fade <?=(!isset($PRE_TAB_NAME)) ? 'active in' : ''?>" id="tab-ntkd">
      			<?php require 'wl_ntkd/__tab_ntkdt.php'; ?>
      		</div>
        <?php } if(isset($_SESSION['option']->folder) && $_SESSION['option']->folder != '') { ?>
            <div class="tab-pane fade" id="tab-photo">
                <?php require_once 'wl_images/__tab-photo.php'; ?>
            </div>
        <?php } if($_SESSION['alias']->content >= 0) { ?>
            <div class="tab-pane fade" id="tab-video">
                <?php require_once 'wl_video/__tab-video.php'; ?>
            </div>
            <?php if(isset($_SESSION['option']->folder) && $_SESSION['option']->folder != '') { ?>
                <div class="tab-pane fade" id="tab-audio">
                    <?php require_once 'wl_audio/__tab-audio.php'; ?>
                </div>
                <div class="tab-pane fade" id="tab-files">
                    <?php require_once 'wl_files/__tab-files.php'; ?>
                </div>
            <?php } 
        }
        if(isset($AFTER_TAB_NAME) && isset($AFTER_TAB_PATH))
        {
            foreach ($AFTER_TAB_PATH as $key => $path) {
                echo('<div class="tab-pane fade" id="tab-'.$key.'">');
                require $path;
                echo('</div>');
            }
        }
        ?>
        <div class="tab-pane fade" id="tab-statistic">
            <?php require_once 'wl_statistic/__statistic.php'; ?>
        </div>
    </div>
</div>

<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>

<script type="text/javascript">
    var ALIAS_ID = <?=$_SESSION['alias']->id?>;
    var CONTENT_ID = <?=$_SESSION['alias']->content?>;
    <?php if(isset($_SESSION['option']->folder) && $_SESSION['option']->folder != '') { ?>
        var ALIAS_FOLDER = '<?=$_SESSION['option']->folder?>';
        var PHOTO_FILE_NAME = '<?=(isset($PHOTO_FILE_NAME)) ? $PHOTO_FILE_NAME : $_SESSION['alias']->alias?>';
        var PHOTO_TITLE = '<?=addslashes($_SESSION['alias']->name)?>';
    <?php } else { ?>
        var ALIAS_FOLDER = false;
        var PHOTO_FILE_NAME = false;
        var PHOTO_TITLE = false;
    <?php } if(isset($ADDITIONAL_TABLE) && $ADDITIONAL_TABLE != '') { ?>
        var ADDITIONAL_TABLE = '<?=$ADDITIONAL_TABLE?>';
        var ADDITIONAL_TABLE_ID = <?=$ADDITIONAL_TABLE_ID?>;
        var ADDITIONAL_FIELDS = '<?=$ADDITIONAL_FIELDS?>';
    <?php } else { ?>
        var ADDITIONAL_TABLE = false;
        var ADDITIONAL_TABLE_ID = false;
        var ADDITIONAL_FIELDS = false;
    <?php }
    $_SESSION['alias']->js_load[] = 'assets/ckeditor/ckeditor.js';
    $_SESSION['alias']->js_load[] = 'assets/ckfinder/ckfinder.js';
    $_SESSION['alias']->js_load[] = 'assets/white-lion/__edit_page.js';
    ?>
</script>