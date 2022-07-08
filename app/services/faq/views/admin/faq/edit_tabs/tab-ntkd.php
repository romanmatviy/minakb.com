<?php 
	if($_SESSION['language'] && isset($lang)) { 
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = $question->id;
		$where['language'] = $lang;
		$ntkd = $this->db->getAllDataById('wl_ntkd', $where);
		$lang_text_1 = ", '{$lang}'";
		$lang_text_2 = "-{$lang}";
	} else {
		$ntkd = new stdClass();
		$ntkd->name = $question->question;
		$ntkd->text = $question->answer;
		$lang_text_1 = $lang_text_2 = $lang = "";
	}
?>

<div class="row">
	<label class="col-md-2 control-label">Питання:</label>
	<div class="col-md-4">
	    <input type="text" onChange="save('name', this <?=$lang_text_1?>)" value="<?=$ntkd->name?>" class="form-control">
	</div>
</div>
<div class="row">
	<label class="control-label">Відповідь:</label><br>
	<textarea class="t-big" onChange="save('text', this <?=$lang_text_1?>)" id="editor<?=$lang_text_2?>"><?=$ntkd->text?></textarea>
	<button class="btn btn-success m-t-5" onClick="saveText('<?=$lang?>')"><i class="fa fa-save"></i> Зберегти відповідь</button>
</div>


<style type="text/css">
	textarea{
		width: 100%;
		height: 100px;
	}
	textarea.t-big{
		height: 300px;
	}
</style>