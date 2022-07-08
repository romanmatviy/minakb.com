<?php $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-success">
            <div class="panel-heading">
        		<div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/wl_aliases" class="btn btn-info btn-xs"><i class="fa fa-bank"></i> До всіх адрес</a>
                </div>
                <h4 class="panel-title">Редагувати загальні налаштування сайту</h4>
            </div>
            <div class="panel-body">
	            <form action="<?=SITE_URL?>admin/wl_aliases/save_all" method="POST" class="form-horizontal">
					<?php if(isset($options)) { 
                        $skip = ['global_MetaTags'];
                        $bools = array('sitemap_active', 'sitemap_autosent', 'showTimeSiteGenerate', 'userSignUp', 'showInAdminWl_comments', 'statictic_set_page', 'sendEmailForce', 'sendEmailSaveHistory');
                        $dates = array('sitemap_lastgenerate', 'sitemap_lastsent', 'sitemap_lastedit');
                        $titles = array( 'sitemap_active' => 'Автоматично оновлювати SiteMap при зміні контенту на сайті',
                            'sitemap_autosent' => 'Автоматично відправляти SiteMap пошуковим роботам',
                            'sitemap_lastgenerate' => 'Остання генерація SiteMap на сайті',
                            'sitemap_lastsent' => 'Остання відправка SiteMap пошуковим роботам',
                            'sitemap_lastedit' => 'Остання зміна інформації на сайті',
                            'sendEmailForce' => 'Відправляти листи моментально <br><small>(не рекомендується, краще за розкладом)</small>',
                            'sendEmailSaveHistory' => 'Відправлений лист зберегти в архів',
                            'paginator_per_page' => 'Матеріалів на сторінці (per page)',
                            'showTimeSiteGenerate' => 'Виводити час генерації сторінки',
                            'statictic_set_page' => 'Зберігати внутрішню статистику',
                            'userSignUp' => 'Вільна реєстрація користувачів',
                            'new_user_type' => 'Тип (категорія) новозареєстрованого користувача',
                            'showInAdminWl_comments' => 'Виводити в панелі керування вігуки');
						foreach ($options as $option) {
                            if(in_array($option->name, $skip) || $option->name == 'new_user_type' && empty($_SESSION['option']->userSignUp))
                                continue; ?>
							<div class="form-group">
		                        <label class="col-md-5 control-label"><?=(isset($titles[$option->name])) ? $titles[$option->name] : $option->name?></label>
		                        <div class="col-md-7">
                                    <?php if(in_array($option->name, $dates)) {
                                        echo ($option->value > 0) ? date('d.m.Y H:i', $option->value) : 'Дані відсутні';
                                    } elseif(in_array($option->name, $bools)) { ?>
                                        <input name="option-<?=$option->id?>-<?=$option->name?>" type="checkbox" data-render="switchery" <?=($option->value == 1) ? 'checked' : ''?> value="1" />
                                    <?php } elseif($option->name == 'new_user_type') {
                                        $selected = $option->value ?? 4;
                                        if($userTypes = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active')) {
                                     ?>
                                        <select name="option-<?=$option->id?>" class="form-control">
                                            <?php foreach ($userTypes as $type) {
                                                echo "<option value='{$type->id}'";
                                                if($type->id == $selected)
                                                    echo " selected";
                                                echo ">{$type->title}</option>";
                                            } ?>
                                        </select>
                                    <?php } } else { ?>
		                            <input type="text" class="form-control" name="option-<?=$option->id?>" value="<?=$option->value?>" placeholder="<?=$option->name?>">
                                    <?php } ?>
		                        </div>
		                    </div>
		                <?php } ?>
		                <div class="form-group">
	                        <label class="col-md-5 control-label"></label>
	                        <div class="col-md-7">
	                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Зберегти</button>
	                        </div>
	                    </div>
                    <?php } else { ?>
                    	<div class="note note-info">
							<h4>Увага! Загальні налаштування сайту відсутні</h4>
							<p>
							    За допомогою форми праворуч додайте налаштування.
		                    </p>
						</div>
                    <?php } ?>
                </form>
                <p>Загальні налаштування мають найнижчий пріорітет (можуть переоголошуватися для alias), проте доступні для цілого сайту.</p>
            </div>
        </div>
    </div>
	<div class="col-md-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h4 class="panel-title">Додати загальне налаштування сайту</h4>
            </div>
            <div class="panel-body">
	            <form action="<?=SITE_URL?>admin/wl_aliases/add_all" method="POST" class="form-horizontal">
					<div class="form-group">
                        <label class="col-md-3 control-label">Назва властивості</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name" placeholder="Назва (анг), без пробілів" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Дані властивості</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="value" placeholder="Значення" required>
                        </div>
                    </div>
	                <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-plus"></i> Додати</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>