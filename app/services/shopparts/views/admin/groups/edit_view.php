<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups'?>" class="btn btn-info btn-xs">До всіх груп</a>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити <?=$_SESSION['admin_options']['word:groups_to_delete']?></button>
        </div>

        <h5 class="panel-title">Дані групи</h5>
      </div>

      <div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
        <i class="fa fa-trash fa-2x pull-left"></i>
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete_group" method="POST">
          <p>Ви впевнені що бажаєте видалити <?=$_SESSION['admin_options']['word:groups_to_delete']?>?</p>
          <p><label><input type="checkbox" name="content" value="1" id="content" onChange="setContentUninstall(this)"> Видалити всі <?=$_SESSION['admin_options']['word:products']?> і підгрупи, що пов'язані з даною групою</label></p>
          <input type="hidden" name="id" value="<?=$group->id?>">
          <input type="submit" value="Видалити" class="btn btn-danger">
          <button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
        </form>
      </div>

      <?php if(isset($_SESSION['notify'])){ 
        require APP_PATH.'views/admin/notify_view.php';
      } ?>

      <div class="panel-body">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#tab-main" data-toggle="tab" aria-expanded="true">Загальні дані</a></li>
          <?php if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $lang) { ?>
          	<li><a href="#tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
          <?php } } else { ?>
          	<li><a href="#tab-ntkd" data-toggle="tab" aria-expanded="true">Назва та опис</a></li>
          <?php } ?>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="tab-main">
            <?php require_once 'edit_tabs/tab-main.php'; ?>
          </div>
          <?php if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $lang) { ?>
            <div class="tab-pane fade" id="tab-<?=$lang?>">
              <?php require 'edit_tabs/tab-ntkd.php'; ?>
            </div>
          <?php } } else { $lang = 'lang'; ?>
        		<div class="tab-pane fade" id="tab-ntkd">
        			<?php require 'edit_tabs/tab-ntkd.php'; ?>
        		</div>
          <?php } ?>
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
      	content: '-<?=$group->id?>',
        field: field,
        data: value,
        language: lang,
        json: true
      },
      success: function(res){
        if(res['result'] == false){
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
	function saveText(lang){
		if(lang != false){
			data = CKEDITOR.instances['editor-'+lang].getData();
		} else {
			data = CKEDITOR.instances['editor'].getData();
		}
		save('text', false, lang);
	}
	function showEditTKD (lang) {
		if($('#tkd-'+lang).is(":hidden")){
			$('#tkd-'+lang).slideDown("slow");
	    } else {
			$('#tkd-'+lang).slideUp("fast");
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
<script type="text/javascript">
	function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}
	function setContentUninstall (e){
		if(e.checked){
			if(confirm("Увага! Будуть видалені всі товари даної групи та товари груп, що пов'язані з даною категорією! Ви впевнені що хочете видалити?")){
				e.checked = true;
			} else e.checked = false;
		}
	}
</script>