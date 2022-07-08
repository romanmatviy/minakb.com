<div class="row">
	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_question" method="POST">
		<input type="hidden" name="id" value="<?=$question->id?>">
        <div class="table-responsive">
            <table class="table table-striped table-bordered nowrap" width="100%">
				<?php 
				$group_alias = '';
				if($_SESSION['option']->useGroups){
					$this->load->smodel('faq_model');
					$groups = $this->faq_model->getGroups();
					if($groups){
						echo "<tr><th>Група</th><td>";
						echo('<select name="group" class="form-control">');
							echo ('<option value="0">Немає</option>');
							foreach ($groups as $group) {
								$selected = '';
								if($group->id == $question->group) {
									$selected = 'selected';
									$group_alias = $group->alias .'/';
								}
								echo ("<option value=\"{$group->id}\" {$selected}>{$group->name}</option>");
							}
						echo('</select>');
						echo "</td></tr>";
					} else { ?>
						<div class="note note-info">
							<h4>Увага! В налаштуваннях адреси не створено жодної групи!</h4>
							<p>
							    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group">Додати групу</a>
                            </p>
						</div>
				<?php }
				} ?>
				<tr>
					<th>Власне посилання</th>
					<td> <?=SITE_URL.$_SESSION['alias']->alias.'/'.$group_alias?>
						<input type="text" name="alias" value="<?=$question->alias?>" class="form-control" required>
					</td>
				</tr>
				<tr>
					<th>Статус активності</th>
					<td>
						<input type="radio" name="active" value="1" <?=($question->active == 1)?'checked':''?> id="active-1"><label for="active-1">Активно</label>
						<input type="radio" name="active" value="0" <?=($question->active == 0)?'checked':''?> id="active-0"><label for="active-0">Тимчасово відключено</label>
					</td>
				</tr>
				<tr>
					<th>Додано</th>
					<td><?=$question->author_add .'. ' . $question->author_add_name . date(' d.m.Y H:i', $question->date_add)?></td>
				</tr>
				<tr>
					<th>Востаннє редагувано</th>
					<td><?=$question->author_edit .'. ' . $question->author_edit_name . date(' d.m.Y H:i', $question->date_edit)?></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" class="btn btn-sm btn-success" value="Зберегти"></td>
				</tr>
            </table>
        </div>
    </form>
</div>