<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <?php if(isset($alias)) { ?>
                        <a href="<?=SITE_URL?>admin/wl_ntkd/<?=$alias->alias?>" class="btn btn-success btn-xs"><i class="fa <?=$alias->admin_ico?>"></i> До <?=$alias->alias?></a>
                    <?php } else { ?>
                        <a href="<?=SITE_URL?>admin/wl_ntkd" class="btn btn-success btn-xs"><i class="fa fa-globe"></i> До розділів</a>
                    <?php } ?>
                </div>
                <h4 class="panel-title">Значення за замовчуванням</h4>
            </div>
            <div class="panel-body" id="seo_robot">
                <div class="col-md-9">
                    <ul class="nav nav-tabs">
                        <?php if(isset($alias)) { ?>
                            <li class="active"><a href="#article" data-toggle="tab" aria-expanded="true" onclick="getRobotKeyWords(1)">Стаття / товар детально</a></li>
                        <?php } else { ?>
                            <li class="active"><a href="#main" data-toggle="tab" aria-expanded="true">Всі сторінки</a></li>
                            <li><a href="#article" data-toggle="tab" aria-expanded="true">Стаття / товар детально</a></li>
                        <?php } ?>
                        <li><a href="#groups" data-toggle="tab" aria-expanded="true" onclick="getRobotKeyWords(-1)">Група статтей / товарів </a></li>
                    </ul>
                    <div class="tab-content">
                        <?php if(!isset($alias)) { ?>
                        <div class="tab-pane active" id="main">
                            <?php if($_SESSION['language']) { ?>
                                <ul class="nav nav-tabs">
                                    <?php foreach ($_SESSION['all_languages'] as $lang) { ?>
                                        <li class="<?=($_SESSION['language'] == $lang) ? 'active' : ''?>"><a href="#language-tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
                                    <?php } ?>
                                </ul>
                                <div class="tab-content">
                                    <?php foreach ($_SESSION['all_languages'] as $lang) { 
                                        $tab = array('title' => '', 'description' => '', 'keywords' => '', 'text' => '', 'list' => '', 'meta' => '');
                                        if($tkdtm)
                                            foreach ($tkdtm as $row) {
                                                if($row->content == 0 && $row->language == $lang)
                                                {
                                                    foreach ($tab as $key => $value) {
                                                        $tab[$key] = $row->$key;
                                                    }
                                                    break;
                                                }
                                            }
                                        ?>
                                        <div class="tab-pane fade <?=($_SESSION['language'] == $lang) ? 'active in' : ''?> form-horizontal" id="language-tab-<?=$lang?>">
                                            <table>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Title</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" onChange="save('title', this, 0, '<?=$lang?>')" value="<?=$tab['title']?>" placeholder="Як назва сторінки" >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Description</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('description', this, 0, '<?=$lang?>')" class="form-control"><?=nl2br($tab['description'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Keywords</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" onChange="save('keywords', this, 0, '<?=$lang?>')" value="<?=$tab['keywords']?>" placeholder="keywords" >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Meta</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('meta', this, 0, '<?=$lang?>')" class="form-control"><?=nl2br($tab['meta'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Короткий опис сторінки</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('list', this, 0, '<?=$lang?>')" class="form-control" placeholder="Як у description"><?=nl2br($tab['list'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Текст сторінки</label>
                                                    <dic class="col-md-10">
                                                        <textarea id="editor0-<?=$lang?>"><?=$tab['text']?></textarea>
                                                    </dic>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <button type="button" class="btn btn-sm btn-warning " onclick="saveText(0, <?=$lang?>)">Зберегти текст сторінки</button>
                                                    </div>
                                                </div>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } else { 
                                $tab = array('title' => '', 'description' => '', 'keywords' => '', 'text' => '', 'list' => '', 'meta' => '');
                                if($tkdtm)
                                    foreach ($tkdtm as $row) {
                                        if($row->content == 0)
                                        {
                                            foreach ($tab as $key => $value) {
                                                $tab[$key] = $row->$key;
                                            }
                                            break;
                                        }
                                    } ?>
                            <div class="form-horizontal">
                                <table>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Title</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" onChange="save('title', this, 0)" value="<?=$tab['title']?>" placeholder="Як назва сторінки" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Description</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('description', this, 0)" class="form-control"><?=nl2br($tab['description'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Keywords</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" onChange="save('keywords', this, 0)" value="<?=$tab['keywords']?>" placeholder="keywords" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Meta</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('meta', this, 0)" class="form-control"><?=nl2br($tab['meta'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Короткий опис сторінки</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('list', this, 0)" class="form-control" placeholder="Як у description"><?=nl2br($tab['list'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Текст сторінки</label>
                                        <dic class="col-md-10">
                                            <textarea class="t-big" id="editor0"><?=$tab['text']?></textarea>
                                        </dic>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <button type="button" class="btn btn-sm btn-warning " onclick="saveText(1)">Зберегти текст сторінки</button>
                                        </div>
                                    </div>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <div class="tab-pane active" id="article">
                            <?php if($_SESSION['language']) { ?>
                                <ul class="nav nav-tabs">
                                    <?php foreach ($_SESSION['all_languages'] as $lang) { ?>
                                        <li class="<?=($_SESSION['language'] == $lang) ? 'active' : ''?>"><a href="#article-tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
                                    <?php } ?>
                                </ul>
                                <div class="tab-content">
                                    <?php foreach ($_SESSION['all_languages'] as $lang) { 
                                        $tab = array('title' => '', 'description' => '', 'keywords' => '', 'text' => '', 'list' => '', 'meta' => '');
                                        if($tkdtm)
                                            foreach ($tkdtm as $row) {
                                                if($row->content > 0 && $row->language == $lang)
                                                {
                                                    foreach ($tab as $key => $value) {
                                                        $tab[$key] = $row->$key;
                                                    }
                                                    break;
                                                }
                                            }
                                        ?>
                                        <div class="tab-pane fade <?=($_SESSION['language'] == $lang) ? 'active in' : ''?> form-horizontal" id="article-tab-<?=$lang?>">
                                            <table>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Title</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" onChange="save('title', this, 1, '<?=$lang?>')" value="<?=$tab['title']?>" placeholder="Як назва сторінки" >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Description</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('description', this, 1, '<?=$lang?>')" class="form-control"><?=nl2br($tab['description'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Keywords</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" onChange="save('keywords', this, 1, '<?=$lang?>')" value="<?=$tab['keywords']?>" placeholder="keywords" >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Meta</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('meta', this, 1, '<?=$lang?>')" class="form-control"><?=nl2br($tab['meta'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Короткий опис сторінки</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('list', this, 1, '<?=$lang?>')" class="form-control" placeholder="Як у description"><?=nl2br($tab['list'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Текст сторінки</label>
                                                    <dic class="col-md-10">
                                                        <textarea id="editor1-<?=$lang?>"><?=$tab['text']?></textarea>
                                                    </dic>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <button type="button" class="btn btn-sm btn-warning " onclick="saveText(1, <?=$lang?>)">Зберегти текст сторінки</button>
                                                    </div>
                                                </div>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } else { 
                                $tab = array('title' => '', 'description' => '', 'keywords' => '', 'text' => '', 'list' => '', 'meta' => '');
                                if($tkdtm)
                                    foreach ($tkdtm as $row) {
                                        if($row->content > 0)
                                        {
                                            foreach ($tab as $key => $value) {
                                                $tab[$key] = $row->$key;
                                            }
                                            break;
                                        }
                                    } ?>
                            <div class="form-horizontal">
                                <table>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Title</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" onChange="save('title', this, 1)" value="<?=$tab['title']?>" placeholder="Як назва сторінки" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Description</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('description', this, 1)" class="form-control"><?=nl2br($tab['description'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Keywords</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" onChange="save('keywords', this, 1)" value="<?=$tab['keywords']?>" placeholder="keywords" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Meta</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('meta', this, 1)" class="form-control"><?=nl2br($tab['meta'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Короткий опис сторінки</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('list', this, 1)" class="form-control" placeholder="Як у description"><?=nl2br($tab['list'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Текст сторінки</label>
                                        <dic class="col-md-10">
                                            <textarea class="t-big" id="editor1"><?=$tab['text']?></textarea>
                                        </dic>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <button type="button" class="btn btn-sm btn-warning " onclick="saveText(1)">Зберегти текст сторінки</button>
                                        </div>
                                    </div>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="tab-pane " id="groups">
                           <?php if($_SESSION['language']) { ?>
                                <ul class="nav nav-tabs">
                                    <?php foreach ($_SESSION['all_languages'] as $lang) { ?>
                                        <li class="<?=($_SESSION['language'] == $lang) ? 'active' : ''?>"><a href="#language-tab2-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
                                    <?php } ?>
                                </ul>
                                <div class="tab-content">
                                    <?php foreach ($_SESSION['all_languages'] as $lang) { 
                                        $tab = array('title' => '', 'description' => '', 'keywords' => '', 'text' => '', 'list' => '', 'meta' => '');
                                        if($tkdtm)
                                            foreach ($tkdtm as $row) {
                                                if($row->content < 0 && $row->language == $lang)
                                                {
                                                    foreach ($tab as $key => $value) {
                                                        $tab[$key] = $row->$key;
                                                    }
                                                    break;
                                                }
                                            }
                                        ?>
                                        <div class="tab-pane fade <?=($_SESSION['language'] == $lang) ? 'active in' : ''?> form-horizontal" id="language-tab2-<?=$lang?>">
                                            <table>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Title</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" onChange="save('title', this, -1, '<?=$lang?>')" value="<?=$tab['title']?>" placeholder="Як назва сторінки" >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Description</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('description', this, -1, '<?=$lang?>')" class="form-control"><?=nl2br($tab['description'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Keywords</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" onChange="save('keywords', this, -1, '<?=$lang?>')" value="<?=$tab['keywords']?>" placeholder="keywords" >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Meta</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('meta', this, -1, '<?=$lang?>')" class="form-control"><?=nl2br($tab['meta'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Короткий опис сторінки</label>
                                                    <div class="col-md-10">
                                                        <textarea onChange="save('list', this, -1, '<?=$lang?>')" class="form-control" placeholder="Як у description"><?=nl2br($tab['list'])?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Текст сторінки</label>
                                                    <dic class="col-md-10">
                                                        <textarea class="t-big" id="editor-1-<?=$lang?>"><?=$tab['text']?></textarea>
                                                    </dic>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <button type="button" class="btn btn-sm btn-warning " onclick="saveText(-1, <?=$lang?>)">Зберегти текст сторінки</button>
                                                    </div>
                                                </div>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } else { 
                                $tab = array('title' => '', 'description' => '', 'keywords' => '', 'text' => '', 'list' => '', 'meta' => '');
                                if($tkdtm)
                                    foreach ($tkdtm as $row) {
                                        if($row->content < 0)
                                        {
                                            foreach ($tab as $key => $value) {
                                                $tab[$key] = $row->$key;
                                            }
                                            break;
                                        }
                                    } ?>
                            <div class="form-horizontal">
                                <table>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Title</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" onChange="save('title', this, -1)" value="<?=$tab['title']?>" placeholder="Як назва сторінки" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Description</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('description', this, -1)" class="form-control"><?=nl2br($tab['description'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Keywords</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" onChange="save('keywords', this, -1)" value="<?=$tab['keywords']?>" placeholder="keywords" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Meta</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('meta', this, -1)" class="form-control"><?=nl2br($tab['meta'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Короткий опис сторінки</label>
                                        <div class="col-md-10">
                                            <textarea onChange="save('list', this, -1)" class="form-control" placeholder="Як у description"><?=nl2br($tab['list'])?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Текст сторінки</label>
                                        <dic class="col-md-10">
                                            <textarea class="t-big" id="editor-1"><?=$tab['text']?></textarea>
                                        </dic>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <button type="button" class="btn btn-sm btn-warning " onclick="saveText(-1)">Зберегти текст сторінки</button>
                                        </div>
                                    </div>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-inverse" data-sortable-id="ui-buttons-1" -="">
                        <div class="panel-heading">
                            <div class="panel-heading-btn">
                            </div>
                            <h4 class="panel-title">Слова</h4>
                        </div>
                        <div class="panel-body" id="words">
                            <button type="button" class="btn btn-default m-b-5" title="назва сторінки">{name}</button>
                            <button type="button" class="btn btn-default m-b-5" title="Базова адреса сайту: <?=SITE_URL?>">{SITE_URL}</button>
                            <button type="button" class="btn btn-default" title="Базова адреса зображень: <?=IMG_PATH?>">{IMG_PATH}</button>
                            <hr>
                            <div id="pageKeyWords">
                                <?php if(isset($alias)) {
                                    if($words = $this->load->function_in_alias($alias->id, '__getRobotKeyWords', 1, true))
                                        foreach ($words as $word) {
                                            echo '<button type="button" class="btn btn-default m-b-5 m-r-5">'.$word.'</button>';
                                        }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?=SITE_URL?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>assets/ckfinder/ckfinder.js"></script>
<script type="text/javascript">
    <?php if($_SESSION['language']) 
        foreach($_SESSION['all_languages'] as $lng) {
            if(empty($alias))
                echo "CKEDITOR.replace( 'editor0-{$lng}' ); ";
            echo "CKEDITOR.replace( 'editor1-{$lng}' ); ";
            echo "CKEDITOR.replace( 'editor-1-{$lng}' ); ";
        }
        else
        {
            if(empty($alias))
                echo "CKEDITOR.replace( 'editor0' ); ";
            echo "CKEDITOR.replace( 'editor1' ); ";
            echo "CKEDITOR.replace( 'editor-1' );"; 
        } ?>
        CKFinder.setupCKEditor( null, {
        basePath : '<?=SITE_URL?>assets/ckfinder/',
        filebrowserBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html?type=Images',
        filebrowserFlashBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html?type=Flash',
        filebrowserUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Images',
        filebrowserFlashUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Flash',
    });
</script>

<script>
    document.onreadystatechange = function () {
        if (document.readyState == "complete") {

            $('#seo_robot input').on('click',function (e) {
                $('#seo_robot').find('#wordTarget').removeAttr('id');
                var $target = $(event.target).attr('id', 'wordTarget');
            })

            $('#seo_robot textarea').on('click',function (e) {
                $('#seo_robot').find('#wordTarget').removeAttr('id');
                var $target = $(event.target).attr('id', 'wordTarget');
            })

            $('#words').on('click', function (e) {
                var $wordTarget = $('#wordTarget');
                if($wordTarget.length){
                    var buttonText = event.target.textContent;
                    var wordTargetValue = $wordTarget.val();
                    var cursorPos = $('#wordTarget').prop('selectionStart');

                    var textBefore = wordTargetValue.substring(0,  cursorPos );
                    var textAfter  = wordTargetValue.substring( cursorPos, wordTargetValue.length );
                    $('#wordTarget').val( textBefore + buttonText + textAfter );

                    $('#wordTarget').focus()
                    wordTarget.setSelectionRange(cursorPos + buttonText.length, cursorPos + buttonText.length);

                    $('#wordTarget').change();
                }
            })
       }
     }

     var data;
    function save (field, e, content, lang) {
        $('#saveing').css("display", "block");
        var value = '';
        if(e != false) value = e.value;
        else value = data;

        $.ajax({
            url: SITE_URL + "admin/wl_ntkd/save_robot",
            type: 'POST',
            data: {
                alias: <?=(isset($alias)) ? $alias->id : 0 ?>,
                content: content,
                field: field,
                data: value,
                language: lang,
                json: true
            },
            success: function(res){
                if(res['result'] == false) {
                    $.gritter.add({title:"Помилка!",text:res['error']});
                } else {
                    language = '';
                    if(lang) language = lang;
                    $.gritter.add({title:field+' '+language,text:"Дані успішно збережено!"});
                }
                $('#saveing').css("display", "none");
            },
            error: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
            },
            timeout: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
            }
        });
    }
    function getRobotKeyWords (content) {
        $('#saveing').css("display", "block");
        $('#pageKeyWords').html('');

        $.ajax({
            url: SITE_URL + "admin/wl_ntkd/getRobotKeyWords",
            type: 'POST',
            data: {
                alias: <?=(isset($alias)) ? $alias->id : 0 ?>,
                content: content,
                json: true
            },
            success: function(res){
                if(res['result'] == false) {
                    $.gritter.add({title:"Помилка!",text:res['error']});
                } else if(res) {
                    var buttons = '';
                    res.forEach(function(item, i, res) {
                      buttons += '<button type="button" class="btn btn-default m-b-5 m-r-5">'+item+'</button> ';
                    });
                    $('#pageKeyWords').html(buttons);
                }
                $('#saveing').css("display", "none");
            },
            error: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
            },
            timeout: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
            }
        });
    }
    <?php if($_SESSION['language']) { ?>
        function saveText(content, lang)
        {
            data = CKEDITOR.instances['editor'+content+'-'+lang].getData();
            save('text', false, content, lang);
        }
    <?php } else { ?>
        function saveText(content)
        {
            data = CKEDITOR.instances['editor'+content].getData();
            save('text', false, content, false);
        }
    <?php } ?>
</script>