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
          <p><label><input type="checkbox" name="content" value="1" id="content" onChange="setContentUninstall(this)"> Видалити всі <?=$_SESSION['admin_options']['word:articles']?> і підгрупи, що пов'язані з даною групою</label></p>
          <input type="hidden" name="id" value="<?=$group->id?>">
          <input type="submit" value="Видалити" class="btn btn-danger">
          <button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
        </form>
      </div>

      <?php
        $AFTER_TAB_NAME = array('main' => 'Загальні дані');
        $AFTER_TAB_PATH = array('main' => APP_PATH.'services'.DIRSEP.$_SESSION['service']->name.DIRSEP.'views/admin/groups/__tab-main.php');
        $PHOTO_FILE_NAME = $group->alias;
        $ADDITIONAL_TABLE = $this->groups_model->table();
        $ADDITIONAL_TABLE_ID = $group->id;
        $ADDITIONAL_FIELDS = 'author_edit=>user,date_edit=>time';
        require APP_PATH.'views/admin/__edit_page.php';
        ?>

      </div>
    </div>
  </div>
</div>

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