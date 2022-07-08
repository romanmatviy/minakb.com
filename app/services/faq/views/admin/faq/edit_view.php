<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <?php if($_SESSION['option']->useGroups && $question->group > 0){ ?>
            <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$this->data->uri(2)?>" class="btn btn-info btn-xs">До питань групи</a>
          <?php } else { ?>
            <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">До всіх питань</a>
          <?php } ?>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити питання</button>
        </div>

          <h5 class="panel-title">Питання #<?=$question->id?>. <?=$question->question?></h5>
      </div>
      <div id="uninstall-form" style="background: rgba(236, 0, 0, 0.68); color: #000; padding: 10px; display: none;">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete_question" method="POST">
          Ви впевнені що бажаєте видалити питання?
    			<br>
    			<input type="hidden" name="id" value="<?=$question->id?>">
    			<input type="submit" value="Видалити" style="margin-left:25px; float:left;">
    			<button type="button" style="margin-left:25px" onClick="showUninstalForm()">Скасувати</button>
		    </form>
		    <div class="clear"></div>
      </div>
      <div class="panel-body">

        <?php if(isset($_SESSION['notify'])){ 
        	require APP_PATH.'views/admin/notify_view.php';
        } ?>

        <ul class="nav nav-tabs">
          <?php if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $lang) { ?>
          	<li <?=($_SESSION['language'] == $lang)?'class="active"':''?>>
              <a href="#tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a>
            </li>
          <?php } } else { ?>
          	<li class="active"><a href="#tab-ntkd" data-toggle="tab" aria-expanded="true">Назва та опис</a></li>
          <?php } ?>
          <li><a href="#tab-main" data-toggle="tab" aria-expanded="true">Загальні дані</a></li>
        </ul>
        <div class="tab-content">
          <?php if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $lang) { ?>
            <div class="tab-pane fade <?=($_SESSION['language'] == $lang)?'active in':''?>" id="tab-<?=$lang?>">
              <?php require 'edit_tabs/tab-ntkd.php'; ?>
            </div>
          <?php } } else { $lang = 'lang'; ?>
        		<div class="tab-pane fade active in" id="tab-ntkd">
        			<?php require 'edit_tabs/tab-ntkd.php'; ?>
        		</div>
          <?php } ?>
          <div class="tab-pane fade" id="tab-main">
            <?php require_once 'edit_tabs/tab-main.php'; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?=SITE_URL?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>assets/ckfinder/ckfinder.js"></script>
<script type="text/javascript">
	<?php if($_SESSION['language']) foreach($_SESSION['all_languages'] as $lng) echo "CKEDITOR.replace( 'editor-{$lng}' ); "; else echo "CKEDITOR.replace( 'editor' ); "; ?>
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

<script type="text/javascript">
	var data;
	function save (field, e, lang) {
    $('#saveing').css("display", "block");
    var value = '';
    if(e != false) value = e.value;
    else value = data;

    $.ajax({
        url: "<?=SITE_URL?>admin/wl_ntkd/save",
        type: 'POST',
        data: {
        	alias: '<?=$_SESSION['alias']->id?>',
        	content: '<?=$question->id?>',
          field: field,
          data: value,
          language: lang,
          additional_table : '<?=$this->question_model->table()?>',
          additional_table_id : '<?=$question->id?>',
          additional_fields : 'author_edit=>user,date_edit=>time',
          json: true
        },
        success: function(res){
          if(res['result'] == false){
              $.gritter.add({title:"Помилка!",text:res['error']});
          } else {
          	language = '';
          	if(lang) language = lang;
          	$.gritter.add({title:field+' '+language, text:"Дані успішно збережено!"});
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
	function saveText(lang){
		if(lang != false){
			data = CKEDITOR.instances['editor-'+lang].getData();
		} else {
			data = CKEDITOR.instances['editor'].getData();
		}
		save('text', false, lang);
	}
  function showUninstalForm () {
    if($('#uninstall-form').is(":hidden")){
      $('#uninstall-form').slideDown("slow");
    } else {
      $('#uninstall-form').slideUp("fast");
    }
  }
</script>

<style type="text/css">
	input[type="radio"]{
		min-width: 15px;
		height: 15px;
		margin-left: 15px;
		margin-right: 5px;
	}
	img.f-left {
		margin-right: 10px;
		height: 80px;
	}
</style>