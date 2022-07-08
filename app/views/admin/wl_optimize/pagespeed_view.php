<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Завантажити оптимізовані файли за PageSpeed Insights (optimized_contents.zip)</h4>
            </div>
            <div class="panel-body">
    	        <form enctype="multipart/form-data" method="POST" class="form-horizontal">
                    <div class="row">
                        <div class="col-md-9">
                            <input type="file" name="optimized_contents" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-warning ">Аналізувати</button>
                        </div>
                    </div>
    	        </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">PageSpeed Insights</h4>
            </div>
            <div class="panel-body">
    	        <a href="https://developers.google.com/speed/pagespeed/insights/?url=<?=SITE_URL?>" target="_blank" class="btn btn-sm btn-info ">Перевірити сайт за допомогою Google PageSpeed Insights</a>
            </div>
        </div>
    </div>
</div>

<?php if($manifest) { 
	function size($mem)
	{
		if($mem > 1048576)
			echo (string) round($mem/1048576, 5) . ' Мб';
		elseif($mem > 1024)
			echo (string) round($mem/1024, 5) . ' Кб';
		else
			echo (string) $mem . ' б';
	}
	?>
<div class="row">
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Файли за optimized_contents.zip</h4>
        </div>
        <div class="panel-body">
        	<form action="<?=SITE_URL?>admin/wl_pagespeed/replace" method="POST">
	        	<table class="table table-striped table-bordered nowrap" width="100%">
	                <thead>
	                    <tr>
	                        <th title="Замінити"><i class="fa fa-share"></i></th>
	                        <th title="Зберегти оригінал"><i class="fa fa-save"></i></th>
	                        <th>Файл</th>
	                        <th>Розмір до</th>
	                        <th>Розмір після</th>
	                        <th>Економія</th>
	                    </tr>
	                </thead>
	                <tbody>
	                	<?php $before = $after = 0;
	                	foreach ($manifest as $file) { 
	                		$before += $file['to_size']; $after += $file['from_size']; ?>
	                		<tr>
	                			<td title="Замінити"><input type="checkbox" name="replace[]" value="<?=$file['to']?>" checked></td>
	                			<td title="Зберегти оригінал"><input type="checkbox" name="backup[]" value="<?=$file['to']?>" <?=in_array($file['part'], array('js', 'css')) ? 'checked' : ''?>></td>
	                			<td><a href="<?=SITE_URL.$file['to']?>" target="_blank"><?=$file['to']?></a> <strong><a href="<?=SITE_URL.$file['from']?>" target="_blank">Нове</a></strong></td>
	                			<td><?=size($file['to_size'])?></td>
	                			<td><?=size($file['from_size'])?></td>
	                			<td><?=size($file['to_size'] - $file['from_size'])?> <strong><?=round(100 * ($file['to_size'] - $file['from_size']) / $file['to_size'], 1)?>%</strong></td>
	                		</tr>
	                	<?php } ?>
	                	<tr>
	                		<th></th>
	                		<th></th>
	                		<th class="text-right">Разом:</th>
	                		<th><?=size($before)?> </th>
	                		<th><?=size($after)?> </th>
	                		<th><?=size($before - $after)?> <strong><?=round(100 * ($before - $after) / $before, 1)?>%</strong></th>
	                	</tr>
	                </tbody>
	            </table>
	            <button type="submit" class="btn btn-sm btn-warning ">Виконати заміну</button>
            </form>
        </div>
    </div>
</div>
<?php } ?>