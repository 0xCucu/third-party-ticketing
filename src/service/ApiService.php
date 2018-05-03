<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ApiService 
{
	public $_ci;
	private $verison;
	private $prefix;
	private $data;
    private $header;
	public function __construct()
	{
		$this->_ci = & get_instance();


		# code...
	}
	public function handle()
	{
		# code...
	}
	public function __call($name, $arguments)
	{
        $makeSign = false;
        if (isset($arguments[1])) {
            $makeSign = true;

        }
		$this->data['method'] = $name;
        $this->addHeader(config_item('api_header'));
		$header = $this->buildHeader($this->header);
        $appendsData = [];
        if (isset($arguments[2])) {
            $appendsData = $arguments[2];
        }
        $uri = $this->buildUri();
		$result = json_decode($this->request($uri, $header, $arguments[0], $makeSign, $appendsData),true);
        $this->data = array();
        $this->header = array();
        return $result;
		# code...
	}
    public function addHeader(array $header)
    {
        if ($this->header) {
            $this->header = array_merge($this->header, $header);
            return true;
        }
        $this->header = $header;
        return true;
    }
    public function generateSign($params)
    {
        ksort($params);

        $tmps = [];
        foreach ($params as $k => $v) {
            $tmps[] = $k.$v;
        }
        //放 redis
        $this->_ci->load->model('product_model');
        $app = $this->_ci->product_model->getAppSecrert('web_musikid');
        
        if (!$app) {
            return false;
        }
        $string = $app->app_secret.implode('', $tmps).$app->app_secret;
        return strtoupper(md5($string));
    
    }
	public function getVariables()
	{
		return $this->data;
	}
    public  function setVariables(array $variables)
    {
        if (!$this->data) {
            $this->data = $variables;
        } else {
            $this->data = array_merge($this->data, $variables);
        }
    }
	public function __get($value)
	{
		//设置版本
		if (!isset($this->data['version'])) {
			$this->data['version'] = $value;
			return $this;
		}
	
		$this->data['url'][] = $value;
		return $this;
	}

    public function buildUri()
    {
    	if (isset($this->data['url'])) {
    		$url = implode("/",$this->data['url']);
    		return  $url . '/'. $this->data['method'];
    	}

    	return  $this->data['method'];

    }
    public function request($uri, $header,   array $data, $makeSign, array $appendsData)
    {

        if($makeSign) {
            $build = array( );
            $build['format'] = 'json';
            $build['app_id'] = config_item('app_id');
            $build['sign_method'] = 'md5';
            $build['timestamp']  = (string)time();
            $build['sign'] = $this->generateSign($build);
            $build['data'] =$data;
            $data = null;
            $data = $build;
            
        }
        if (count($appendsData) > 0) {
            $data = array_merge($data, $appendsData);
        }
      
    	$client = new \GuzzleHttp\Client(
    		[
    			'timeout'  => 6.5,
    			'base_uri' => config_item('api_url'),
    			'headers' => $header 	
    		]
    	);
        try{
            $response = $client->request('POST', $uri, ['json' => $data]);
        }catch (Exception $e){
            print_r($header);
        }
    
    
    	$body = $response->getBody();

    	$stringBody = (string) $body;
    	return $stringBody;
    

    }
    public function buildHeader(array $api_header)
    {
 
    	$header =  preg_replace_callback(
    		'/\{([\s\S]*?)\}/', 
    		function($matches) {
    			$var = $matches[1];
    			if (!isset($this->data[$var])) {
    				return "";
    			}
    			return $this->data[$var];
    		},
    		$api_header
    	);
    	return $header;
    	
    	# code...
    }
}