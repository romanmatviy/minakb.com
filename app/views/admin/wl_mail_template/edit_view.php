<?php $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />

<div class="row">
    <div class="panel-body">
		<ul class="nav nav-tabs">
			<li class="active"><a data-target="#tabs-main" data-toggle="tab" aria-expanded="true">Загальні дані</a></li>
			<?php if($mailTemplate->multilanguage == 1 && !empty($_SESSION['all_languages'])) foreach($_SESSION['all_languages'] as $lang) { ?>
				<li><a data-target="#tabs-<?= $lang?>" data-toggle="tab" aria-expanded="true">Текст шаблону - <?= $lang?></a></li>
			<?php } else { ?>
				<li><a data-target="#tabs-text" data-toggle="tab" aria-expanded="true">Текст шаблону</a></li>
			<?php } if($mailTemplate->savetohistory == 1) { ?>
				<li><a data-target="#tabs-history" data-toggle="tab" aria-expanded="true">Історія</a></li>
			<?php } ?>
		</ul>

		<div class="tab-content" id="mailTemplate">
			<div class="tab-pane fade active in" id="tabs-main">
				<form action="<?=SITE_URL?>admin/wl_mail_template/save" method="POST" class="form-horizontal">
					<input type="hidden" name="mailTemplateId" value="<?= $mailTemplate->id?>">
					<table>
						<div class="form-group">
							<label class="col-md-3 control-label">Від</label>
							<div class="col-md-9">
								<small style="width: 100%; text-align: left;">У "від", "до" можна використовувати наступні поля: SITE_EMAIL, <?php if(!empty($fields)) foreach($fields as $field) { echo $field->type == 'email' ? $field->name.' ' : ''; } ?></small>
								<input type="text" class="form-control" name="from" value="<?= $mailTemplate->from?>" placeholder="<?= SITE_EMAIL?>" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">До</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="to" value="<?= $mailTemplate->to?>" placeholder="to" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Зберегти в історію</label>
							<div class="col-md-9">
								<input type="checkbox" class="form-control" data-render="switchery" name="saveToHistory" value="1" <?= $mailTemplate->savetohistory == 1 ? 'checked' : '' ?>>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Форма</label>
							<div class="col-md-9">
								<?php if($allForms) foreach($allForms as $form) { ?>
									<label><input type="checkbox" name="form[]" value="<?= $form->id?>" <?= (isset($form->checked) && $form->checked == 1) ? 'checked' : '' ?> > <strong><?= $form->title?></strong> (<?= $form->name?>)</label> <a href="<?=SITE_URL?>admin/wl_forms/<?= $form->name?>">До форми</a><br>
								<?php } ?>
							</div>
						</div>
						<div class="form-group">
	                    	<div class="col-md-3"></div>
	                        <div class="col-md-9">
	                        	<input type="submit" class="btn btn-sm btn-warning " value="Зберегти">
							</div>
						</div>
					</table>
				</form>
			</div>

			<?php if($mailTemplate->multilanguage == 1 && !empty($_SESSION['all_languages'])) foreach($_SESSION['all_languages'] as $lang) {?>
			<div class="tab-pane fade" id="tabs-<?= $lang?>">
				<form action="<?=SITE_URL?>admin/wl_mail_template/saveText" method="POST" class="form-horizontal">
					<input type="hidden" name="language" value="<?= $lang?>">
					<input type="hidden" name="template" value="<?= $mailTemplate->id?>">
					<table>
						<div class="form-group">
							<label class="col-md-3 control-label">Заголовок</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="title" value="<?= isset($mailTemplateData[$lang]->title) ? $mailTemplateData[$lang]->title : '' ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Текст</label>
							<div class="col-md-9">
								<textarea class="form-control" style="height: 300px" rows="25" name="text" required><?= isset($mailTemplateData[$lang]->text) ? $mailTemplateData[$lang]->text : "<html><head>\n<title>\n\n</title>\n</head><body>\n<p>\n\n</p>\n</body></html>" ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Слова</label>
							<div class="col-md-9">
								<span class="words">
									<button type="button" class="btn btn-sm btn-default">{SITE_URL}</button>
									<button type="button" class="btn btn-sm btn-default">{IMAGE_PATH}</button>
									<?php if(is_array($fields)) 
										foreach($fields as $field)
											echo (' <button type="button" class="btn btn-sm btn-default">{'.$field->name.'}</button>'); 
									?>
								</span>
							</div>
						</div>
						<div class="form-group">
	                    	<div class="col-md-3"></div>
	                        <div class="col-md-9">
	                        	<input type="submit" class="btn btn-sm btn-warning " value="Зберегти">
							</div>
						</div>
					</table>
				</form>
			</div>

			<?php } else { ?>
			<div class="tab-pane fade" id="tabs-text">
				<form action="<?=SITE_URL?>admin/wl_mail_template/saveText" method="POST" class="form-horizontal">
					<input type="hidden" name="language" value="">
					<input type="hidden" name="template" value="<?= $mailTemplate->id?>">
					<table>
						<div class="form-group">
							<label class="col-md-3 control-label">Заголовок</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="title" value="<?= isset($mailTemplateData[0]->title) ? $mailTemplateData[0]->title : '' ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Текст</label>
							<div class="col-md-9">
								<textarea class="form-control" style="height: 300px"  rows="25" name="text" required><?= isset($mailTemplateData[0]->text) ? $mailTemplateData[0]->text : "<html><head>\n<title>\n\n</title>\n</head><body>\n<p>\n\n</p>\n</body></html>" ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Слова</label>
							<div class="col-md-9">
								<span class="words">
									<button type="button" class="btn btn-sm btn-default">{SITE_URL}</button>
									<button type="button" class="btn btn-sm btn-default">{IMAGE_PATH}</button>
									<?php if(is_array($fields)) 
										foreach($fields as $field)
											echo (' <button type="button" class="btn btn-sm btn-default">{'.$field->name.'}</button>'); 
									?>
								</span>
							</div>
						</div>
						<div class="form-group">
	                    	<div class="col-md-3"></div>
	                        <div class="col-md-9">
	                        	<input type="submit" class="btn btn-sm btn-warning " value="Зберегти">
							</div>
						</div>
					</table>
				</form>
			</div>
			<?php } if($mailTemplate->savetohistory == 1) { ?>
				<div class="tab-pane fade" id="tabs-history">
					<table class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Дата</th>
                                <th>Від</th>
                                <th>До</th>
                                <th>Тема</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($history = $this->db->getAllDataByFieldInArray('wl_mail_history', $mailTemplate->id, 'template', 'id DESC LIMIT 30')) 
                            foreach ($history as $row) { ?>
                            <tr>
                                <td><a href="<?= SITE_URL.'admin/wl_mail_template/history/'. $row->id ?>" class="btn btn-xs btn-info"> Детально (#<?= $row->id ?>)</a></td>
                                <td><?= date('d.m.Y H:i', $row->date) ?> / <strong><?=$row->send_email ? 'Відправлено' : 'Очікує відправки'?></strong></td>
                                <td><?= $row->from ?></td>
                                <td><?= $row->to ?></td>
                                <td><?= $row->subject ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
				</div>
			<?php } ?>
		</div>
    </div>
</div>
<style>
.tab-content .tab-pane small {
    margin: 0;
    width: 30px;
}
</style>

<script>
document.onreadystatechange = function () {
    if (document.readyState == "complete") {
    	$('#mailTemplate  textarea').on('click',function (e) {
            $('#mailTemplate').find('#wordTarget').removeAttr('id');
            var $target = $(e.target).attr('id', 'wordTarget');
        })

		$('.words').on('click', function (e) {
	        var $wordTarget = $('#wordTarget');
	        if($wordTarget.length){
	            var buttonText = e.target.textContent;
	            var wordTargetValue = $wordTarget.val();
	            var cursorPos = $('#wordTarget').prop('selectionStart');

	            var textBefore = wordTargetValue.substring(0,  cursorPos );
	            var textAfter  = wordTargetValue.substring( cursorPos, wordTargetValue.length );
	            $('#wordTarget').val( textBefore + buttonText + textAfter );

	            $('#wordTarget').focus()
	            wordTarget.setSelectionRange(cursorPos + buttonText.length, cursorPos + buttonText.length);
	        }
	    })
	}
}
</script>

