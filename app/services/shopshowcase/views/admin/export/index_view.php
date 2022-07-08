<div class="row">
    <div class="col-md-6">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?= SITE_URL ?>admin/wl_aliases/<?= $_SESSION['alias']->alias ?>" class="btn btn-xs btn-info"><i class="fa fa-cogs"></i> Налаштування</a>
                </div>
                <h4 class="panel-title">Експорт товарів у форматі <span class="label label-warning">xlsx</span> <span class="label label-warning">xls</span> <span class="label label-warning">csv</span></h4>
            </div>
            <div class="panel-body">
                <form action="<?= SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/export_xlsx' ?>" method="POST" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Групи</label>
                        <div class="col-md-9">
                            <p><strong>Якщо не обрано жодної групи => вигрузка всіх товарів</strong></p>
                            <input type="hidden" name="product_groups" id="selected" value="" />
                            <?php if (!empty($groups)) {
                                $_SESSION['alias']->js_load[] = 'assets/jstree/jstree.min.js';
                                $_SESSION['alias']->js_load[] = 'js/' . $_SESSION['alias']->alias . '/init-jstree.js';
                                echo '<link rel="stylesheet" href="' . SITE_URL . 'assets/jstree/themes/default/style.min.css" />';
                                $product_groups = array();
                                if ($group = $this->data->get('group'))
                                    $product_groups = explode(',', $group);
                                require_once APP_PATH . 'services/' . $_SESSION['alias']->service . '/views/admin/products/_groupsTree.php';
                            } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Стан</label>
                        <div class="col-md-9">
                            <label><input type="radio" name="active" value="-1" required /> всі</label>
                            <label><input type="radio" name="active" value="1" checked /> тільки активні</label>
                            <!-- <label><input type="radio" name="active" value="0" /> тільки не активні</label> -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Поля вигрузки</label>
                        <div class="col-md-9">
                            <label><input type="checkbox" name="fields[]" value="id" checked /> Внутрішній id</label><br>
                            <label><input type="checkbox" name="fields[]" value="article_show" checked /> Артикул</label><br>
                            <label><input type="checkbox" name="fields[]" value="name" checked /> Назва</label><br>
                            <label><input type="checkbox" name="fields[]" value="price" checked /> Ціна</label><br>
                            <label><input type="checkbox" name="fields[]" value="availability" checked /> Наявність</label><br>
                            <label><input type="checkbox" name="fields[]" value="active" /> Стан</label><br>
                            <label><input type="checkbox" name="fields[]" value="text" /> Опис</label><br>
                            <label><input type="checkbox" name="fields[]" value="link" checked /> Посилання</label><br>
                            <label><input type="checkbox" name="fields[]" value="photo" /> Основне зображення (посилання)</label><br>

                            <?php if (!empty($options)) foreach ($options as $option) if ($option->main)
                            { ?>
                                <label><input type="checkbox" name="fields[]" value="<?= $option->alias ?>" checked /> <?= $option->name ?></label><br>
                            <?php }
                            if (!empty($groups)) { ?>
                                <label><input type="checkbox" name="fields[]" value="group" /> id групи</label><br>
                                <label><input type="checkbox" name="fields[]" value="group_name" /> Назва групи</label><br>
                            <?php } ?>
                            <label><input type="checkbox" name="fields[]" value="date_add" /> Дата додано (dd.mm.yyyy hh:ii)</label><br>
                            <label><input type="checkbox" name="fields[]" value="date_edit" /> Дата останнього редагування (dd.mm.yyyy hh:ii)</label><br>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Тип вигрузки (документу)</label>
                        <div class="col-md-9">
                            <label><input type="radio" name="file" value="csv" required /> csv</label>
                            <label><input type="radio" name="file" value="xls" /> xls</label>
                            <label><input type="radio" name="file" value="xlsx" checked /> xlsx</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-3"></div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-download" aria-hidden="true"></i> Експорт</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if (!empty($_SESSION['option']->export_ok)) { ?>
        <div class="col-md-6">
            <?php require "xml_view.php"; ?>
        </div>
    <?php } ?>
    <div class="col-md-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h4 class="panel-title">Імпорт цін</h4>
            </div>
            <div class="panel-body">
                <form action="<?= SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/import/prepare' ?>" method="POST" enctype="multipart/form-data" class="form-horizontal">
                    <div class="note note-warning">
                        <h4><b>Увага!</b> Імпорт працює як <strong>оновлення цін</strong> для існуючих товарів</h4>
                        <p>При оновленні файл має містити не менше 2 колонок: <strong>id i price</strong>. Інші колонки при оновленні ігноруються</p>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Файл <span class="label label-warning">xlsx</span></label>
                        <div class="col-md-9">
                            <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="file" required class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-3"></div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-upload" aria-hidden="true"></i> Аналізувати</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>