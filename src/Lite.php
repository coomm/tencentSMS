<?php
namespace PhalApi\tencentqcloudsms;


class Lite {
	//应用Id（必填）
	private $smsSdkAppi="";
	//SecretId（必填）
	private $secretId="";
	//SecretKey（必填）
	private $secretKey="";
	//短信签名（必填）
	private $sign="";
	//接入地域
	private $host="sms.ap-guangzhou.tencentcloudapi.com";
	//Api接口
	private $action="SendSms";
	//接入地域
	private $region="ap-guangzhou";
	//签名方法
	private $signatureMethod="HmacSHA256";	
	//语言
	private $language='zh-CN';
	
    public function SendSms($params) {
        try {
			//模板Id
			$req['TemplateID']=$params['templateID'];
			//接收方手机号
			$req['PhoneNumberSet.0']='+86'.$params['phoneNumberSet0'];//
			//模板参数
			$req['TemplateParamSet.0']=$params['code'];			
			
			if($params['templateID']=='496699'){
				$req['TemplateParamSet.1']='1';
			}
			return $resp = $this->send($req,"POST");
			
		}
		catch(Exception $e) {
			echo $e;
		}
    }
	/**
     * makeSignPlainText
     * 生成拼接签名源文字符串
     * @param  array $requestParams  请求参数
     * @param  string $requestMethod 请求方法
     * @param  string $requestHost   接口域名
     * @param  string $requestPath   url路径
     * @return
     */
    public function makeSignPlainText($requestParams,$requestMethod = 'GET', $requestHost,$requestPath = '/v2/index.php')
    {

        $url = $requestHost . $requestPath;

        // 取出所有的参数
        $paramStr = $this->buildParamStr($requestParams, $requestMethod);

        $plainText = $requestMethod . $url . $paramStr;

        return $plainText;
    }

    /**
     * buildParamStr
     * 拼接参数
     * @param  array $requestParams  请求参数
     * @param  string $requestMethod 请求方法
     * @return
     */
    protected function buildParamStr($requestParams, $requestMethod = 'GET')
    {
        $paramStr = '';
        ksort($requestParams);
        $i = 0;
        foreach ($requestParams as $key => $value)
        {
            if ($key == 'Signature')
            {
                continue;
            }
            // 排除上传文件的参数
            if ($requestMethod == 'POST' && substr($value, 0, 1) == '@') {
                continue;
            }
            // 把 参数中的 _ 替换成 .
            if (strpos($key, '_'))
            {
                $key = str_replace('_', '.', $key);
            }

            if ($i == 0)
            {
                $paramStr .= '?';
            }
            else
            {
                $paramStr .= '&';
            }
            $paramStr .= $key . '=' . $value;
            ++$i;
        }

        return $paramStr;
    }
	/**
     * @throws TencentCloudSDKException
     */
    private function sign($secretKey, $signStr, $signMethod)
    {
        $signMethodMap = ["HmacSHA1" => "sha1", "HmacSHA256" => "sha256"];
        if (!array_key_exists($signMethod, $signMethodMap)) {
            throw new Exception("signMethod invalid", "signMethod only support (HmacSHA1, HmacSHA256)");
        }
        $signature = base64_encode(hash_hmac($signMethodMap[$signMethod], $signStr, $secretKey, true));		
		return $signature;
    }
    
	/**
     * send
     * 发起请求
     * @param  array  $paramArray    请求参数
     * @param  string $secretId      secretId
     * @param  string $secretKey     secretKey
     * @param  string $requestMethod 请求方式，GET/POST
     * @param  string $requestHost   接口域名
     * @param  string $requestPath   url路径
     * @return
     */
    public function send($paramArray, $requestMethod)
    {

        if(!isset($paramArray['SecretId'])){
            $paramArray['SecretId'] = $this->secretId;
		}
        if (!isset($paramArray['Nonce'])){
            $paramArray['Nonce'] = rand(1, 65535);
		}
        if (!isset($paramArray['Timestamp'])){
            $paramArray['Timestamp'] = time();
		}
		if (!isset($paramArray['Version'])){
            $paramArray['Version'] = "2019-07-11";
		}
		if (!isset($paramArray['SmsSdkAppid'])){
            $paramArray['SmsSdkAppid'] = $this->smsSdkAppi;
		}
		if (!isset($paramArray['Action'])){
            $paramArray['Action'] = $this->action;
		}
		if (!isset($paramArray['Region'])){
            $paramArray['Region'] = $this->region;
		}
		if (!isset($paramArray['SignatureMethod'])){
            $paramArray['SignatureMethod'] = $this->signatureMethod;
		}
		if (!isset($paramArray['Sign'])){
            //$paramArray['Sign'] = $this->sign;
		}
		if (!isset($paramArray['Language'])){
            $paramArray['Language'] = $this->language;
		}
        
        $signMethod = 'HmacSHA256';
        if (isset($paramArray['SignatureMethod']) && $paramArray['SignatureMethod'] == "HmacSHA256"){
            $signMethod= 'HmacSHA256';
        }
        
        $plainText = $this->makeSignPlainText($paramArray,$requestMethod, $this->host, "/");
		
        $paramArray['Signature'] = $this->sign($this->secretKey,$plainText,  $signMethod);

        $url = 'https://' . $this->host . "/";
		
		
        $ret = $this->sendRequest($url, $paramArray, $requestMethod);

        return $ret;
    }
	/**
     * sendRequest
     * @param  string $url        请求url
     * @param  array  $paramArray 请求参数
     * @param  string $method     请求方法
     * @return
     */
    protected function sendRequest($url, $paramArray,$method = 'POST')
    {

        $ch = curl_init();
		$headers=array();
        if ($method == 'POST')
        {
            $paramArray = is_array( $paramArray ) ? http_build_query( $paramArray ) : $paramArray;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArray);
			
        }
        else
        {
            $url .= '?' . http_build_query($paramArray);
        }
		$headers["Host"] = $this->host;
		$headers["Content-Type"] = "application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HEADER, $headers);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (false !== strpos($url, "https")) {
            // 证书
            // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }
        $resultStr = curl_exec($ch);        

        $result = json_decode($resultStr, true);
        if (!$result)
        {
            return $resultStr;
        }
        return $result;
    }
	
}