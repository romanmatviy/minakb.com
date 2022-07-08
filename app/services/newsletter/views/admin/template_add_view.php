<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">До всіх шаблонів</a>
            	</div>
                <h4 class="panel-title">Додати шаблон розсилки</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/insert" method="POST" class="form-horizontal">
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Назва шаблону </label>
                        <div class="col-md-9"> <input type="text" name="name" required class="form-control"> </div>
                    </div>
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Тема листа </label>
                        <div class="col-md-9"> <input type="text" name="theme" required class="form-control"> </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-plus"></i> Додати</button>
                        </div>
	                </div>
	            </form>
            </div>
        </div>
    </div>
</div>