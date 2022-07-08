<?php namespace Zenwalker\CommerceML\Model;

use Zenwalker\CommerceML\ORM\Collection;

class CurrencyCollection extends Collection
{
    /**
     * Get price Currency by id.
     *
     * @param $code
     * @return string
     */
    public function getCurrency($code)
    {
        return $this->get($code)->markup;
    }
}
