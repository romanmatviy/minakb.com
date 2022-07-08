<div class="row">
	<div class="col-md-8 ui-sortable">
        <div class="panel panel-inverse">
	        <div class="panel-heading">
	        	<div class="panel-heading-btn">
	        		<a href="<?=SITE_URL?>admin/wl_sitemap" class="btn btn-success btn-xs"><i class="fa fa-refresh"></i> До всіх записів</a>
	        	</div>
	            <h4 class="panel-title">Додати 301 переадресацію</h4>
	        </div>
	        <div class="panel-body panel-form">
	            <form class="form-horizontal form-bordered" action="<?=SITE_URL?>admin/wl_sitemap/save_add_redirect" method="POST">
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Адреса звідки</label>
	                    <div class="col-md-9 input-group">
                    		<span class="input-group-addon"><?=SITE_URL?></span>
	                		<input name="from" value="<?=$this->data->re_post('from')?>" class="form-control" required="required">
						</div>
	                </div>
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Направити до</label>
                        <div class="col-md-9 input-group">
                    		<span class="input-group-addon"><?=SITE_URL?></span>
	                		<input name="to" value="<?=$this->data->re_post('to')?>" class="form-control">
						</div>
	                </div>
	                <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success">Додати</button>
                        </div>
                    </div>
	            </form>
	        </div>
	    </div>
	</div>
</div>