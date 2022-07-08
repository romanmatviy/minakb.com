<?php

class balance_admin extends Controller {

    function _remap($method, $data = array())
    {
        $_SESSION['option']->paginator_per_page = 30;
        // $_GET['request'] = 'account';
        if (method_exists($this, $method)) {
            if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
            $this->index($method);
        }
    }

    public function index($id = 0)
    {
        $this->load->smodel('balance_model');
        $this->wl_alias_model->setContent();
        $_SESSION['alias']->breadcrumb = array();

        if ($id)
        {
            if($payment = $this->balance_model->getPayment_s($id)) {
                $this->load->admin_view('detal_view', ['payment' => $payment]);
                exit;
            }
            else
                $this->load->page_404(false);
        }

        $where = array();
        if(isset($_GET['status']) && $_GET['status'] == 'confirmed')
            $where['status'] = 2;
        if($user = $this->data->get('user'))
            $where['user'] = $user;
        if($from = $this->data->get('from'))
            if($from = strtotime($from))
            {
                $where['date_add'] = '>='.$from;
                if($to = $this->data->get('to'))
                    if($to = strtotime($to))
                        $where['+date_add'] = '<='.$to;
            }

        $this->load->admin_view('list_view', array('payments' => $this->balance_model->getPayment_s($where, true)));
    }

    public function save()
    {
        if ($payment_id = $this->data->post('id')) {
            $status = $this->data->post('status');
            $this->load->smodel('balance_model');

            if($status == 2)
            {
                if($payment = $this->balance_model->getPayment_s($payment_id))
                    if($payment->security_check)
                    {
                        if($debit = $this->data->post('debit'))
                            if(is_numeric($debit) && $debit > 0)
                                $payment->debit = round($debit, 3);
                        if($this->balance_model->confirmPayment($payment))
                        {
                            $total_left = $payment->debit;
                            $balance = $this->balance_model->getUserBalance($payment->user_id);
                            if($payment->debit > 0)
                                if($payments = $this->balance_model->getPayment_s(['status' => '1', 'credit' => '>0', 'action_alias' => '>0', 'action_id' => '>0']))
                                {
                                    if(is_object($payments))
                                        $payments = [$payments];
                                    foreach ($payments as $credit) {
                                        if($credit->security_check)
                                        {
                                            if($total_left >= $credit->credit || $balance >= 0)
                                            {
                                                $pay = new stdClass();
                                                $pay->alias = $_SESSION['alias']->id;
                                                $pay->id = $credit->id;
                                                $pay->cart_id = $credit->action_id;
                                                $pay->min_status = $pay->cart_status = 2;
                                                $pay->amount = $credit->credit;
                                                $pay->comment = 'Платіж поповнення #'.$payment->id;
                                                if($this->function_in_alias($credit->action_alias, '__set_Payment', $pay, true))
                                                {
                                                    $total_left -= $credit->credit;
                                                    $this->balance_model->confirmPayment($credit, 'Платіж поповнення #'.$payment->id);
                                                }
                                            }
                                            else
                                                break;
                                        }
                                    }
                                }
                        }
                    }
            }
            if($status == 3)
                $this->balance_model->cancelPayment($payment_id);
        }
        $this->redirect();
    }

    public function debit()
    {
        if($user = $this->data->post('user'))
            if ($debit = $this->data->post('debit'))
                if(is_numeric($debit) && $debit > 0)
                {
                    $data = ['status' => 2];
                    $data['user'] = $user;
                    $data['debit'] = round($debit, 3);
                    $data['action'] = "Поповнення рахунку. $".$data['debit'];
                    $data['info'] = $this->data->post('info');

                    $_SESSION['notify'] = new stdClass();
                    $this->load->smodel('balance_model');
                    if($payment_id = $this->balance_model->debit($data))
                    {
                        $total_left = $data['debit'];
                        $balance = $this->balance_model->getUserBalance($user);
                        if($payments = $this->balance_model->getPayment_s(['status' => '1', 'credit' => '>0', 'action_alias' => '>0', 'action_id' => '>0']))
                        {
                            if(is_object($payments))
                                $payments = [$payments];
                            foreach ($payments as $credit) {
                                if($credit->security_check)
                                {
                                    if($total_left >= $credit->credit || $balance >= 0)
                                    {
                                        $pay = new stdClass();
                                        $pay->alias = $_SESSION['alias']->id;
                                        $pay->id = $credit->id;
                                        $pay->cart_id = $credit->action_id;
                                        $pay->min_status = $pay->cart_status = 2;
                                        $pay->amount = $credit->credit;
                                        $pay->comment = 'Платіж поповнення #'.$payment_id;
                                        if($this->function_in_alias($credit->action_alias, '__set_Payment', $pay, true))
                                        {
                                            $total_left -= $credit->credit;
                                            $this->balance_model->confirmPayment($credit, 'Платіж поповнення #'.$payment_id);
                                        }
                                    }
                                    else
                                        break;
                                }
                            }
                        }
                        $_SESSION['notify']->success = $this->text('Рахунок поповнено успішно');
                    }
                    else
                        $_SESSION['notify']->success = $this->text('Помилка. Зверніться до адміністрації');
                }
        $this->redirect('#tabs-'.$_SESSION['alias']->alias);
    }

    // $payment array()
    public function __debit($data)
    {
        if(empty($data['user']))
            return false;
        $data['status'] = 2;
        $data['debit'] = round($data['debit'], 3);
        if(empty($data['action']))
            $data['action'] = "Поповнення рахунку. $".$data['debit'];

        $this->load->smodel('balance_model');
        return $this->balance_model->debit($data);
    }

    // $payment array()
    public function __editPayment($data)
    {
        if(empty($data['id']))
            return false;
        $this->load->smodel('balance_model');
        if($payment = $this->balance_model->getPayment_s($data['id']))
            if($payment->security_check)
                return $this->balance_model->editPayment($payment, $data);
        return false;
    }

    public function __confirmPayment($payment_id = 0)
    {
        $this->load->smodel('balance_model');
        if($payment = $this->balance_model->getPayment_s($payment_id))
            if($payment->security_check)
            {
                if($payment->action_alias && $payment->action_id && $payment->credit)
                {
                    if($payment->client_balance_now >= 0)
                    {
                        if($this->balance_model->confirmPayment($payment))
                        {
                            $pay = new stdClass();
                            $pay->alias = $_SESSION['alias']->id;
                            $pay->id = $payment->id;
                            $pay->cart_id = $payment->action_id;
                            $pay->min_status = 2;
                            $pay->cart_status = 3;
                            $pay->amount = $payment->credit;
                            $pay->comment = '';
                            $this->function_in_alias($payment->action_alias, '__set_Payment', $pay, true);
                        }
                    }
                }
                else
                    $this->balance_model->confirmPayment($payment);
                return true;
            }
        return false;
    }

    public function __cancelPayment($payment_id = 0)
    {
        $this->load->smodel('balance_model');
        return $this->balance_model->cancelPayment($payment_id);
    }

    public function __sidebar($alias)
    {
        $alias->counter = $this->db->getCount($_SESSION['service']->table, array('status' => 1, 'debit' => '>0'));
        return $alias;
    }

    public function __tab_profile($user_id)
    {   
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 30;
        $this->load->smodel('balance_model');
        ob_start();
        $user = $this->db->getAllDataById('wl_users', $user_id);
        $user->balance_correct_sign = $this->balance_model->getUserSign($user->balance, $user_id);
        if (empty($user->balance))
            $user->balance = 0;
        $user->balance_security_check = ($user->balance_correct_sign == $user->balance_sign || empty($user->balance_sign)) ? true : false;
        $this->load->view('admin/__tab_profile', array('user' => $user, 'payments' => $this->balance_model->getPayment_s(['user' => $user_id], true)));
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = 'Баланс / Звірка';
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }

}
?>