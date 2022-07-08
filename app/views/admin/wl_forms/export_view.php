<div class="row">
    <div class="col-md-6">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?= SITE_URL ?>admin/wl_forms/info/<?= $form->name ?>" class="btn btn-xs btn-info"><i class="fa fa-list-ul"></i> До заявок</a>
                </div>
                <h4 class="panel-title">Експорт заявок у форматі <span class="label label-warning">xlsx</span> <span class="label label-warning">xls</span> <span class="label label-warning">csv</span></h4>
            </div>
            <div class="panel-body">
                <form action="<?= SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/export_xlsx' ?>" method="POST" class="form-horizontal">
                    <input type="hidden" name="form_id" value="<?=$form->id?>">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Період</label>
                        <div class="col-md-9">
                            <div class="row">
                                <label><input type="radio" name="all" value="1" checked /> всі</label>
                                <label><input type="radio" name="all" value="0" /> певний період</label>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" name="date_from" class="form-control" min="<?=date('Y-m-d', $min->date_add)?>" max="<?=date('Y-m-d')?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="date_to" class="form-control" min="<?=date('Y-m-d', $min->date_add)?>" max="<?=date('Y-m-d')?>" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Поля вигрузки</label>
                        <div class="col-md-9">
                            <label><input type="checkbox" name="fields[]" value="id" checked /> Внутрішній id</label><br>
                            <?php if (!empty($formInfo)) foreach ($formInfo as $field) { ?>
                                <label><input type="checkbox" name="fields[]" value="<?= $field->name ?>" checked /> <?= $field->title ?></label><br>
                            <?php } ?>
                            <label><input type="checkbox" name="fields[]" value="date_add" /> Дата додано (dd.mm.yyyy hh:ii)</label><br>
                            <?php if($_SESSION['language']) { ?>
                                <label><input type="checkbox" name="fields[]" value="language" /> Мова сайту</label><br>
                            <?php } ?>
                            
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Сортування</label>
                        <div class="col-md-9">
                            <label><input type="radio" name="order" value="asc" checked /> нові знизу</label>
                            <label><input type="radio" name="order" value="desc" /> нові згори</label>
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
</div>

<?php $this->load->js_init('init__export()'); ?>
<script type="text/javascript">
function init__export() {
    $('input[name="all"]').change(function(){
        if($(this).val() == 1)
            $('input[type="date"]').attr("disabled", true);
        else
            $('input[type="date"]').attr("disabled", false);
    });
    $('input[name="date_from"]').change(function(){
        $('input[name="date_to"]').attr("min", $(this).val());
    });
    $('input[name="date_to"]').change(function(){
        $('input[name="date_from"]').attr("max", $(this).val());
    });
}
</script>