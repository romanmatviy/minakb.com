<?php

$action = ($action != '') ? $action : 'wl_forms/save';

?>

	<form action="<?=SITE_URL.$action?>" method="<?= ($form->type == 1)? 'GET' : 'POST' ?>">
		<input type="hidden" name="form_name" value="<?=$form->name?>">
		<?php if($fields) foreach ($fields as $field) {

			if($field->name == 'age' && $_SESSION['user']->worker == 2)
				continue;

			$path = APP_PATH.'views/@wl_forms/@commons/'.$field->type_name.'.php';
			if(file_exists($path))
				require $path;
			else require '@commons/input.php';
		}

		if(!empty($data)){
			foreach ($data as $key => $value) {
				echo "<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\">";
			}
		}

		?>
		<div class="col-lg-12" >
			<div class="form-group text-center">
	            <input type="submit" class="btn btn-success" value="Зберегти">
	        </div>
	    </div>
	</form>
