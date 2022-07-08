<?php 

/**
 * facebook_feed xml generator
 * 20.02.2020 Ostap Matskiv fix for nonEnd groups
 * 27.11.2019 Ostap Matskiv
 */
class facebook_feed
{
	public $mode = 'echo';
	
	function createXml($products, $groups)
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
				 <channel>
		  		<title>'.SITE_NAME.'</title>
				<description><![CDATA['.SITE_NAME.']]></description>
				<link>'.SITE_URL.'</link>';
		if($products)
			foreach ($products as $product) {
				$xml .= "<item>
				   <g:id>{$product->id}</g:id>
				   <g:title><![CDATA[{$product->name}]]></g:title>
				   <g:link>".SITE_URL.$product->link."</g:link>";
				if(!empty($product->options))
				{
					$options = [];
					foreach ($product->options as $key => $value) {
						$options[] = $key .' '. $value;
					}
					$xml .= "<g:description><![CDATA[".implode('<br>', $options)."]]></g:description>";
				}
				if(!empty($product->images))
					foreach ($product->images as $i => $image) {
						if($i == 0)
							$xml .= "<g:image_link>{$image}</g:image_link>";
						else
							$xml .= "<g:additional_image_link>{$image}</g:additional_image_link>";
					}
				   
				if($_SESSION['option']->useAvailability)
				{
					if($product->availability > 0)
						$xml .= "<g:availability>in_stock</g:availability>";
					else
						$xml .= "<g:availability>out_of_stock</g:availability>";
				}
				else
				{
					if($product->availability == 1)
						$xml .= "<g:availability>in_stock</g:availability>";
					else
						$xml .= "<g:availability>out_of_stock</g:availability>";
				}
				$xml .= "<g:condition>new</g:condition>
				   <g:price>{$product->price} UAH</g:price>
				   <g:mpn>{$product->article_show}</g:mpn>";
				if(!empty($product->group) && $groups)
				{
					$parents = [];
					$parent = $product->group;
					$find = true;
					while ($parent > 0 && $find) {
						$find = false;
						foreach ($groups as $group) {
							if($group->id == $parent)
							{
								$parents[] = $group->name;
								$parent = $group->parent;
								$find = true;
								break;
							}
						}
					}
					if(!empty($parents))
					{
						$parents = array_reverse ($parents);
						$xml .= "<g:product_type>".implode(' &gt; ', $parents)."</g:product_type>";
					}
				}
				$xml .= "<g:google_product_category>Hardware &gt; Hardware Accessories &gt; Hardware Fasteners</g:google_product_category>
				   <g:unit_pricing_base_measure>1 ct</g:unit_pricing_base_measure>
				   <g:unit_pricing_measure>100 ct</g:unit_pricing_measure>
				   <g:multipack>{$product->box}</g:multipack>
				</item>";
			}
		$xml .= "</channel>
			</rss>";
		if($this->mode == 'echo')
		{
			header('Content-Type: text/xml; charset=UTF-8');
			echo($xml);
			exit;
		}
		return $xml;
	}

}

?>