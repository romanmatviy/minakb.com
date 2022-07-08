<div class="row">
    <div class="panel panel-inverse">
        <div class="panel-heading">
        	<div class="panel-heading-btn">
        		<a href="<?=SITE_URL?>admin/wl_ntkd" class="btn btn-success btn-xs"><i class="fa fa-list"></i> До всіх записів</a>
        	</div>
            <h4 class="panel-title">Загальні Meta-теги</h4>
        </div>
        <div class="panel-body panel-form">
            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_ntkd/save_global_metatags" method="POST">
                <div class="form-group">
                	<div class="col-md-12">
                		<textarea name="global_MetaTags" placeholder="Загальні Meta-теги" rows="30" class="form-control"><?=$_SESSION['option']->global_MetaTags?></textarea>
                	</div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label"></label>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-sm btn-success">Зберегти</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>