<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/wl_images/<?=$alias->alias?>" class="btn btn-info btn-xs">До зображень <?=$alias->alias?></a>
                </div>
                <h4 class="panel-title">Додати зміну розміру</h4>
            </div>
            <div class="panel-body">
    	        <form action="<?=SITE_URL?>admin/wl_images/add" method="POST" class="form-horizontal">
    	        	<input type="hidden" name="alias" value="<?=$alias->id?>">
    	        	<input type="hidden" name="alias_name" value="<?=$alias->alias?>">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Назва</label>
                        <div class="col-md-9">
                            <input type="text" name="name" class="form-control" value="<?=(isset($_POST['name']))?$_POST['name']:''?>" required placeholder="Назва/признчення мініатюри" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Префікс</label>
                        <div class="col-md-9">
                            <input type="text" name="prefix" class="form-control" value="<?=(isset($_POST['prefix']))?$_POST['prefix']:''?>" placeholder="Префікс мініатюри" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Тип</label>
                        <div class="col-md-9">
                            <select name="type" class="form-control">
                                <option value="1">resize</option>
                                <option value="2">preview</option>
                            </select>
                        </div>
                    </div>                
                    <div class="form-group">
                        <label class="col-md-3 control-label">Ширина</label>
                        <div class="col-md-9">
                            <input type="number" name="width" class="form-control" value="<?=(isset($_POST['width']))?$_POST['width']:''?>" required placeholder="Ширина" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Висота</label>
                        <div class="col-md-9">
                            <input type="number" name="height" class="form-control" value="<?=(isset($_POST['height']))?$_POST['height']:''?>" required placeholder="Висота" />
                        </div>
                    </div>
                    <div class="form-group">
                    	<div class="col-md-3"></div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-warning ">Додати</button>
                        </div>
                    </div>
    	        </form>
            </div>
        </div>
    </div>
</div>