<div class="col-md-3 col-sm-6">
    <div class="widget widget-stats bg-blue">
        <div class="stats-icon stats-icon-lg"><i class="fa fa-line-chart fa-fw"></i></div>
        <div class="stats-title">Курс UAH / USD</div>
        <div class="stats-number">
            <?=$_SESSION['currency']?>
        </div>
        <div class="stats-progress progress">
            <div class="progress-bar" style="width: 40.5%;"></div>
        </div>
        <div class="stats-desc">Курс валют на <?=date('d.m.Y')?> <a href="<?=SITE_URL?>admin/currency" class="btn btn-warning btn-xs">Оновити</a></div>
    </div>
</div>