<div class="col-md-4">
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <div class="panel-heading-btn">
                <a href="<?=SITE_URL?>admin/paypal" class="btn btn-xs btn-success"><i class="fa fa-repeat"></i> До всіх квитанцій</a>
            </div>
            <h4 class="panel-title"><i class="fa fa-paypal"></i> Оплати PayPal</h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered nowrap" width="100%">
                    <?php 
                    $where = array();
                    $where['status'] = '!new';
                    $where['date_edit'] = '>='.strtotime('today');
                    $this->db->select('s_paypal as p', '*', $where);
                    $this->db->join('wl_aliases', 'alias as cart_alias_name', '#p.cart_alias');
                    $this->db->order('id DESC');
                    $this->db->limit(7);
                    $payments =  $this->db->get('array');
                    if(!empty($payments)) { ?>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Дата</th>
                                <th>Сума</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($payments as $pay){ ?>
                            <tr>
                                <td><a href="<?=SITE_URL.'admin/'.$pay->cart_alias_name.'/'.$pay->cart_id?>" class="btn btn-primary btn-xs">До замовлення #<?=$pay->cart_id?></a>
                                <td><?=date("d.m.Y H:i", $pay->date_edit)?></td>
                                <td><b><?=$pay->amount?> €</b></td>
                                <td><a href="<?=SITE_URL.'admin/paypal/'.$pay->id?>"><?=$pay->status?></a></td>
                            </tr>
                    <?php } } else { ?>
                        <tbody>
                        <tr>
                            <td colspan="4">Оплати за <?=date('d.m.Y')?> відсутні</td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>