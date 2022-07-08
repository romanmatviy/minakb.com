<div class="row">
    <div class="col-md-12">
        <?php
		if(!empty($words)) {
			$_SESSION['alias']->js_load[] = 'assets/white-lion/wl_language_words.js';
			if($_SESSION['language'])
				$count_colums = count($_SESSION['all_languages']) + 2;
			else
				$count_colums = 3;
			foreach ($aliases as $alias) {
				$go = false;
				foreach ($words as $word) {
					if($word->alias == $alias->id)
					{
						$go = true;
						break;
					}
				}
				if(!$go)
					continue;
		?>
			<div class="panel panel-inverse">
	            <div class="panel-heading">
					<div class="btn-group pull-right">
	                        <button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
	                            <span class="caret"></span> Копіювати
	                        </button>
	                        <ul class="dropdown-menu" role="menu">
	                        	<?php foreach ($_SESSION['all_languages'] as $language) {
	                        		echo("<li><a href=\"#modal-copy-dialog\" data-toggle=\"modal\" data-alias=\"{$alias->id}\" data-language=\"{$language}\"  data-title=\"{$alias->alias}\">Копіювати ключові слова до <b>{$language}</b> де пусто</a></li>");
	                            } ?>
	                        </ul>
	                    </div>
	                <h4 class="panel-title"><?=$alias->alias?></h4>
	            </div>
	            <div class="panel-body">
		            <div class="table-responsive">
		                <table class="table table-striped table-bordered nowrap" width="100%">
		                    <thead>
		                        <tr>
		                            <th style="width: 35%">Ключове слово у шаблоні</th>
		                            <?php if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $language) {
		                            	echo("<th>{$language}</th>");
		                            } else echo("<th>До виводу</th>")?>
		                            <th>Тип</th>
		                        </tr>
		                    </thead>
		                    <tbody>
								<?php
									$all = 0;
									foreach ($words as $word) {
										if($word->alias == $alias->id){
											$word->word = htmlspecialchars($word->word, ENT_COMPAT, 'UTF-8');
											echo("<tr>");
												echo("<td id=\"word-{$word->id}\" class=\"alias-{$alias->id}\"><button class='btn btn-xs btn-danger' onClick='deleteWord({$word->id}, \"{$word->word}\")'>x</button> <b>{$word->word}</b></td>");
												if($_SESSION['language'])
												{												
													foreach ($_SESSION['all_languages'] as $language) {
														if($word->type == 1) {
															echo("<td id=\"td-word-{$word->id}-{$language}\"><input type=\"text\" value=\"{$word->$language}\" placeholder=\"{$word->word}\" id=\"word-{$word->id}-{$language}\" class=\"form-control\" onChange=\"save({$word->id}, '{$language}', this)\"></td>");
														} elseif($word->type == 3) {
															echo("<td id=\"td-word-{$word->id}-{$language}\"><textarea id=\"word-{$word->id}-{$language}\" placeholder=\"{$word->word}\" class=\"form-control\" onChange=\"save({$word->id}, '{$language}', this)\">{$word->$language}</textarea></td>");
														}
													}
												}
												else
												{
													if($word->type == 1)
													{
														echo("<td id=\"td-word-{$word->id}\"><input type=\"text\" value=\"{$word->value}\" placeholder=\"{$word->word}\" id=\"word-{$word->id}\" class=\"form-control\" onChange=\"save({$word->id}, '', this)\"></td>");
													}
													elseif($word->type == 3)
													{
														echo("<td id=\"td-word-{$word->id}\"><textarea id=\"word-{$word->id}\" placeholder=\"{$word->word}\" class=\"form-control\" onChange=\"save({$word->id}, '', this)\">{$word->value}</textarea></td>");
													}
												}
												echo("<td>");
													echo("<select onChange=\"changeType({$word->id}, this)\" class=\"form-control\">");
													$selected = ($word->type == 1) ? 'selected' : '';
													echo("<option value=\"1\" {$selected}>input</option>");
													$selected = ($word->type == 3) ? 'selected' : '';
													echo("<option value=\"3\" {$selected}>textarea</option>");
													echo("</select>");
												echo("</td>");
											echo("</tr>");
											$all++;
										}
									}
									if($all == 0) {
										echo("<tr><th colspan='{$count_colums}'>Фрази та слова сайту не налаштовано у даному шаблоні</th></tr>");
									}
								?>
		                    </tbody>
		                </table>
		            </div>
	            </div>
	        </div>
        <?php
        	}
        }
        ?>
        <div class="modal fade" id="modal-copy-dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Встановити ключові слова для мови <span class="copy-language"></span></h4>
					</div>
					<div class="modal-body">
						Встановити ключові слова та фрази шаблону <b class="copy-title"></b> як значення перекладу для мови <b class="copy-language"></b>. 
						<br> Увага! <u>Заповняться тільки пусті слова</u>, всі існуючі переклади залишаться без змін!
						<input type="hidden" id="copy-alias" value="0">
						<input type="hidden" id="copy-language" value="">
					</div>
					<div class="modal-footer">
						<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Скасувати</a>
						<a href="javascript:;" class="btn btn-sm btn-success" onclick="confirmCopy()">Копіювати</a>
					</div>
				</div>
			</div>
		</div>
    </div>
</div>

<script type="text/javascript">
	function save (id, lang, e) {
		$('#saveing').css("display", "block");
        $.ajax({
            url: "<?=SITE_URL?>admin/wl_language_words/save",
            type: 'POST',
            data: {
            	word: id,
                value: e.value,
                language: lang,
                json: true
            },
            success: function(res){
                if(res['result'] == false){
                    $.gritter.add({title:"Помилка!", text:res['error']});
                } else {
                	language = $('#word-'+id).html() + ' ' + lang;
                	$.gritter.add({title:language, text:"Дані успішно збережено!"});
                }
                $('#saveing').css("display", "none");
            },
            error: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
            },
            timeout: function(){
            	$.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            	$('#saveing').css("display", "none");
            }
        });
	}
	function deleteWord (id, word) {
		if(confirm('Підтвердіть видалення фрази "'+word+'" зі всіма перекладами. Увага! Відновити неможливо!'))
		{
			$('#saveing').css("display", "block");
	        $.ajax({
	            url: "<?=SITE_URL?>admin/wl_language_words/delete",
	            type: 'POST',
	            data: {
	            	id: id,
	                json: true
	            },
	            success: function(res){
	                if(res['result'] == false){
	                    $.gritter.add({title:"Помилка!", text:res['error']});
	                } else {
	                	$.gritter.add({title:'Видалення фрази', text:'Фразу <b>"'+word+'"</b> успішно видалено!'});
	                	$('#word-'+id).parent().slideUp();
	                }
	                $('#saveing').css("display", "none");
	            },
	            error: function(){
	                $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
	                $('#saveing').css("display", "none");
	            },
	            timeout: function(){
	            	$.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
	            	$('#saveing').css("display", "none");
	            }
	        });
	    }
	}
	function changeType (id, e) {
		$('#saveing').css("display", "block");
        $.ajax({
            url: "<?=SITE_URL?>admin/wl_language_words/changeType",
            type: 'POST',
            data: {
            	word: id,
                type: e.value,
                json: true
            },
            success: function(res){
                if(res['result'] == false){
                    $.gritter.add({title:"Помилка!",text:res['error']});
                } else {
                	if(e.value == 1) {
                		<?php if($_SESSION['language']) foreach($_SESSION['all_languages'] as $lang) { ?>
	                		var value_<?=$lang?> = $('#word-' + id + '-<?=$lang?>').val();
	                		var input_<?=$lang?> = '<input type="text" value="' + value_<?=$lang?> + '" id="word-' + id + '-<?=$lang?>" class="form-control" onChange="save(' + id + ', \'<?=$lang?>\', this)">';
	                		$('td#td-word-' + id + '-<?=$lang?>').html(input_<?=$lang?>);
                		<?php } else { ?>
                			var value = $('#word-' + id).val();
	                		var input = '<input type="text" value="' + value + '" id="word-' + id + '" class="form-control" onChange="save(' + id + ', \'\', this)">';
	                		$('td#td-word-' + id).html(input);
                		<?php } ?>
                	}
                	if(e.value == 3) {
                		<?php if($_SESSION['language']) foreach($_SESSION['all_languages'] as $lang) { ?>
	                		var value_<?=$lang?> = $('#word-' + id + '-<?=$lang?>').val();
	                		var textarea_<?=$lang?> = '<textarea id="word-' + id + '-<?=$lang?>" class="form-control" onChange="save(' + id + ', \'<?=$lang?>\', this)">' + value_<?=$lang?> + '</textarea>';
	                		$('td#td-word-' + id + '-<?=$lang?>').html(textarea_<?=$lang?>);
                		<?php } else { ?>
                			var value = $('#word-' + id).val();
	                		var textarea = '<textarea id="word-' + id + '" class="form-control" onChange="save(' + id + ', \'\', this)">' + value + '</textarea>';
	                		$('td#td-word-' + id).html(textarea);
                		<?php } ?>
                	}
                	$.gritter.add({title:$('#word-'+id).html(), text:"Тип успішно змінено!"});
                }
                $('#saveing').css("display", "none");
            },
            error: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
            },
            timeout: function(){
            	$.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            	$('#saveing').css("display", "none");
            }
        });
	}
</script>