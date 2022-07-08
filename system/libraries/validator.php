<?php if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/libraries/validator.php
 *
 * Перевіряємо дані
 * Версія 1.1 (14.09.2017) - до setRules() додано "chaining methods"; додано getPhone(), перевірку на phone у setRules()
 */

class validator {

    private $errors = array();

    /**
     * Назначаємо правила
     *
     * @param <string> $field назва поля
     * @param <string> $data дані
     * @param <string> $rules правила, наприклад 'required|3..10'
     */
    public function setRules($field, $data, $rules)
    {
        $rules = explode('|', $rules);
        foreach ($rules as $rule) {
            if(method_exists('validator', $rule))
                $this->$rule($field, $data);
            if(strpos($rule, '..'))
            {
                $min = intval(substr($rule,0,strpos($rule,'..'))); //intval(strstr($rule, '..', true));
                $max = intval(substr(strstr($rule,'..'), 2));
                $this->valid_length($field, $data, $min, $max);
            }
        }
        return $this;
    }

    /**
     * Перевірямо якщо валідація пройдена
     *
     * @return <boolean>
     */
    public function run()
    {
        if(empty($this->errors))
            return true;
        return false;
    }

    /**
     * Перевірямо довжину даних
     *
     * @param <string> $field
     * @param <string> $data
     * @param <int> $min
     * @param <int> $max
     */
    public function valid_length($field, $data, $min, $max)
    {
        if(mb_strlen($data, 'utf-8') < $min || mb_strlen($data, 'utf-8') > $max)
        {
            array_push($this->errors, $field.' must have from '.$min.' to '.$max.' letters');
            return false;
        }
        return true;
    }

    /**
     * Перевірямо емейл-адресу
     *
     * @param <string> $field
     * @param <string> $data
     */
    public function email($field, $data)
    {
        if(!preg_match('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^', $data)){
            array_push($this->errors, '"'.$field.'" no correct');
            return false;
        }
        return true;
    }

    /**
     * Перевірямо якщо поле необхідне
     *
     * @param <string> $field
     * @param <string> $data
     */
    public function required($field, $data = '')
    {
        if($data == '')
        {
            array_push($this->errors, '"'.$field.'" обов\'зкове');
            return false;
        }
        return true;
    }

    public function password($pas1, $pas2)
    {
        if($pas1 != $pas2)
        {
            array_push($this->errors, 'Паролі не співпадають.');
            return false;
        }
        return true;
    }

    public function phone($field, $data = '')
    {
        if(!$this->getPhone($data))
        {
            array_push($this->errors, $field.': невірний формат телефону.');
            return false;
        }
        return true;
    }

    public function getPhone($phone, $template = '380000000000')
    {
        if($phone[0] == '+')
            $phone = substr($phone, 1);
        $phone = preg_replace('~[^0-9]+~','', $phone);
        if(strlen($phone) < 9 || strlen($phone) > 12)
            return false;
        elseif (strlen($phone) < 12)
        {
            $add = substr($template, 0, 12 - strlen($phone));
            $phone = $add . $phone;
        }
        if(strlen($phone) == 12 && is_numeric($phone))
            return $phone;
        return false;
    }

    /**
     * Повертаємо помилки
     *
     * @param <String> $open_tag
     * @param <String> $closed_tag
     *
     * @return <string>
     */
    public function getErrors($open_tag = '<p>', $closed_tag = '</p>')
    {
        $errors = '';
        foreach ($this->errors as $error) {
            $errors .= $open_tag.$error.$closed_tag;
        }
        return $errors;
    }
}

?>
