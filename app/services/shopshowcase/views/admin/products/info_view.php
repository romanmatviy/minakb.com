<?php $changePriceTab = false;
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

$storages = $marketing = array();
if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1'))
    foreach ($cooperation as $c) {
        if($c->type == 'storage') $storages[] = $c->alias2;
        if($c->type == 'marketing') $marketing[] = $this->load->function_in_alias($c->alias2, '__tab_product', $product, true);
    }
?>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-inverse">
      <div class="panel-heading">
        <div class="panel-heading-btn">
          <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/clear_cache?id='.$product->id?>" class="btn btn-warning btn-xs"><i class="fa fa-trash" aria-hidden="true"></i> Очистити КЕШ</a>
          <button onClick="showUninstalForm()" class="btn btn-danger btn-xs"><i class="fa fa-trash-o"></i> Видалити товар</button>
        </div>

          <h5 class="panel-title">
            Додав: <?=$product->author_add_name .' '.date('d.m.Y H:i', $product->date_add)?>.
            Редаговано: <?=$product->author_edit_name .' '.date('d.m.Y H:i', $product->date_edit)?>
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

      <?php if(isset($_SESSION['notify'])) {
        require APP_PATH.'views/admin/notify_view.php';
      } ?>

      <div class="panel-body">
        <div class="m-b-5">
          <?php $url = $this->data->url();
              array_shift($url);
              array_pop ($url);
              $url = implode('/', $url);
            if(!$_SESSION['option']->ProductMultiGroup) { ?>
            <a href="<?=SITE_URL.'admin/'.$url?>" class="btn btn-success btn-sm"><i class="fa fa-undo"></i> До каталогу</a>
          <?php } ?>
          <a href="<?=SITE_URL.$product->link?>" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Дивитися як клієнт</a>
          <a href="<?=SITE_URL.'admin/'.$product->link?>?edit" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Редагувати товар</a>
        </div>

        <ul class="nav nav-tabs">
          <li class="active"><a href="#tab-main" data-toggle="tab" aria-expanded="true">Загальні дані</a></li>
          <?php if($changePriceTab) { ?>
            <li><a href="#tab-changePrice" data-toggle="tab" aria-expanded="true">Керування ціною</a></li>
          <?php } ?>
          <li><a href="#tab-photo-video-audio-files" data-toggle="tab" aria-expanded="true">Фото Відео Аудіо Файли</a></li>
          <li><a href="#tab-statistic" data-toggle="tab" aria-expanded="true">Статистика</a></li>
          <li><a href="#tab-similar" data-toggle="tab" aria-expanded="true">Подібні</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="tab-main">
            <?php require_once 'edit_tabs/tab-info-main.php'; ?>
          </div>
          <?php if($changePriceTab) { ?>
            <div class="tab-pane fade" id="tab-changePrice">
              <?php require 'edit_tabs/tab-changePrice.php'; ?>
            </div>
          <?php } ?>
          <div class="tab-pane fade" id="tab-photo-video-audio-files">
            <?php require_once 'edit_tabs/tab-photo-video-audio.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-statistic">
            <?php require_once APP_PATH.'views/admin/wl_statistic/__statistic.php'; ?>
          </div>
          <div class="tab-pane fade" id="tab-similar">
            <?php require 'edit_tabs/tab-similar.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>