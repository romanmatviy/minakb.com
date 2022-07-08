<?php if(isset($_SESSION['notify'])) { 
require APP_PATH.'views/admin/notify_view.php';
} ?>
      
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-list"></i> До замовлень</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/bonus" class="btn btn-success btn-xs"><i class="fa fa-ravelry"></i> До бонус-кодів</a>
            	</div>
                <h4 class="panel-title"><?=($bonus->id == 0) ? 'Додати бонус-код':'Бонус-код #'.$bonus->id?></h4>
            </div>
            <div class="panel-body">
                <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_bonus" method="POST" class="form-horizontal" id="bonusFormActivate">
                    <input type="hidden" name="id" value="<?=$bonus->id?>">
                    <input type="hidden" name="code" value="<?=$bonus->code?>">
                    <input type="hidden" name="onlyActive" value="1">
                </form>
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_bonus" method="POST" class="form-horizontal" name="bonusForm">
            		<input type="hidden" name="id" value="<?=$bonus->id?>">
            		<?php if($bonus->id > 0){ ?>
        			<div class="form-group">
                        <label class="col-md-3 control-label">Статус</label>
                        <div class="col-md-9">
                            <label>
                            	<input type="radio" name="status" value="1" <?=($bonus->status == 1) ? 'checked':''?>> Активний
                            </label>
                            <label>
                            	<input type="radio" name="status" value="0" <?=($bonus->status == 0) ? 'checked':''?>> Відключено
                            </label>
                            <label>
                            	<input type="radio" name="status" value="-1" <?=($bonus->status == -1) ? 'checked':''?>> Архів
                            </label>
                            <?php if($bonus->status == 0) { ?>
                                <div class="alert alert-warning">
                                    <i class="fa fa-check fa-2x pull-left"></i>
                                    <h4>Увага! Бонус-код <strong><?=$bonus->code?></strong> необхідно <input type="submit" class="btn btn-xs btn-warning" value="Активувати" form="bonusFormActivate"></h4>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
            		<?php } ?>
            		<div class="form-group">
                        <label class="col-md-3 control-label">Режим бонусу</label>
                        <div class="col-md-9">
                            <label>
                            	<input type="radio" name="mode" value="0" <?=($bonus->mode == 0) ? 'checked':''?>> Без коду (діє зразу для всіх замовлень)
                            </label>
                            <label>
                            	<input type="radio" name="mode" value="1" <?=($bonus->mode == 1) ? 'checked':''?>> Бонус-код
                            </label>
                        </div>
                    </div>
                    <?php if($bonus->mode == 1) { ?>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Бонус-код</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="code" value="<?=$bonus->code?>" <?=($bonus->id == 0) ? 'disabled':''?> required minlength="4">
                                <?php if($bonus->id == 0){ ?>
    	                            <br>
    	                            <label>
    	                            	<input type="checkbox" checked name="generate" value="1">
    	                            	Автогенерація бонус-коду у <input type="number" min="4" max="12" value="8" name="generateLength"> символів
    	                            </label>
    	                        <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Службова інформація</label>
                        <div class="col-md-9">
                            <textarea name="info" class="form-control" placeholder="Джерела поширення коду, тощо"><?=$bonus->info?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Код діє <br><strong>рази</strong></label>
                        <div class="col-md-9">
                            <label>
                            	<input type="radio" name="count_do" value="0" <?=($bonus->count_do >= 0) ? 'checked':''?>>
                            	<input type="number" min="1" value="<?=($bonus->count_do >= 0) ? $bonus->count_do:1?>" <?=($bonus->count_do == -1) ? 'disabled':''?> name="count_do_numbers"> раз
                            </label>
                            <br>
                            <label>
                            	<input type="radio" name="count_do" value="-1" <?=($bonus->count_do == -1) ? 'checked':''?>> Необмежену кількість разів
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Код діє <br><strong>дати</strong></label>
                        <div class="col-md-9">
                            <div class="input-group">
                            	<span class="input-group-addon">від</span>
					            <input type="datetime-local" name="from" value="<?=(!empty($bonus->from))?date('Y-m-d\TH:s', $bonus->from):date('Y-m-d\TH:s')?>" class="form-control">
					        </div>
					        <div class="input-group m-t-5">
                            	<span class="input-group-addon">до</span>
					            <input type="datetime-local" name="to" min="<?=(!empty($bonus->from))?date('Y-m-d\TH:s', $bonus->from):date('Y-m-d\TH:s')?>" value="<?=(!empty($bonus->to))?date('Y-m-d\TH:s', $bonus->to):''?>" class="form-control">
					        </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Знижка</label>
                        <div class="col-md-9">
                            <div class="input-group">
                            	<label class="input-group-addon"> <input type="radio" name="type_do" value="persent" required <?=(isset($bonus->discount_type) && $bonus->discount_type == 2)?'checked':''?>> %</label>
					            <input type="number" name="persent" <?=(isset($bonus->discount_type) && $bonus->discount_type == 2)?'value="'.$bonus->discount.'"':'disabled'?> min="0" step="0.01" required class="form-control">
					            <span class="input-group-addon">від суми у корзині</span>
					        </div>
					        <div class="input-group m-t-5">
                            	<label class="input-group-addon"> <input type="radio" name="type_do" value="fixsum" required <?=(isset($bonus->discount_type) && $bonus->discount_type == 1)?'checked':''?>> фіксована сума</label>
					            <input type="number" name="fixsum" <?=(isset($bonus->discount_type) && $bonus->discount_type == 1)?'value="'.$bonus->discount.'"':'disabled'?> min="0" step="0.01" required class="form-control">
					            <span class="input-group-addon">y.o.</span>
					        </div>
					        <br>
					        <label>
                            	<input type="checkbox" name="maxActive" value="1" <?=($bonus->discount_max > 0)?'checked':''?>>
                            	Не більше ніж <input type="number" min="0" name="maxDiscount" <?=($bonus->discount_max > 0)?'value="'.$bonus->discount_max.'"':'disabled'?>> y.o.
                            </label><br>
					        <label>
                            	<input type="checkbox" name="minActive" value="1" <?=($bonus->order_min > 0)?'checked':''?>>
                            	Мінімальна сума замовлення <input type="number" min="0" name="minSum" <?=($bonus->order_min > 0)?'value="'.$bonus->order_min.'"':'disabled'?>> y.o.
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success"><?=($bonus->id == 0) ? 'Додати':'Зберегти'?></button>
                        </div>
                    </div>
            	</form>
            </div>
        </div>
    </div>
</div>

<?php $_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/admin_bonus.js'; ?>