<?php
	$manufacturers = $this->shop_model->getManufactures();

  $options_parents = array();
  if($_SESSION['option']->useGroups && isset($list))
  {
    $parent = $product->group;
    while ($parent != 0) {
      array_unshift($options_parents, $parent);
      $parent = $list[$parent]->parent;
    }
  }
  array_unshift($options_parents, 0);
  
  $product_options = array();
  $options = $this->db->getAllDataByFieldInArray($this->shop_model->table('_product_options'), $product->id, 'product');
  if($options)
  {
    foreach ($options as $option) {
      if($option->language != '' && in_array($option->language, $_SESSION['all_languages'])){
        $product_options[$option->option][$option->language] = $option->value;
      } else {
        $product_options[$option->option] = $option->value;
      }
    }
  }

  $storages = array();
  $cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1');
  if($cooperation)
    foreach ($cooperation as $c) {
      if($c->type == 'storage') $storages[] = $c->alias2;
    }

?>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$product->alias?>" class="btn btn-info btn-xs"><?=$_SESSION['admin_options']['word:product_to']?></a>
          <?php
            $url = $this->data->url();
            array_shift($url);
            array_pop ($url);
            $url = implode('/', $url);
          ?>
          <a href="<?=SITE_URL.'admin/'.$url?>" class="btn btn-success btn-xs">До каталогу</a>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити <?=$_SESSION['admin_options']['word:product_to_delete']?></button>
        </div>

          <h5 class="panel-title">
            Додано: <?=date('d.m.Y H:i', $product->date_add)?>
            Редаговано: <?=date('d.m.Y H:i', $product->date_edit)?>
          </h5>
      </div>

      <div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
        <i class="fa fa-trash fa-2x pull-left"></i>
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
          <p>Ви впевнені що бажаєте видалити товар?</p>
          <input type="hidden" name="id" value="<?=$product->id?>">
          <input type="submit" value="Видалити" class="btn btn-danger">
          <button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
        </form>
      </div>

      <?php if(isset($_SESSION['notify'])){ 
        require APP_PATH.'views/admin/notify_view.php';
      } ?>

      <div class="panel-body">
        <ul class="nav nav-tabs">
          <?php if(!empty($storages)) { ?>
            <li class="active"><a href="#tab-storages" data-toggle="tab" aria-expanded="true">Склад</a></li>
            <li>
          <?php } else echo '<li class="active">'; ?>
            <a href="#tab-main" data-toggle="tab" aria-expanded="true">Про товар</a></li>
          <li><a href="#tab-history" data-toggle="tab" aria-expanded="true">Історія наявності</a></li>
          <li><a href="#tab-search" data-toggle="tab" aria-expanded="true">Цікавилися</a></li>
          <li><a href="#tab-buy" data-toggle="tab" aria-expanded="true">Покупки</a></li>
        </ul>
        <div class="tab-content">
          <?php if(!empty($storages)) { ?>
            <div class="tab-pane fade active in" id="tab-storages">
              <?php require 'edit_tabs/tab-storages.php'; ?>
            </div>
            <div class="tab-pane fade" id="tab-main">
          <?php } else { ?>
            <div class="tab-pane fade active in" id="tab-main">
            <?php } require_once 'edit_tabs/tab-main.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-history">
            <?php require 'edit_tabs/tab-history.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-search">
            <?php require 'edit_tabs/tab-search.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-buy">
            <?php require 'edit_tabs/tab-buy.php'; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
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