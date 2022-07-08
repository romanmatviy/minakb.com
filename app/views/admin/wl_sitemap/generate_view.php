<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
	        <div class="panel-heading">
	        	<div class="panel-heading-btn">
	        		<a href="<?=SITE_URL?>admin/wl_sitemap" class="btn btn-success btn-xs"><i class="fa fa-refresh"></i> До всіх записів</a>
	        	</div>
	            <h4 class="panel-title">Загальні налаштування SiteMap</h4>
	        </div>
	        <div class="panel-body panel-form">
	            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/save_generate" method="POST">
	            	<?php if (file_exists('sitemap.xml')) { ?>
		            	<div class="form-group">
		            		<p class="text-center m-t-10">
			            		<a href="<?=SITE_URL?>sitemap.xml" class="btn btn-info btn-xs" target="_blank">Відкрити sitemap.xml</a>
			            	</p>
		            	</div>
	            	<?php } ?>
	                <div class="form-group">
	                	<label class="col-md-9 control-label">Генератор карти сайту активний<br>
							<small>Автоматично оновлювати SiteMap при зміні контенту на сайті</small>
	                	</label>
	                    <div class="col-md-3">
                    		<input type="checkbox" data-render="switchery" <?=($_SESSION['option']->sitemap_active)?'checked':''?> value="1" name="sitemap_active" />
						</div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-9 control-label">Автоматично відправляти SiteMap пошуковим роботам<br>
							<small>google.com, yahoo.com, ask.com, bing.com не частіше 1 раза за добу <br>та не менше 2 год від останньої зміни інформації на сайті</small>
	                	</label>
	                    <div class="col-md-3">
                    		<input type="checkbox" data-render="switchery" <?=($_SESSION['option']->sitemap_autosent)?'checked':''?> value="1" name="sitemap_autosent" />
						</div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-6 control-label">Остання зміна інформації на сайті</label>
	                    <div class="col-md-6">
                    		<?= ($_SESSION['option']->sitemap_lastedit > 0) ? date('d.m.Y H:i', $_SESSION['option']->sitemap_lastedit) : 'Дані відсутні' ?>
						</div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-6 control-label">Остання генерація</label>
	                    <div class="col-md-6">
                    		<?= ($_SESSION['option']->sitemap_lastgenerate > 0) ? date('d.m.Y H:i', $_SESSION['option']->sitemap_lastgenerate) : 'Дані відсутні' ?>
						</div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-6 control-label">Відправлено пошуковим роботам</label>
	                    <div class="col-md-6">
                    		<?= ($_SESSION['option']->sitemap_lastsent > 0) ? date('d.m.Y H:i', $_SESSION['option']->sitemap_lastsent) : 'Дані відсутні' ?>
						</div>
	                </div>
	                <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success">Зберегти</button>
                        </div>
                    </div>
	            </form>
	        </div>
	    </div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-inverse panel-warning">
	        <div class="panel-heading">
	            <h4 class="panel-title">Заново створити карту сайту всіх посилань</h4>
	        </div>
	        <div class="panel-body panel-form">
	            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/re_generate" method="POST">
	            	<div class="form-group col-md-12">
		            	<h4>Розділи, посилання яких включити до карти сайту</h4>
		            	<p>Існуючу карту сайту (базу посилань) буде очищено, за винятком 301 редіректів. На основі активних розділів буде заново зібрано всі посилання. В процесі роботи сайту, сторінки на які відбувалися заходи та не увійшли до карти сайту, будуть додані автоматично.</p>
		            </div>
		            <div class="form-group">
	                	<label class="col-md-9 control-label">
	                		Видалити 404 сторінки
                		</label>
	                    <div class="col-md-3">
                    		<input type="checkbox" data-render="switchery" value="404" name="deletePageCode" checked />
						</div>
	                </div>
		            <?php $init_services = array();
		            $this->db->select('wl_aliases as a', 'id, alias, service');
		            $this->db->join('wl_services as s', 'name as service_name', '#a.service');
		            $where = array('alias' => '#a.id', 'content' => 0);
		            if($_SESSION['language'])
		            	$where['language'] = $_SESSION['language'];
		            $this->db->join('wl_ntkd', 'name', $where);
		            if($aliases = $this->db->get('array'))
		            	foreach ($aliases as $alias) { 
		            		$page = $alias->alias;
		            		if($alias->service)
		            		{
		            			$page = $alias->service_name;
		            			if(!isset($init_services[$page]))
		            			{
		            				$init_services[$page] = false;
		            				$path = APP_PATH.'services'.DIRSEP.$alias->service_name.DIRSEP.$alias->service_name.'.php';
		            				if(file_exists($path))
		            				{
		            					require_once $path;
										if(is_callable(array($page, '__get_SiteMap_Links')))
											$init_services[$page] = true;
		            				}
		            			}
		            			if(!$init_services[$page])
	            					continue;
		            		}
		            		else
		            		{
		            			$init = false;
		            			$page = $alias->alias;
		            			$path = APP_PATH.'controllers'.DIRSEP.$page.'.php';
	            				if(file_exists($path))
	            				{
	            					require_once $path;
									if(is_callable(array($page, '__get_SiteMap_Links')))
										$init = true;
	            				}
	            				if(!$init)
	            					continue;
		            		}
		            		?>
	                <div class="form-group">
	                	<label class="col-md-9 control-label">
	                		<?=(empty($alias->name)) ? $alias->alias : $alias->name?>
                			<br>
                			<?php $a = ($alias->alias != 'main') ? SITE_URL.$alias->alias : SITE_URL;
                			$link = ($alias->alias != 'main') ? SITE_URL.$alias->alias.'/*' : SITE_URL;
                			echo("<a href='{$a}'><small>{$link}</small></a>");
                			?>
                		</label>
	                    <div class="col-md-3">
                    		<input type="checkbox" data-render="switchery" value="<?=$alias->id?>" name="aliases[]" checked />
						</div>
	                </div>
	                <?php } $Cache = rand(0, 999); ?>
	                <input type="hidden" name="code_hidden" value="<?=$Cache?>">
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Код перевірки <strong><?=$Cache?></strong></label>
                        <div class="col-md-9">
		                	<input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
						</div>
	                </div>
	                <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success">Генерувати</button>
                        </div>
                    </div>
	            </form>
	        </div>
	    </div>
        <div class="panel panel-inverse panel-warning">
	        <div class="panel-heading">
	            <h4 class="panel-title">Ручне генерування SiteMap.xml</h4>
	        </div>
	        <div class="panel-body panel-form">
	            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/start_generate" method="POST">
	                <div class="form-group">
	                	<label class="col-md-9 control-label">Відправити згенерований sitemap.xml пошуковим роботам</label>
	                    <div class="col-md-3">
                    		<input type="checkbox" data-render="switchery" value="1" name="sent" />
						</div>
	                </div>
	                <?php $Cache = rand(0, 999); ?>
	                <input type="hidden" name="code_hidden" value="<?=$Cache?>">
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Код перевірки <strong><?=$Cache?></strong></label>
                        <div class="col-md-9">
		                	<input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
						</div>
	                </div>
	                <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success">Генерувати</button>
                        </div>
                    </div>
	            </form>
	        </div>
	    </div>
	</div>
</div>

<?php
$_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js';
?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />