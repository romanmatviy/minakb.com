<div class="row">
	<div class="col-md-3"></div>
	<div class="col-md-6">
		<form method="POST" action="<?=SITE_URL?>admin/wl_audio/save" enctype="multipart/form-data" class="form-horizontal" onsubmit="$('#saveing').css('display', 'block');">
			<input type="hidden" name="alias" value="<?=$_SESSION['alias']->id?>">
			<input type="hidden" name="alias_folder" value="<?=$_SESSION['option']->folder?>">
			<input type="hidden" name="content" value="<?=$_SESSION['alias']->content?>">
			<div class="form-group">
		        <label>Виберіть аудіо: (audio/mp3, wma, mpeg, wav, ogg)</label>
		        <input type="file" name="audio[]" class="form-control" multiple required>
			</div>
			<div class="form-group">
				<div class="center">
					<button type="submit" class="btn btn-primary">Додати</button>
				</div>
			</div>
		</form>
	</div>
</div>

<ol id="sortable">
<?php
	$audios = $this->db->getAllDataByFieldInArray('wl_audio', array('alias' => $_SESSION['alias']->id, 'content' => $_SESSION['alias']->content), 'position');
	if($audios)
	{
		foreach ($audios as $audio) {
			echo("<li class=\"ui-state-default\" id=\"audio-{$audio->id}\"> "); ?>
			<audio controls>
				  <source src="<?= SITE_URL.'audio/'.$_SESSION['option']->folder.'/'.$audio->content.'/'.$audio->name?>" type="audio/<?= $audio->extension?>">
				  Ваш браузер не підтримує аудіо формат.
			</audio>
			<input type="text" id="text-audio-<?=$audio->id?>" value="<?=$audio->text?>" class="form-control" onChange="saveAudioText(<?=$audio->id?>, this)">
			<span style="float:left">Додано: <?=date('d.m.Y H:i', $audio->date_add)?></span>
			<?php echo(" <a class='btn btn-danger btn-xs' href=\" ".SITE_URL."admin/wl_audio/delete?id={$audio->id}&position={$audio->position}&alias={$_SESSION['alias']->id}&content={$audio->content}&name={$audio->name}\">Видалити</a></li>");
		}
	}
?>
</ol>

<style>
	.ui-state-default {
		margin: 2px;
		padding: 5px;
		font-size: 15px;
		height: 45px;
    	clear: both;
	}
	.ui-state-default audio {
		float: left;
	}
	.ui-state-default a {
		float: right;
	}
	.ui-state-default input {
		width: 50%;
	    float: left;
	    margin: 0 10px;
	}
</style>

<script>
document.onreadystatechange = function () {
 	if (document.readyState == "complete") {
 		$( "#sortable" ).sortable({
			update: function( event, ui ) {
				$('#saveing').css("display", "block");
		        $.ajax({
		            url: "<?=SITE_URL?>admin/wl_audio/change_position",
		            type: 'POST',
		            data: {
						alias: <?=$_SESSION['alias']->id?>,
						content: <?=$_SESSION['alias']->content?>,
		                id: ui.item.attr('id'),
		                position: ui.item.index(),
		                json: true
		            },
		            success: function(res){
		                if(res['result'] == false){
		                    alert(res['error']);
		                }
		                $('#saveing').css("display", "none");
		            },
		            error: function(){
		                alert("Помилка! Спробуйте ще раз!");
		                $('#saveing').css("display", "none");
		            },
		            timeout: function(){
		                alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
		                $('#saveing').css("display", "none");
		            }
		        });
			}
		});
		$( "#sortable" ).disableSelection();
   	}
 }
 function saveAudioText(id, e){
    $('#saveing').css("display", "block");
    $.ajax({
        url: "<?=SITE_URL?>admin/wl_audio/save_text",
        type: 'POST',
        data: {
        	alias: <?=$_SESSION['alias']->id?>,
			content: <?=$_SESSION['alias']->content?>,
            id: id,
            text: e.value,
            json: true
        },
        success: function(res){
            if(res['result'] == false){
                alert(res['error']);
            }
            $('#saveing').css("display", "none");
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
            $('#saveing').css("display", "none");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
            $('#saveing').css("display", "none");
        }
    });
}
</script>