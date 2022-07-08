<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <?php if($_SESSION['user']->admin) { ?>
                    <div class="panel-heading-btn">
                        <a href="<?=SITE_URL?>admin/wl_forms/<?=$this->data->uri(3)?>" class="btn btn-warning btn-xs"><i class="fa fa-cogs"></i> До налаштування форми</a>
                    </div>
                    <div class="panel-heading-btn">
                        <a href="<?=SITE_URL?>admin/wl_forms/export/<?=$this->data->uri(3)?>" class="btn btn-info btn-xs"><i class="fa fa-cloud-download"></i> Експорт</a>
                    </div>
                <?php } ?>
                <h4 class="panel-title">Інформація:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата додачі</th>
                                <?php $files_field = [];
                                if($formInfo)
                                {
                                    if($tableInfo)
                                    {
                                        foreach ($tableInfo[0] as $key => $value) {
                                            if($key != 'new')
                                                foreach ($formInfo as $info) {
                                                    if($info->type_name == 'file')
                                                        $files_field[] = $info->name;
                                                    if($info->name == $key)
                                                        echo '<th>'.$info->title.'</th>';
                                                }
                                        }
                                    }
                                    else
                                        foreach ($formInfo as $info) {
                                            echo '<th>'.$info->title.'</th>';
                                        }
                                } ?>
                                <?php if($_SESSION['language']) { ?>
                                    <th>Мова сайту</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php if($tableInfo) foreach ($tableInfo as $info) {
                                echo ('<tr id="row-'.$info->id.'">');
                                $user = $info->user_id ? " <a href='/admin/wl_users/{$info->user_id}' title=\"{$info->user_id}. {$info->user_name}\"><i class=\"fa fa-user\"></i></a>" : '';
                                foreach ($info as $key => $value) {
                                    if($key == 'user_id' || $key == 'user_name' || $key == 'new')
                                        continue;
                                    if($key == 'id' && $_SESSION['user']->type == 1)
                                        echo '<td><button class="btn btn-xs btn-danger" data-id="'.$value.'"><i class="fa fa-trash" aria-hidden="true"></i></button> '.$value.$user.'</td>';
                                    elseif(in_array($key, $files_field) && !empty($value))
                                    {
                                        $path = SERVER_URL."files/form_{$form->name}/{$value}";
                                        $extension = explode('.', $value);
                                        $extension = end($extension);
                                        $download = in_array($extension, ['jpg', 'png', 'pdf']) ? 'target="_blank"' : 'download';
                                        echo "<td><a href='{$path}' {$download}>Файл</td>";
                                    }
                                    elseif($key == 'language')
                                    {
                                        if($_SESSION['language'])
                                            echo '<td>'.$value.'</td>';
                                        else
                                            continue;
                                    }
                                    else
                                        echo "<td>" . (preg_match('/date/', $key) ? date('d.m.Y H:i', $value) : $key.$value) . '</td>';
                                }
                                echo ('</tr>');
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/jquery.dataTables.js';  
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colReorder.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colVis.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.responsive.js';
  $_SESSION['alias']->js_init[] = '$("#data-table").DataTable({ "order":[[0, "desc"]], responsive: true, lengthMenu: [20, 40, 60] });'; 
?>
<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />

<?php if($_SESSION['user']->type == 1) { ?>
<script>
    var btn = document.querySelectorAll('table#data-table td > button.btn-danger');
    for (var i = 0; i < btn.length; i++) {
        btn[i].onclick = deleteRow;
    }
    function deleteRow() {
        var rowId = this.dataset.id;
        if(confirm('Видалити запис #'+rowId+'? Увага! Дані відновленню не підлягають!'))
        {
            $('#saveing').css("display", "block");
            $.ajax({
                url: SITE_URL + "admin/wl_forms/deleteRow",
                type: 'POST',
                data: {
                    id: this.dataset.id,
                    table: '<?=$form->table?>',
                    json: true
                },
                success: function(res){
                    if(res['result'] == false) {
                        $.gritter.add({title:"Помилка!", text:res['error']});
                    }
                    else
                    {
                        $('tr#row-'+rowId).slideUp();
                        $.gritter.add({title:'Запис #'+rowId, text:"Дані успішно видалено!"});
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
</script>
<?php } ?>