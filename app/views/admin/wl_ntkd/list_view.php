<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
            		<a href="<?=SITE_URL.'admin/wl_ntkd/'.$alias->alias?>/edit" class="btn btn-xs btn-info"><i class="fa fa-at"></i> Головна сторінка</a>
                    <a href="<?=SITE_URL?>admin/wl_ntkd/<?=$alias->alias?>/seo_robot" class="btn btn-success btn-xs"><i class="fa fa-line-chart"></i> SEO робот <?=$alias->alias?></a>
            	</div>
                <h4 class="panel-title">Наявні адреси <strong><?=($alias->admin_ico) ? '<i class="fa '.$alias->admin_ico.'"></i>' : ''?> <?=$alias->alias?></strong>:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>id content</th>
                                <th>Адреса</th>
                                <th>Назва</th>
								<th>Остання зміна<?=($_SESSION['language']) ? ' ('.$_SESSION['language'].')' : ''?></th>
                                <th>Частота оновлення<?=($_SESSION['language']) ? ' ('.$_SESSION['language'].')' : ''?></th>
                                <th>Пріорітетність<?=($_SESSION['language']) ? ' ('.$_SESSION['language'].')' : ''?> [0..1]</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6"><center>
                                    <a href="<?=SITE_URL.'admin/wl_ntkd/'.$alias->alias?>/edit" class="btn btn btn-info m-r-15"><i class="fa fa-at"></i> Головна сторінка <strong><?=$alias->alias?></strong></a>
                                    <a href="<?=SITE_URL?>admin/wl_ntkd/<?=$alias->alias?>/seo_robot" class="btn btn-success btn"><i class="fa fa-line-chart"></i> SEO робот на <strong><?=$alias->alias?></strong></a>
                                </center></td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <form class="col-md-8 m-auto pull-none">
                                        <div class="col-md-9">
                                            <input type="text" name="name" class="form-control" placeholder="Пошук по назві" value="<?=$this->data->get('name')?>">
                                        </div>
                                        <button type="submit" class="btn btn btn-info col-md-3"><i class="fa fa-search" aria-hidden="true"></i> Пошук</button>
                                    </form>
                                </td>
                            </tr>
                        <?php if($articles) foreach ($articles as $a) { ?>
							<tr>
								<td><a href="<?=SITE_URL.'admin/wl_ntkd/'.$alias->alias.'/'.$a->content?>"><?=$a->content?></a></td>
                                <td><a href="<?=SITE_URL.'admin/wl_ntkd/'.$alias->alias.'/'.$a->content?>"><?=$a->link ?? $alias->alias?></a></td>
                                <td><?=$a->name?></td>
								<td><?=($a->time)?date('d.m.Y H:i', $a->time):'Не індексовано'?></td>
                                <?php if($a->priority < 0) echo('<td colspan="2">Сторінка не індексується</td>'); else { ?>
                                    <td><?=$a->changefreq?></td>
                                    <td><?=$a->priority/10?></td>
                                <?php } ?>
							</tr>
						<?php } ?>
                        </tbody>
                    </table>
                    <?php $this->load->library('paginator');
                    echo $this->paginator->get(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
