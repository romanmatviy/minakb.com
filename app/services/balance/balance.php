<?php

class balance extends Controller {

    function _remap($method, $data = array())
    {
        if($this->userIs() && !isset($_SESSION['user']->balance))
            $this->__user_login();
        $_SESSION['option']->paginator_per_page = 30;
        if (method_exists($this, $method)) {
            if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
            $this->index($method);
        }
    }

    public function index()
    {
        if($this->userIs())
        {
            $_SESSION['alias']->name = 'Кабінет користувача';
            $_SESSION['alias']->title = $_SESSION['user']->name.'. Особистий рахунок';

            $this->load->smodel('balance_model');
            $_SESSION['user']->balance = round($this->balance_model->getUserBalance(), 2);
            
            $where = array('user' => $_SESSION['user']->id);
            if(isset($_GET['status']) && $_GET['status'] == 'confirmed' || isset($_GET['save']))
                $where['status'] = 2;
            if($from = $this->data->get('from'))
            {
                if($from = strtotime($from))
                    $where['date_add'] = '>='.$from;
            }
            if($to = $this->data->get('to'))
            {
                if($to = strtotime($to))
                {
                    if(isset($where['date_add']))
                        $where['+date_add'] = '<='.$to;
                    else
                        $where['date_add'] = '<='.$to;
                }
            }

            $this->db->select('s_balance as p', '*', $where);
            $this->db->order('date_add DESC');
            if(isset($_GET['save']))
            {
                $this->load->library('PHPExcel');

                // Set properties
                $this->phpexcel->getProperties()->setCreator(SITE_NAME);
                $this->phpexcel->getProperties()->setLastModifiedBy("setCreator");
                $this->phpexcel->getProperties()->setTitle("Баланс звірка");

                // Add some data
                $this->phpexcel->setActiveSheetIndex(0);
                $this->phpexcel->getActiveSheet()->SetCellValue('A1', 'Баланс звірка '.$_SESSION['user']->name);

                $this->phpexcel->getActiveSheet()->SetCellValue('A3', 'Заявка');
                $this->phpexcel->getActiveSheet()->SetCellValue('B3', 'Дебет');
                $this->phpexcel->getActiveSheet()->SetCellValue('C3', 'Кредит');
                $this->phpexcel->getActiveSheet()->SetCellValue('D3', 'Залишок');
                $this->phpexcel->getActiveSheet()->SetCellValue('E3', 'Інформація');

                $this->phpexcel->getActiveSheet()->setTitle('Баланс звірка');

                if($payments = $this->db->get('array'))
                {
                    $productCount = 4;
                    foreach ($payments as $p)
                    {
                        $this->phpexcel->getActiveSheet()->SetCellValue('A'.$productCount, date('d.m.Y H:i', $p->date_add));
                        $this->phpexcel->getActiveSheet()->SetCellValue('B'.$productCount, $p->debit);
                        $this->phpexcel->getActiveSheet()->SetCellValue('C'.$productCount, $p->credit);
                        $this->phpexcel->getActiveSheet()->SetCellValue('D'.$productCount, $p->balance);
                        $this->phpexcel->getActiveSheet()->SetCellValue('E'.$productCount, $p->action);
                        $productCount++;
                    }
                }

                $alias = SITE_NAME .' '. $this->data->latterUAtoEN(trim($_SESSION['user']->name));
                $objWriter = new PHPExcel_Writer_Excel2007($this->phpexcel);
                // $objWriter->save(str_replace('.php', '.xls', __FILE__));
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header("Content-Disposition: attachment; filename=\"{$alias}.xlsx\"");
                header("Cache-Control: max-age=0");
                $objWriter->save('php://output');
            }
            else
            {
                if($_SESSION['option']->paginator_per_page > 0)
                {
                    $start = 0;
                    if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
                        $_SESSION['option']->paginator_per_page = $_GET['per_page'];
                    if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
                        $start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
                    $this->db->limit($start, $_SESSION['option']->paginator_per_page);
                }
                $_SESSION['option']->paginator_total = $this->db->get('count', false);
                $this->load->profile_view('balance_view', array('payments' => $this->db->get('array')));
            }
        }
        else
            $this->redirect('login');
    }

    public function notify()
    {
        if ($this->userIs()) {
            if ($amount = $this->data->post('amount'))
                if(is_numeric($amount) && $amount > 0)
                {
                    $data = [];
                    // for adatrade
                    if(!empty($_SESSION['currency']['USD']))
                        $data['debit'] = round($amount / $_SESSION['currency']['USD'], 3);
                    else
                        $data['debit'] = $amount;
                    $data['action'] = "Заявка. ".$amount.' грн';
                    $data['info'] = $this->data->post('info');

                    $_SESSION['notify'] = new stdClass();
                    $this->load->smodel('balance_model');
                    if($this->balance_model->debit($data))
                        $_SESSION['notify']->success = $this->text('Заявку про поповнення надіслано успішно');
                    else
                        $_SESSION['notify']->success = $this->text('Помилка. Зверніться до адміністрації');
                }
            $this->redirect();
        }
        $this->redirect('login');
    }

    public function __get_Payment($pay)
    {
        $data = [];
        $data['credit'] = $pay->total;
        $data['action'] = 'Замовлення #'.$pay->id;
        $data['action_alias'] = $pay->wl_alias;
        $data['action_id'] = $pay->id;

        $_SESSION['notify'] = new stdClass();
        $this->load->smodel('balance_model');
        if($id = $this->balance_model->credit($data))
        {
            $payment = new stdClass();
            $payment->alias = $_SESSION['alias']->id;
            $payment->id = $id;
            $payment->cart_id = $pay->id;
            $payment->amount = 0;
            $payment->comment = 'Платіж списання #'.$id;
            $this->function_in_alias($pay->wl_alias, '__set_Payment', $payment, true);

            $_SESSION['user']->balance = $this->balance_model->getUserBalance();
            $this->redirect($pay->return_url);
        }
        $this->redirect();
    }

    public function __user_login()
    {
        if($this->userIs())
        {
            $this->load->smodel('balance_model');
            $_SESSION['user']->balance = $this->balance_model->getUserBalance();
        }
    }

    public function __get_info($payment_id=0)
    {
        if($payment_id)
        {
            $this->load->smodel('balance_model');
            if($pay = $this->balance_model->getPayment_s($payment_id))
            {
                $this->wl_alias_model->setContent();
                $pay->name = $_SESSION['alias']->name;
                $pay->info = 'Дебет: <b>$'.$pay->debit.'</b> Кредит: <b>$'.$pay->credit.'</b> </p>';
                $status = "Очікує обробки";
                switch ($pay->status) {
                    case 2:
                        $status = "Підтверджено";
                        break;
                    case 3:
                        $status = "Скасовано";
                        break;
                    case 4:
                        $status = "Підтверджено => Скасовано";
                        break;
                }
                $date_edit = $pay->date_edit ? 'від <b>'.date('d.m.Y H:i', $pay->date_edit).'</b>' : '';
                $pay->info .= '<p>Статус оплати: <b>'.$status.'</b> '.$date_edit.' </p>';
                // $pay->info .= '<p>Інформація: <b>'.$pay->info.'</b> </p>';
                $pay->info .= '<p>Заявку на оплату сформовано: <b>'.date('d.m.Y H:i', $pay->date_add).'</b>';
                $pay->admin_link = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$pay->id;
                return $pay;
            }
        }
        return false;
    }

    public function security_block()
    {
        $this->wl_alias_model->setContent(-1);
        $this->load->page_view('security_block_view');
    }

}
?>