<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Автоматизований експорт товарів у форматі  <span class="label label-success">yml для prom.ua</span></h4>
            </div>
			<div class="panel-body">
				<?php if(empty($_SESSION['option']->exportKey)) { ?>
			        <div class="note note-info">
			        	<h4>Увага! Експорт товарів в автоматизованому режимі відключено</h4>
			        	<p> <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export?active" class="btn btn-warning"><i class="fa fa-check"></i> активувати ключ експорту</a> </p>
			        	<small>В процесі активації автоматично створиться індивідуальний ключ безпеки</small>
			        </div>
				<?php } else { ?>
					<div class="note note-info">
						<h2>yml для <span class="label label-success">prom.ua</span></h2>
			        	<p>Посилання для всіх товарів з активних груп експорту</p>
			        	<?php if($_SESSION['all_languages'])
			        	foreach ($_SESSION['all_languages'] as $i => $language) {
			        		$link = SERVER_URL;
			        		if($i > 0)
			        			$link .= $language.'/'; ?>
			        		<p> <strong><?=$language?>:</strong> <i class="fa fa-download"></i> <a download href="<?=$link.$_SESSION['alias']->alias?>/export_prom?key=<?=$_SESSION['option']->exportKey?>"><strong><?=$link.$_SESSION['alias']->alias?>/export_prom?key=<?=$_SESSION['option']->exportKey?></strong></a> </p>
			        	<?php } else { ?>
			        		<p> <i class="fa fa-download"></i> <a download href="<?=SERVER_URL.$_SESSION['alias']->alias?>/export_prom?key=<?=$_SESSION['option']->exportKey?>"><strong><?=SERVER_URL.$_SESSION['alias']->alias?>/export_prom?key=<?=$_SESSION['option']->exportKey?></strong></a> </p>
			        	<?php } if (!empty($groups)) { ?>
			        	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export?groups&type=prom" class="btn btn-info"><i class="fa fa-cogs"></i> Керування групами експорту</a>
			        	<?php } ?>
			        </div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Автоматизований експорт товарів у форматі <span class="label label-warning">xml для Google Merchant Center</span></h4>
            </div>
			<div class="panel-body">
				<?php if(empty($_SESSION['option']->exportKey)) { ?>
			        <div class="note note-info">
			        	<h4>Увага! Експорт товарів в автоматизованому режимі відключено</h4>
			        	<p> <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export?active" class="btn btn-warning"><i class="fa fa-check"></i> активувати ключ експорту</a> </p>
			        	<small>В процесі активації автоматично створиться індивідуальний ключ безпеки</small>
			        </div>
				<?php } else { ?>
					<div class="note note-info">
			        	<h2>xml для <span class="label label-warning">Google Merchant Center</span></h2>
			        	<p>Посилання для всіх товарів з активних груп експорту</p>
			        	<?php if($_SESSION['all_languages'])
			        	foreach ($_SESSION['all_languages'] as $i => $language) {
			        		$link = SERVER_URL;
			        		if($i > 0)
			        			$link .= $language.'/'; ?>
			        		<p> <strong><?=$language?>:</strong> <i class="fa fa-download"></i> <a download href="<?=$link.$_SESSION['alias']->alias?>/export_google?key=<?=$_SESSION['option']->exportKey?>"><strong><?=$link.$_SESSION['alias']->alias?>/export_google?key=<?=$_SESSION['option']->exportKey?></strong></a> </p>
			        	<?php } else { ?>
			        		<p> <i class="fa fa-download"></i> <a download href="<?=SERVER_URL.$_SESSION['alias']->alias?>/export_google?key=<?=$_SESSION['option']->exportKey?>"><strong><?=SERVER_URL.$_SESSION['alias']->alias?>/export_google?key=<?=$_SESSION['option']->exportKey?></strong></a> </p>
			        	<?php } ?>
			        	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export?groups&type=google" class="btn btn-info"><i class="fa fa-cogs"></i> Керування групами експорту</a>
			        </div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Автоматизований експорт товарів у форматі <span class="label label-info">xml для Facebook Merchant</span></h4>
            </div>
			<div class="panel-body">
				<?php if(empty($_SESSION['option']->exportKey)) { ?>
			        <div class="note note-info">
			        	<h4>Увага! Експорт товарів в автоматизованому режимі відключено</h4>
			        	<p> <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export?active" class="btn btn-warning"><i class="fa fa-check"></i> активувати ключ експорту</a> </p>
			        	<small>В процесі активації автоматично створиться індивідуальний ключ безпеки</small>
			        </div>
				<?php } else { ?>
					<div class="note note-info">
			        	<h2>xml для <span class="label label-info">Facebook</span></h2>
			        	<p>Посилання для всіх товарів з активних груп експорту</p>
			        	<?php if($_SESSION['all_languages'])
			        	foreach ($_SESSION['all_languages'] as $i => $language) {
			        		$link = SERVER_URL;
			        		if($i > 0)
			        			$link .= $language.'/'; ?>
			        		<p> <strong><?=$language?>:</strong> <i class="fa fa-download"></i> <a download href="<?=$link.$_SESSION['alias']->alias?>/export_facebook?key=<?=$_SESSION['option']->exportKey?>"><strong><?=$link.$_SESSION['alias']->alias?>/export_facebook?key=<?=$_SESSION['option']->exportKey?></strong></a> </p>
			        	<?php } else { ?>
			        		<p> <i class="fa fa-download"></i> <a download href="<?=SERVER_URL.$_SESSION['alias']->alias?>/export_facebook?key=<?=$_SESSION['option']->exportKey?>"><strong><?=SERVER_URL.$_SESSION['alias']->alias?>/export_facebook?key=<?=$_SESSION['option']->exportKey?></strong></a> </p>
			        	<?php } ?>
			        	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export?groups&type=facebook" class="btn btn-info"><i class="fa fa-cogs"></i> Керування групами експорту</a>
			        </div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('a[download]').click(function () {
        	$('#saveing').addClass('show').delay( 30000 ).queue(function(){ $(this).removeClass('show').dequeue(); });
        })
    };
</script>