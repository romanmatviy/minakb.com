<?php
$ntkd = $pageNames = array();
$where_ntkd['alias'] = $_SESSION['alias']->id;
$where_ntkd['content'] = $product->id;
if($wl = $this->db->getAllDataByFieldInArray('wl_ntkd', $where_ntkd))
{
	if($_SESSION['language']) {
		foreach ($wl as $nt) {
        $ntkd[$nt->language] = $nt;
        $pageNames[$nt->language] = $nt->name;
    }
    $where_ntkd['name'] = $wl[0]->name;
    foreach ($_SESSION['all_languages'] as $lang) {
      if(!isset($ntkd[$lang])) {
        $where_ntkd['language'] = $lang;
        $id = $this->db->insertRow('wl_ntkd', $where_ntkd);

        $ntkd[$lang] = (object) $where_ntkd;
        $ntkd[$lang]->id = $id;
        $pageNames[$lang] = $where_ntkd['name'];
      }
    }
  } else
	   $ntkd = $wl[0];
}

$changePriceTab = false;
$list = $productOptions = $product_options_values = $product_options_changePrice = $options_parents = array();
if($_SESSION['option']->useGroups && $groups)
{
    $emptyChildsList = array();
    foreach ($groups as $g) {
        $g->parent = (int) $g->parent;
        $list[$g->id] = $g;
        $list[$g->id]->child = array();
        if(isset($emptyChildsList[$g->id]))
            foreach ($emptyChildsList[$g->id] as $c) {
                $list[$g->id]->child[] = $c;
            }
        if($g->parent > 0)
        {
            if(isset($list[$g->parent]->child))
                $list[$g->parent]->child[] = $g->id;
            else
            {
                if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
                else $emptyChildsList[$g->parent] = array($g->id);
            }
        }
    }
    if(!empty($list))
    {
        if($_SESSION['option']->ProductMultiGroup)
        {
            foreach ($product->group as $parent) {
                $parent = (int) $parent;
                while ($parent != 0) {
                    if(!in_array($parent, $options_parents))
                        array_unshift($options_parents, $parent);
                    if(isset($list[$parent]))
                      $parent = $list[$parent]->parent;
                    else
                      break;
                }
            }
        }
        else
        {
            $parent = (int) $product->group;
            while ($parent != 0) {
                array_unshift($options_parents, $parent);
                $parent = $list[$parent]->parent;
            }
        }
    }
}
array_unshift($options_parents, 0);

if($options = $this->db->getAllDataByFieldInArray($this->shop_model->table('_product_options'), $product->id, 'product'))
    foreach ($options as $option) {
        if($option->language != '' && in_array($option->language, $_SESSION['all_languages']))
            $product_options_values[$option->option][$option->language] = $option->value;
        else
            $product_options_values[$option->option] = $option->value;
        $product_options_changePrice[$option->option] = unserialize($option->changePrice);
    }

$this->load->smodel('options_model');
foreach ($options_parents as $option_id) {
    if($options = $this->options_model->getOptions($option_id))
    {
        foreach ($options as $option) {
            if(!empty($option->changePrice) && !$changePriceTab)
                $changePriceTab = true;
        }
        $productOptions[$option_id] = $options;
    }
}

$storages = $marketing = array();
if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1'))
    foreach ($cooperation as $c) {
        if($c->type == 'storage') $storages[] = $c->alias2;
        if($c->type == 'marketing') $marketing[] = $this->load->function_in_alias($c->alias2, '__tab_product', $product, true);
    }

$url = $this->data->url();
array_shift($url);
array_pop ($url);
$url = implode('/', $url); 
?>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$product->alias?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Дивитися як клієнт</a>
          <a href="<?=SITE_URL.'admin/'.$product->link?>" class="btn btn-success btn-xs"><i class="fa fa-undo"></i> Швидкий перегляд</a>
          <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/clear_cache?id='.$product->id?>" class="btn btn-warning btn-xs"><i class="fa fa-trash" aria-hidden="true"></i> Очистити КЕШ</a>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs m-l-10"><i class="fa fa-window-close" aria-hidden="true"></i> Видалити <?=$_SESSION['admin_options']['word:product_to_delete']?></button>
        </div>

          <h5 class="panel-title">
            Додав: <?=$product->author_add_name .' '.date('d.m.Y H:i', $product->date_add)?>.
            Редаговано: <?=$product->author_edit_name .' '.date('d.m.Y H:i', $product->date_edit)?>
          </h5>
      </div>

      <div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
        <i class="fa fa-trash fa-2x pull-left"></i>
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
          <p>Ви впевнені що бажаєте видалити <?=$_SESSION['admin_options']['word:product_to_delete']?>?</p>
          <input type="hidden" name="id" value="<?=$product->id?>">
          <input type="submit" value="Видалити" class="btn btn-danger">
          <button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
        </form>
      </div>

      <?php if(isset($_SESSION['notify'])) {
        require APP_PATH.'views/admin/notify_view.php';
      } ?>

      <div class="panel-body">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#tab-main" data-toggle="tab" aria-expanded="true">Загальні дані</a></li>
          <?php if($changePriceTab) { ?>
            <li><a href="#tab-changePrice" data-toggle="tab" aria-expanded="true">Керування ціною</a></li>
          <?php } if(!empty($marketing)) foreach($marketing as $tab) { ?>
            <li><a href="#tab-<?=$tab->key?>" data-toggle="tab" aria-expanded="true"><?=$tab->name?></a></li>
          <?php } if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $language) { ?>
          	<li><a href="#tab-<?=$language?>" data-toggle="tab" aria-expanded="true"><?=$language?></a></li>
          <?php } } else { ?>
          	<li><a href="#tab-ntkd" data-toggle="tab" aria-expanded="true">Назва та опис</a></li>
          <?php } ?>
          <li><a href="#tab-photo" data-toggle="tab" aria-expanded="true">Фото</a></li>
          <li><a href="#tab-video" data-toggle="tab" aria-expanded="true">Відео</a></li>
          <li><a href="#tab-audio" data-toggle="tab" aria-expanded="true">Аудіо</a></li>
          <li><a href="#tab-files" data-toggle="tab" aria-expanded="true">Файли</a></li>
          <?php /* <li><a href="#tab-statistic" data-toggle="tab" aria-expanded="true">Статистика</a></li> */ ?>
          <li><a href="#tab-similar" data-toggle="tab" aria-expanded="true">Подібні</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="tab-main">
            <?php require_once 'edit_tabs/tab-main.php'; ?>
          </div>
          <?php if($changePriceTab) { ?>
            <div class="tab-pane fade" id="tab-changePrice">
              <?php require 'edit_tabs/tab-changePrice.php'; ?>
            </div>
          <?php } if(!empty($marketing))
              foreach($marketing as $tab) {
                echo('<div class="tab-pane fade" id="tab-'.$tab->key.'">'.$tab->content.'</div>');
           } if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $language) { ?>
            <div class="tab-pane fade" id="tab-<?=$language?>">
              <?php require 'edit_tabs/tab-ntkd.php'; ?>
            </div>
          <?php } } else { ?>
        		<div class="tab-pane fade" id="tab-ntkd">
        			<?php require 'edit_tabs/tab-ntkd.php'; ?>
        		</div>
          <?php } ?>
          <div class="tab-pane fade" id="tab-photo">
            <?php
            $ADDITIONAL_TABLE_ID = $product->id;
            $ADDITIONAL_FIELDS = 'author_edit=>user,date_edit=>time';
            $ADDITIONAL_TABLE = $this->shop_model->table('_products');
			$ADDITIONAL_IMAGE_OPTIONS = $productOptions;
            $ADDITIONAL_IMAGE_OPTIONS_VALUE = $product_options_values;
            require_once APP_PATH.'views/admin/wl_images/__tab-photo.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-video">
            <?php require_once APP_PATH.'views/admin/wl_video/__tab-video.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-audio">
            <?php require_once APP_PATH.'views/admin/wl_audio/__tab-audio.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-files">
            <?php require_once APP_PATH.'views/admin/wl_files/__tab-files.php'; ?>
          </div>
          <?php /* 
          <div class="tab-pane fade" id="tab-statistic">
            <?php require_once APP_PATH.'views/admin/wl_statistic/__statistic.php'; ?>
          </div> */ ?>
          <div class="tab-pane fade" id="tab-similar">
            <?php require 'edit_tabs/tab-similar.php'; ?>
          </div>
        </div>

      </div>
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
  var ALIAS_FOLDER = '<?=$_SESSION['option']->folder?>';
  var PHOTO_FILE_NAME = '<?=$product->alias?>';
  var PHOTO_TITLE = '<?=htmlentities($_SESSION['alias']->name, ENT_QUOTES, 'utf-8')?>';
  var ADDITIONAL_TABLE = '<?=$ADDITIONAL_TABLE?>';
  var ADDITIONAL_TABLE_ID = <?=$ADDITIONAL_TABLE_ID?>;
  var ADDITIONAL_FIELDS = '<?=$ADDITIONAL_FIELDS?>';

  <?php
  $_SESSION['alias']->js_load[] = 'assets/ckeditor/ckeditor.js';
  $_SESSION['alias']->js_load[] = 'assets/ckfinder/ckfinder.js';
  $_SESSION['alias']->js_load[] = 'assets/white-lion/__edit_page.js';

  if($_SESSION['option']->ProductUseArticle) {
  ?>
    function saveNameWithArticle (e, lang) {
      $('#saveing').css("display", "block");

      $.ajax({
          url: "<?=SITE_URL?>admin/wl_ntkd/save",
          type: 'POST',
          data: {
              alias: ALIAS_ID,
              content: CONTENT_ID,
              field: 'name',
              data: e.value + ' <?=$product->article?>',
              language: lang,
              additional_table : ADDITIONAL_TABLE,
              additional_table_id : ADDITIONAL_TABLE_ID,
              additional_fields : ADDITIONAL_FIELDS,
              json: true
          },
          success: function(res){
              if(res['result'] == false) {
                  $.gritter.add({title:"Помилка!",text:res['error']});
              } else {
                  language = '';
                  if(lang) language = lang;
                  $.gritter.add({title:'Назва '+language,text:"Дані успішно збережено!"});
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
  <?php } ?>

  function saveOption (e, label) {
    $('#saveing').css("display", "block");
    $.ajax({
      url: "<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/saveOption",
      type: 'POST',
      data: {
        id: '<?=$product->id?>',
        option: e.name,
        data: e.value,
        json: true
      },
      success: function(res){
        if(res['result'] == false){
            $.gritter.add({title:"Помилка!", text:label + ' ' + res['error']});
        } else {
          $.gritter.add({title:label, text:"Дані успішно збережено!"});
        }
        $('#saveing').css("display", "none");
      },
      error: function(){
        $.gritter.add({title:"Помилка!", text:"Помилка! Спробуйте ще раз!"});
        $('#saveing').css("display", "none");
      },
      timeout: function(){
        $.gritter.add({title:"Помилка!", text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
        $('#saveing').css("display", "none");
      }
    });
  }
</script>