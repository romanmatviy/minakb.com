<legend><i class="fa fa-cogs" aria-hidden="true"></i> Керування замовленням</legend>

<?php if($cartStatuses) { ?>
<table class="table table-striped table-bordered nowrap" width="100%">
    <form action="<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/saveToHistory'?>" onsubmit="return saveToHistory()" method="POST" class="form-horizontal" >
        <input type="hidden" name="cart" value="<?= $cart->id?>">
        <tbody>
            <tr>
                <th>Статус</th>
                <td>
                    <select name="status" class="form-control" required>
                        <optgroup label="Керування замовленням">
                            <?php foreach($cartStatuses as $status) { if($status->weight < 90 || ($cart->payed >= $cart->total && $status->id == 6)) { ?>
                            <option value="<?= $status->id?>"><?= $status->name?></option>
                            <?php } } ?>
                        </optgroup>
                        <option value="0" disabled>---------------------------------------</option>
                        <option value="7">Скасоване</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Коментар</th>
                <td><textarea name="comment" class="form-control" rows="5" placeholder="<?=!empty($cart->user_language) ? "Мова користувача: {$cart->user_language}" : ''?>"></textarea></td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <button type="submit" class="btn btn-md btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>
                    <?php if($cart->status > 1) { ?>
                        <button type="button" class="btn btn-md btn-warning pull-right" data-toggle='modal' data-target='#reNew'><i class="fa fa-refresh" aria-hidden="true"></i> Перевести до статусу "Нове замовлення"</button>
                    <?php } ?>
                </td>
            </tr>
        </tbody>
    </form>
</table>
<?php } else if($cart->status > 1) { ?>
    <button type="button" class="btn btn-md btn-warning pull-right" data-toggle='modal' data-target='#reNew'><i class="fa fa-refresh" aria-hidden="true"></i> Перевести до статусу "Нове замовлення"</button>
<?php } ?>

<div class="modal fade" id="reNew" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" style="margin: 15% auto;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Перевести до статусу "Нове замовлення"</h4>
            </div>
            <form action="<?=SITE_URL?>admin/cart/reNew" method="post">
                <div class="modal-body">
                    <p>Увага! Ви підтверджуєте переведення замовлення до статусу "<strong>Нова, Не опрацьовано, не оплачено</strong>"?</p>
                    <p>Буде зроблено запис до історії замовлення, розблокується можливість редагувати вміст замовлення.</p>
                    <p><label><input type="checkbox" name="reserve_cancel" value="1"> Скасувати резерв товарів на складі, якщо такий є (товари стають доступні)</label></p>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Пароль підтвердження</strong>
                        </div>
                        <div class="col-md-9">
                            <input type="password" name="password" required class="form-control" placeholder="Пароль підтвердження переведення до нового замовлення">
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                        <input type="hidden" name="cart" value="<?=$cart->id?>">
                        <button type="submit" class="btn btn-warning" title="Перевести замовленн до статусу Нове"><i class="fa fa-refresh" aria-hidden="true"></i> Онулити замовлення</button>
                    </div>
                    <button type="button" class="btn btn-success" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span> Скасувати</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" style="margin: 15% auto;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Редагувати коментар</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group">
                        <label class=" col-md-3">Коментар:</label>
                        <div class="col-md-12">
                            <input type="hidden" id="historyId">
                            <textarea class="form-control" id="modalComment"  rows="5"></textarea>    
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="editComment()">Зберегти</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ttnModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" style="margin: 15% auto;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-truck" aria-hidden="true"></i> ТТН доставки</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group">
                        <label class=" col-md-5">Оновити статус замовлення:</label>
                        <div class="col-md-7">
                            <select id="ttn-status" class="form-control" required>
                                <?php if($cartStatuses)
                                    foreach($cartStatuses as $status)
                                        if($status->weight >= 20 && $status->weight < 30) { ?>
                                            <option value="<?= $status->id?>"><?= $status->name?></option>
                                <?php } ?>
                                <option value="0">Не змінювати</option>
                            </select> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="saveTTN(1)"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        $('#commentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget),
                comment = button.data('comment'),
                id = button.data('id');

            var modal = $(this);
            modal.find('#modalComment').text(comment);
            modal.find('#historyId').val(id);
        });

        $('#manager_comment textarea').change(function () {
            if(this.dataset.cart > 0)
            {
                var cart_id = this.dataset.cart;
                $('#saveing').css("display", "block");
                $.ajax({
                    url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/editManagerComment'?>",
                    type: 'POST',
                    data: {
                        'comment' : this.value,
                        'id' : cart_id
                    },
                    success:function(res){
                        if(res['result'] == true)
                            $.gritter.add({title:'Замовлення #'+cart_id, text:"Службовий коментар оновлено!"});
                        else
                            $.gritter.add({title:"Помилка!", text:res['error']});
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
        })
    });

    function editComment() {
        $('#saveing').css("display", "block");
        var comment = $("#modalComment").val(),
            id = $("#historyId").val();
        $.ajax({
            url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/editComment'?>",
            type: 'POST',
            data: {
                'comment' : comment,
                'id' : id
            },
            success:function(res){
                if(res['result'] == true){
                    $("#comment-"+id).text(comment);
                    $('#commentModal').modal('hide');
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
        })
    }

    function presaveTTN(showTTNmodal) {
        if(showTTNmodal)
            $('#ttnModal').modal('show');
        else
            saveTTN(0)
    }

    function saveTTN(showTTNmodal) {
        var status = 0;
        if(showTTNmodal)
        {
            status = $('#ttn-status').val();
            $('#ttnModal').modal('hide');
        }
        if(shipping_ttn.dataset.cart > 0)
        {
            var cart_id = shipping_ttn.dataset.cart;
            $('#saveing').css("display", "block");
            $.ajax({
                url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/saveTTN'?>",
                type: 'POST',
                data: {
                    'comment' : shipping_ttn.value,
                    'status' : status,
                    'cart' : cart_id,
                    'ajax' : true
                },
                success:function(res){
                    if(res['result'] == true)
                    {
                        if(status)
                            document.location.reload();
                        else
                        {
                            $.gritter.add({title:'Замовлення #'+cart_id, text:"ТТН оновлено!"});
                            $('#saveing').css("display", "none");
                        }
                    }
                    else
                    {
                        $.gritter.add({title:"Помилка!", text:res['error']});
                        $('#saveing').css("display", "none");
                    }
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

    function saveToHistory() {
        if(confirm('Ви впевнені, що хочете оновити статус?')){
            return true;
        }
        return false;
    }
</script>