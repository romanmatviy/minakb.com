<div class="row">
	<div class="col-md-4 ui-sortable">
        <div class="panel panel-inverse" data-sortable-id="form-plugins-1">
	        <div class="panel-heading">
	        	<div class="panel-heading-btn">
	        		<a href="<?=SITE_URL?>admin/wl_sitemap" class="btn btn-success btn-xs"><i class="fa fa-refresh"></i> До всіх записів</a>
	        	</div>
	            <h4 class="panel-title">Редагувати</h4>
	        </div>
	        <div class="panel-body panel-form">
	            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/save" method="POST">
	            	<input type="hidden" name="id" value="<?=$sitemap->id?>">
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Адреса</label>
	                    <div class="col-md-9">
	                    	<a href="<?=SITE_URL?><?=($sitemap->link == 'main') ? '' : $sitemap->link?>"><strong><?=SITE_URL?><?=($sitemap->link == 'main') ? '' : $sitemap->link?></strong></a>
	                    </div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Адресу розпізнано</label>
	                    <div class="col-md-9">
	                    	<strong><i class="fa fa-<?=($sitemap->alias > 0)?'check':'times'?>"></i> <?=$sitemap->name?></strong>
	                    </div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Остання зміна</label>
	                    <div class="col-md-9">
	                    	<strong><?=($sitemap->time) ? date('d.m.Y H:i', $sitemap->time) : 'Не індексовано'?></strong>
	                    </div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Код відповіді</label>
                        <div class="col-md-9">
		                	<select name="code" class="form-control" onchange="setCode(this)">
		                		<option value="200" <?=($sitemap->code == 200)?'selected':''?>>200 Cache активний</option>
		                		<option value="201" <?=($sitemap->code == 201)?'selected':''?>>200 Cache НЕ активний</option>
		                		<option value="301" <?=($sitemap->code == 301)?'selected':''?>>301 Переадресація</option>
		                		<option value="404" <?=($sitemap->code == 404)?'selected':''?>>404 Адреса недоступна</option>
		                	</select>
						</div>
	                </div>
	                <div class="form-group redirect" <?=($sitemap->code == 301) ? '' : 'style="display:none"'?>>
	                	<label class="col-md-3 control-label">Направити до</label>
                        <div class="col-md-9">
                    		<span class="input-group-addon"><?=SITE_URL?></span>
	                		<input name="redirect" class="form-control" value="<?=($sitemap->code == 301) ? $sitemap->data : ''?>">
						</div>
	                </div>
	                <div class="form-group code activeDiv" <?=($sitemap->code > 300) ? 'style="display:none"' : ''?>>
	                	<label class="col-md-3 control-label">Сторінка включена до індексації:</label>
                        <div class="col-md-9">
		                	<input type="checkbox" data-render="switchery" <?=($sitemap->priority >= 0) ? 'checked' : ''?> value="1" id="active" name="active" onChange="setActive(this)" />
						</div>
	                </div>
	                <div class="form-group SiteMap code" <?=($sitemap->priority < 0 || $sitemap->code > 300) ? 'style="display:none"' : ''?>>
	                	<label class="col-md-3 control-label">Частота оновлення</label>
                        <div class="col-md-9">
		                	<select name="changefreq" class="form-control">
		                		<?php $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
									foreach ($changefreq as $freq) {
										echo('<option value="'.$freq.'"');
										if($sitemap->changefreq == $freq) echo(' selected');
										echo(">$freq</option>");
									}
									?>
		                	</select>
						</div>
	                </div>
	                <div class="form-group SiteMap code" <?=($sitemap->priority < 0 || $sitemap->code > 300) ? 'style="display:none"' : ''?>>
	                	<label class="col-md-3 control-label">Пріорітетність</label>
                        <div class="col-md-9">
		                	<input type="number" name="priority" value="<?=$sitemap->priority/10?>" placeholder="0.5" min="<?=($sitemap->priority < 0 || $sitemap->code > 300) ? '-1' : '0'?>" max="1" step="0.1" class="form-control priority">
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
	    <?php if($sitemap->code == 200 && !empty($sitemap->data)) { ?>
		    <div class="panel panel-inverse" data-sortable-id="form-plugins-1">
		        <div class="panel-heading">
		            <h4 class="panel-title">Керування Cache</h4>
		        </div>
		        <div class="panel-body panel-form">
		        	<?php $Cache = rand(0, 999); ?>
		            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/cleanCache" method="POST">
		            	<input type="hidden" name="id" value="<?=$sitemap->id?>">
		            	<input type="hidden" name="code_hidden" value="<?=$Cache?>">
						<div class="form-group">
		                    <label class="col-md-3 control-label"></label>
		                    <div class="col-md-9">
		                        <a href="<?=SITE_URL?>admin/wl_sitemap/cache/<?=$sitemap->id?>" class="btn btn-sm btn-warning">Дивитися Cache</a>
		                    </div>
		                </div>
		                <div class="form-group">
		                	<label class="col-md-3 control-label">Код перевірки <strong><?=$Cache?></strong></label>
	                        <div class="col-md-9">
			                	<input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
							</div>
		                </div>
		                <div class="form-group">
	                        <label class="col-md-3 control-label"></label>
	                        <div class="col-md-9">
	                            <button type="submit" class="btn btn-sm btn-danger">Очистити Cache</button>
	                        </div>
	                    </div>
		            </form>
		        </div>
		    </div>
	    <?php } ?>
	    <div class="panel panel-inverse" data-sortable-id="form-plugins-1">
	        <div class="panel-heading">
	            <h4 class="panel-title">Видалити <strong><?=SITE_URL?><?=($sitemap->link == 'main') ? '' : $sitemap->link?></strong> </h4>
	        </div>
	        <div class="panel-body panel-form">
	        	<?php $Cache = rand(0, 999); ?>
	            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/delete" method="POST">
	            	<input type="hidden" name="id" value="<?=$sitemap->id?>">
	            	<input type="hidden" name="code_hidden" value="<?=$Cache?>">
	                <div class="form-group">
	                	<label class="col-md-5 control-label">Код перевірки <strong><?=$Cache?></strong></label>
                        <div class="col-md-7">
		                	<input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
						</div>
	                </div>
	                <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-sm btn-danger">Видалити запис з каталогу SiteMap</button>
                        </div>
                    </div>
	            </form>
	        </div>
	    </div>
    </div>
	<?php if($wl_statistic) {
		$totalUsers = $viewsCount = $min = $max = 0;
		foreach ($wl_statistic as $statistic) {
			$totalUsers += $statistic->unique;
			$viewsCount += $statistic->views;
			if($statistic->day < $min || $min == 0) $min = $statistic->day;
			if($statistic->day > $max) $max = $statistic->day;
		}
	?>
	<div class="col-md-8">
		<div class="row">
			<div class="widget-chart with-sidebar bg-black">
			    <div class="widget-chart-content">
			        <h4 class="chart-title">
			            Аналітика відвідувань
			            <small>Статистика подобово по конкретній адресі. Дані менеджерів не враховуються.</small>
			        </h4>
			        <div id="visitors-line-chart" class="morris-inverse" style="height: 260px;"></div>
			    </div>
			    <div class="widget-chart-sidebar bg-black-darker">
			    	<h4 class="chart-title">
			            <?=date('d.m.Y', $min).' - '.date('d.m.Y', $max)?>
			            <small>Підраховано дані за період</small>
			        </h4>
			        <div class="chart-number">
			            <?= $totalUsers?>
			            <small>Відвідувачів</small>
			            <?= $viewsCount?>
			            <small>Переглядів</small>
			            <?= round($viewsCount/$totalUsers, 2)?>
			            <small>Переглядів/відвідувача</small>
			        </div>
			    </div>
			</div>
		</div>

		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

		<script>
		var getMonthName = function(e) {
		    var t = [];
		    t[0] = "Січень";
		    t[1] = "Лютий";
		    t[2] = "Березень";
		    t[3] = "Квітень";
		    t[4] = "Травень";
		    t[5] = "Червень";
		    t[6] = "Липень";
		    t[7] = "Серпень";
		    t[8] = "Вересень";
		    t[9] = "Жовтень";
		    t[10] = "Листопад";
		    t[11] = "Грудень";
		    return t[e];
		};

		var e = "#0D888B",
			t = "#00ACAC",
			n = "#3273B1",
			r = "#348FE2",
			i = "rgba(0,0,0,0.6)",
			z = "#eee",
			s = "rgba(255,255,255,0.4)";

		Morris.Line({
		    element: "visitors-line-chart",
		    data: [
		    <?php foreach($wl_statistic as $data) {?>
		    	{
			        x: "<?= date('Y-m-d', $data->day)?>",
			        y: <?= $data->views?>,
			        z: <?= $data->unique?>
			    },
		    <?php } ?>
		    ],
		    xkey: "x",
		    ykeys: ["y", "z"],
		    labels: ["Переглядів", "Відвідувачів"],
		    lineColors: [z, e, n],
		    pointFillColors: [z, t, r],
		    xLabels:'week',
		    lineWidth: "2px",
		    pointStrokeColors: [i, i],
		    resize: true,
		    gridTextFamily: "Open Sans",
		    gridTextWeight: "normal",
		    gridTextSize: "11px",
		    gridLineColor: "rgba(0,0,0,0.5)",
		    hideHover: "auto"
		});
		</script>

		<style>
		.morris-inverse .morris-hover {
		    background: rgba(0,0,0,.4) !important;
		    border: none !important;
		    padding: 8px !important;
		    color: #ccc !important;
		}
		</style>
		<div class="row">
			<div class="panel panel-inverse" data-sortable-id="form-plugins-2">
				<div class="panel-body">
					<table class="table table-striped table-responsive table-hover">
			            <thead>
			                <tr>
			                    <th>День</th>
			                    <th>Унікальні відвідувачі</th>
			                    <th>Загальні перегляди</th>
			                </tr>
			            </thead>
			            <tbody>
			            	<?php if($wl_statistic) {
			            		foreach ($wl_statistic as $statistic) { ?>
				                <tr>
				                    <td><?=date('d.m.Y', $statistic->day)?></td>
				                    <td><?=$statistic->unique?></td>
				                    <td><?=$statistic->views?></td>
				                </tr>
			                <?php if($sitemap->code > 299)
				                	{
				                		$where = array();
				                		$where['sitemap'] = $sitemap->id;
				                		$where['date'] = '>='.$statistic->day;
				                		$statistic->day += 3600*24;
				                		$where['+date'] = '<'.$statistic->day;
				                		if($wl_sitemap_from = $this->db->getAllDataByFieldInArray('wl_sitemap_from', $where))
				                		{
				                			foreach ($wl_sitemap_from as $from) { ?>
				                				<tr>
								                    <td><?=date('d.m.Y H:i', $from->date)?></td>
								                    <td colspan="2"><?=$from->from?></td>
								                </tr>
				                			<?php }
				                		}
				                	}
			                 	}
			                } else { ?>
				                <tr>
				                	<td colspan="3">Інформація відсутня.</td>
				                </tr>
			                <?php } ?>
			            </tbody>
			        </table>
			        <?php
		                $this->load->library('paginator');
		                echo $this->paginator->get();
		            ?>
		        </div>
		    </div>
		</div>
	</div>
<?php } elseif($sitemap->code > 299) {
	if($wl_sitemap_from = $this->db->getAllDataByFieldInArray('wl_sitemap_from', $sitemap->id, 'sitemap')) { ?>
	<div class="col-md-8">
		<div class="row">
			<div class="panel panel-inverse" data-sortable-id="form-plugins-2">
				<div class="panel-body">
					<table class="table table-striped table-responsive table-hover">
			            <thead>
			                <tr>
			                    <th>День та година</th>
			                    <th>Адреса ініціалізації</th>
			                </tr>
			            </thead>
			            <tbody>
						<?php foreach ($wl_sitemap_from as $from) { ?>
							<tr>
				                <td><?=date('d.m.Y H:i', $from->date)?></td>
				                <td><?=$from->from?></td>
				            </tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php }
}
$_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js';
?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />

<script>
	var activeIndex = <?=($sitemap->priority < 0 || $sitemap->code > 300) ? 0 : 1?>;
	function setActive() {
		if(activeIndex == 0)
		{
			$('.SiteMap').slideDown("slow");
			var priority = $('.priority').val();
			if(priority < 0)
			{
				$('.priority').val(priority * -1);
				$('.priority').attr('min', 0);
			}
			activeIndex = 1;
	    } else {
			$('.SiteMap').slideUp("fast");
			var priority = $('.priority').val();
			if(priority > 0)
			{
				$('.priority').val(priority * -1);
				$('.priority').attr('min', -1);
			}
			activeIndex = 0;
	    }
	}
	function setCode(e)
	{
		if(e.value > 299)
		{
			$('.code').slideUp("fast");
			if(e.value == 301)
				$('.redirect').slideDown("slow");
			else
				$('.redirect').slideUp("slow");
		}
		else
		{
			$('.activeDiv').slideDown("slow");
			$('.redirect').slideUp("slow");
			if(activeIndex == 1)
			{
				$('.SiteMap').slideDown("slow");
				var priority = $('.priority').val();
				if(priority < 0) $('.priority').val(priority * -1);
			} else {
				$('.SiteMap').slideUp("fast");
		    }
		}
	}
</script>