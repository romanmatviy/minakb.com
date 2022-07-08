<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <?php
            $url = $this->data->url();
            array_shift($url);
            $url = implode('/', $url);
            if(isset($_SESSION['admin_options']['word:article_to'])) {
          ?>
            <a href="<?=SITE_URL.$url?>" class="btn btn-info btn-xs"><?=$_SESSION['admin_options']['word:article_to']?></a>
          <?php }
            $url = $this->data->url();
            array_shift($url);
            array_pop ($url);
            $url = implode('/', $url);
          ?>
          <a href="<?=SITE_URL.'admin/'.$url?>" class="btn btn-success btn-xs">До каталогу</a>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити <?=$_SESSION['admin_options']['word:article_to_delete']?></button>
        </div>

          <h5 class="panel-title">
            Додано: <?=date('d.m.Y H:i', $article->date_add)?>
            Редаговано: <?=date('d.m.Y H:i', $article->date_edit)?>
          </h5>
      </div>

      <div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
        <i class="fa fa-trash fa-2x pull-left"></i>
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
          <p>Ви впевнені що бажаєте видалити <?=$_SESSION['admin_options']['word:article_to_delete']?>?</p>
          <input type="hidden" name="id" value="<?=$article->id?>">
          <input type="submit" value="Видалити" class="btn btn-danger">
          <button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
        </form>
      </div>

      <?php
        $AFTER_TAB_NAME = array('main' => 'Загальні дані');
        $AFTER_TAB_PATH = array('main' => APP_PATH.'services'.DIRSEP.$_SESSION['service']->name.DIRSEP.'views/admin/articles/__tab-main.php');
        $PHOTO_FILE_NAME = $article->alias;
        $ADDITIONAL_TABLE = $this->library_model->table('_articles');
        $ADDITIONAL_TABLE_ID = $article->id;
        $ADDITIONAL_FIELDS = 'author_edit=>user,date_edit=>time';
        require APP_PATH.'views/admin/__edit_page.php';
        ?>

    </div>
  </div>
</div>