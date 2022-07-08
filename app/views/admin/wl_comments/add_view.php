<!-- begin row -->
<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-6">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
            <div class="panel-heading">     
                <div class="row">
                    <div class="col-md-9">
                        <h4 class="panel-title">Новий відгук</h4>
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="<?=SITE_URL?>admin/commentaries" class="btn btn-warning btn-xs">Повернутись</a>
                    </div>
                </div>    
            </div>
            <div class="panel-body">
                <form action="<?=SITE_URL?>admin/commentaries/save" method="POST" class="form-horizontal">
                	<input type="hidden" name="id" value="0">
                    <div class="form-group" >
                        <label class="col-md-3 control-label">Автор</label>
                        <div class="col-md-9">
                            <input type="text" name="name_autor" class="form-control" required placeholder="Ваше ім'я" required />
                        </div>
                    </div>
                    <div class="form-group" title="УВАГА! На даний емейл буде надіслано пароль користувача">
                        <label class="col-md-3 control-label">Пошта</label>
                        <div class="col-md-9">
                            <input type="email" name="email" class="form-control" placeholder="Ваша пошта" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Відгук</label>
                        <div class="col-md-9">
                            <textarea name="comment" class="form-control" placeholder="Ваш відгук" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Розділ публікації</label>
                        <div class="col-md-8">
                            <?php $chapter = $this->db->getAllData('wl_aliases');
                                foreach ($chapter as $ch) { if($ch->id > 7) {?>
                                    <input type="radio" id="<?=$ch->alias?>" name="chapter" value="<?=$ch->id?>"><label style="padding-left:5px;" for="<?=$ch->alias?>"><?=$ch->alias?></label>
                            <?php } }?>
                        </div>
                     </div>   
                    <div class="form-group">
                    	<div class="col-md-3"></div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success ">Додати відгук</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
    <!-- end col-6 -->
</div>