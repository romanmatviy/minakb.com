<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/<?= $_SESSION['alias']->alias?>/markup" class="btn btn-info btn-xs"><i class="fa fa-list-ul"></i> До всіх націнок</a>
                </div>
                <h4 class="panel-title">Націнка:</h4>
            </div>
            <div class="panel-body">
                <form action="<?= SITE_URL?>admin/<?= $_SESSION['alias']->alias?>/markup_add" method="POST" >
                    <div class="col-md-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class=><b>Від</b></label>
                                <input type="text" class="form-control" name="from" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>До</label>
                                <input type="text" class="form-control" name="to" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Націнка (коефіціент)</label>
                                <input type="text" class="form-control" name="value" value="">
                            </div>
                        </div>
                    </div>
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