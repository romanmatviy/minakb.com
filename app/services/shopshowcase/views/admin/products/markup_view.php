<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/<?= $_SESSION['alias']->alias?>/markup_add" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> Додати націнку</a>
                </div>
                <h4 class="panel-title">Націнка:</h4>
            </div>
            <div class="panel-body">
                <form action="<?= SITE_URL?>admin/<?= $_SESSION['alias']->alias?>/markup_save" method="POST" >
                    <?php if($markups) foreach($markups as $markup) {?>
                    <div class="col-md-12" id="markUp_<?= $markup->id?>">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class=><b>Від</b></label>
                                <input type="text" class="form-control" name="<?= $markup->id?>[from]" value="<?= $markup->from?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>До</label>
                                <input type="text" class="form-control" name="<?= $markup->id?>[to]" value="<?= $markup->to?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Націнка (коефіціент)</label>
                                <input type="text" class="form-control" name="<?= $markup->id?>[value]" value="<?= $markup->value?>">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="">Видалити</label>
                                <button type="button" class="btn btn-md btn-danger" onclick="deleteMarkUp(<?= $markup->id?>)">X</button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="col-md-12 text-center">
                                <input type="submit" class="btn btn-sm btn-info" value="Зберегти">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteMarkUp(id) {
        if(confirm('Ви впевнені, що хочете видалити націнку?'))
        {
            $.ajax({
                url: '<?= SITE_URL?>admin/<?= $_SESSION['alias']->alias?>/markup_delete',
                type: 'POST',
                data : {
                    id : id
                },
                success: function (res) {
                    if(res['result'] == true)
                        $("#markUp_"+id).remove();
                    else
                        alert('Помилка');
                }
            })
        }
    }
</script>