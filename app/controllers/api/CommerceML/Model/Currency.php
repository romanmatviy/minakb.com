<?php namespace Zenwalker\CommerceML\Model;

use Zenwalker\CommerceML\ORM\Model;

class Currency extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $code;

    /**
     * @var float
     */
    public $markup;

    /**
     * @param SimpleXMLElement [$xmlCurrency]
     * @return \Zenwalker\CommerceML\Model\Currency
     */
    public function __construct($xmlCurrency = null)
    {
        if (! is_null($xmlCurrency)) {
            $this->loadImport($xmlCurrency);
        }
    }

    /**
     * @param SimpleXMLElement [$xmlCurrency]
     * @return void
     */
    private function loadImport($xmlCurrency)
    {
        $this->id = (string) $xmlCurrency->Ид;

        $this->code = (string) $xmlCurrency->Наименование;

        $this->markup = (float) $xmlCurrency->Курс;
    }
}
