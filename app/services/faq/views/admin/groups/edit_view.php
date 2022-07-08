<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups'?>" class="btn btn-info btn-xs">До всіх груп</a>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити групу</button>
        </div>

          <h5 class="panel-title">Дані групи</h5>
      </div>
      <div id="uninstall-form" style="background: rgba(236, 0, 0, 0.68); color: #000; padding: 10px; display: none;">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete_group" method="POST">
    			Ви впевнені що бажаєте видалити групу "<?=$group->name?>?
    			<br>
    			<label>
            <input type="checkbox" name="content" value="1" id="content" onChange="setContentUninstall(this)">
              Видалити всі питання, що пов'язані з даною групою
            </label>
    			<br>
    			<input type="hidden" name="id" value="<?=$group->id?>">
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
          	<li><a href="#tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
          <?php } } else { ?>
          	<li class="active"><a href="#tab-ntkd" data-toggle="tab" aria-expanded="true">Назва та опис</a></li>
          <?php } ?>
          <li><a href="#tab-main" data-toggle="tab" aria-expanded="true">Загальні дані</a></li>
        </ul>
        <div class="tab-content">
          <?php 
            if($_SESSION['language']) { 
              foreach ($_SESSION['all_languages'] as $lang) { 
                $class = '';
                if($lang == $_SESSION['language']) $class = 'active in';
          ?>
            <div class="tab-pane fade <?=$class?>" id="tab-<?=$lang?>">
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
            additional_table : '<?=$this->groups_model->table()?>',
            additional_table_id : '<?=$group->id?>',
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
	function showEditTKD (lang) {
		if($('#tkd-'+lang).is(":hidden")){
			$('#tkd-'+lang).slideDown("slow");
	    } else {
			$('#tkd-'+lang).slideUp("fast");
	    }
	}
</script>

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
			if(confirm("Увага! Будуть видалені всі питання, що пов'язані з даною групою! Ви впевнені що хочете видалити?")){
				e.checked = true;
			} else e.checked = false;
		}
	}
</script>