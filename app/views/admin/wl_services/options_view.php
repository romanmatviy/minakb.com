<?php if(!empty($_SESSION['notify']->error)){ ?>
<div class="row">
    <div class="col-md-9">
		<div class="alert alert-danger fade in m-b-15">
			<strong>Помилка!</strong>
			<?=$_SESSION['notify']->error?>
			<span class="close" data-dismiss="alert">&times;</span>
		</div>
    </div>
</div>
<?php unset($_SESSION['notify']->success, $_SESSION['notify']->error); } ?>

<!-- begin row -->
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Загальна інформація про сервіс</h4>
            </div>
            <div class="panel-body">
				<table class="table table-striped table-bordered">
					<tbody>
						<?php foreach ($service as $key => $value) { ?>
							<tr>
								<th><?=$key?></th>
								<td><?=$value?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
            </div>
        </div>
    </div>
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Основні налаштування сервісу</h4>
            </div>
            <div class="panel-body">
            	<?php if($options){ ?>
					<table class="table table-striped table-bordered">
						<tbody>
							<?php foreach ($options as $opt) if($opt->alias == 0) { ?>
								<tr>
									<th><?=$opt->name?></th>
									<td><?='<input type="text" id="opt-'.$opt->id.'" value="'. $opt->value .'" onchange="saveOption('.$opt->id.')">'?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } else { ?>
					<div class="note note-info">
						<h4>Налаштування для даного сервісу відсутні!</h4>
					</div>
				<?php } ?>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
<!-- begin row -->
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Основні адреси, де використовується сервіс</h4>
            </div>
            <div class="panel-body">
            	<?php if (!empty($aliases)){ ?>
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>id</th>
								<th>alias</th>
								<th>table</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($aliases as $alias) { ?>
								<tr>
									<td><?=$alias->id?></td>
									<td><a href="<?=SITE_URL.'admin/wl_aliases/'.$alias->alias?>"><?=$alias->alias?></a></td>
									<td><?=$alias->table?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } else { ?>
					<div class="note note-info">
						<h4>Адреси відсутні!</h4>
						<p>
						    <a href="<?=SITE_URL?>admin/wl_aliases/add?service=<?=$service->id?>">Додати адресу до сервісу</a>
                        </p>
					</div>
				<?php } ?>
            </div>
        </div>
    </div>
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Видалити сервіс</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL?>admin/wl_services/uninstall" method="POST">
					<input type="hidden" name="id" value="<?=$service->id?>">
					<div class="note note-danger">
	            		<h4>Увага!</h4>
	            		<p>Ви впевнені що бажаєте видалити сервіс "<b><?=$service->name?></b>"?</p>
	            		<p>Всі головні адреси, що пов'язані з даним сервісом припинять роботу</p>
	            	</div>
					<input type="checkbox" name="content" value="1" id="content" checked><label for="content">Видалити головні адреси та всі їхні внутрішні підсторінки, що пов'язані зі сервісом</label>
					<br>
	            	<div class="form-group">
                        <label class="col-md-4 control-label">Пароль адміністратора для підтвердження</label>
                        <div class="col-md-8">
                            <input type="password" name="admin-password" class="form-control" required />
                            <br>
                        </div>
                    </div>
                    
                    <div class="col-md-5"></div>
	            	<button type="submit" class="btn btn-sm btn-danger col-md-3">Деінсталювати</button>
				</form>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<?php

$path = APP_PATH.'services'.DIRSEP.$service->name.DIRSEP.'views/admin_view.php';
if(file_exists($path)){
	echo('<br>');
    require_once($path);
    echo('<br>');
}

?>
<script type="text/javascript">
	function saveOption (id) {
		$.ajax({
			url: "<?=SITE_URL?>admin/wl_services/saveOption",
			type: 'POST',
			data: {
				id: id,
				value :  $('#opt-'+id).val(),
				json : true
			},
			success: function(res){
				if(res['result'] == false){
					$.gritter.add({title:"Помилка!",text:res['error']});
				} else {
					$.gritter.add({title:"Дані успішно збережено!"});
				}
			},
			error: function(){
				$.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
			},
			timeout: function(){
				$.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
			}
		});
	}
</script>