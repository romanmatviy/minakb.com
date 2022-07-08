<?php 

/**
 * bing translator API v3
 * need Azure account
 */
class bing_translator
{
	private $subscription_key;
	private $subscription_region = 'westeurope';
	private $endpoint = 'https://api-eur.cognitive.microsofttranslator.com/';
	private $path = "translate?api-version=3.0";
	
	function __construct($cfg)
    {
    	$this->subscription_key = $cfg['subscription_key'];
    	if(!empty($cfg['subscription_region']))
    		$this->subscription_region = $cfg['subscription_region'];
    	if(!empty($cfg['endpoint']))
    		$this->endpoint = $cfg['endpoint'];
    	if(!empty($cfg['path']))
    		$this->path = $cfg['path'];
    }

    public function translate($text, $from = 'en', $to = '')
    {
    	if(empty($to) && !empty($_SESSION['language']))
    		$to = $_SESSION['language'];
    	if(empty($to) || empty($from))
    		return $text;
    	$text = trim($text);

    	$params = "&from={$from}&to={$to}&category=generalnn";

    	$requestBody = array (
		    array (
		        'Text' => $text,
		    ),
		);
		$content = json_encode($requestBody);

    	$headers = "Content-type: application/json\r\n" .
        "Content-length: " . strlen($content) . "\r\n" .
        "Ocp-Apim-Subscription-Key: {$this->subscription_key}\r\n" .
        "Ocp-Apim-Subscription-Region: {$this->subscription_region}\r\n" ;

	    // NOTE: Use the key 'http' even if you are making an HTTPS request. See:
	    // http://php.net/manual/en/function.stream-context-create.php
	    $options = array (
	        'http' => array (
	            'header' => $headers,
	            'method' => 'POST',
	            'content' => $content
	        )
	    );
	    
	    $context  = stream_context_create ($options);
	    if($result = file_get_contents ($this->endpoint . $this->path . $params, false, $context))
	    {
	    	$json = json_decode($result);
	    	return $json[0]->translations[0]->text;
	    }

	    return $text;
    }

}

?>