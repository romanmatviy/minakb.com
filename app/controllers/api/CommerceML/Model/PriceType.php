<?php namespace Zenwalker\CommerceML\Model;

use Zenwalker\CommerceML\ORM\Model;

class PriceType extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var float
     */
    public $markup;

    /**
     * @param SimpleXMLElement [$xmlPriceType]
     * @return \Zenwalker\CommerceML\Model\PriceType
     */
    public function __construct($xmlPriceType = null)
    {
        if (! is_null($xmlPriceType)) {
            $this->loadImport($xmlPriceType);
        }
    }

    /**
     * @param SimpleXMLElement [$xmlPriceType]
     * @return void
     */
    private function loadImport($xmlPriceType)
    {
        $this->id = (string) $xmlPriceType->Ид;

        $this->type = (string) $xmlPriceType->Наименование;

        $this->currency = (string) $xmlPriceType->Валюта;
        
        $this->markup = (float) $xmlPriceType->Наценка;
    }
}
