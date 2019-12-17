# 腾讯云短信扩展


## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "phalapi/tencentsms":"dev-master"
```

然后执行```composer update```。
## 配置
在vendor/phalapi/tencentsms/src/Lite.php修改短信配置
```php
//应用Id
private $smsSdkAppi="123456";
//SecretId
private $secretId="123456";
//SecretKey
private $secretKey="123456";
//接入地域
private $host="sms.ap-guangzhou.tencentcloudapi.com";
//Api接口
private $action="SendSms";
//接入地域
private $region="ap-guangzhou";
//签名方法
private $signatureMethod="HmacSHA256";
//短信签名
private $sign="123456";
//语言
private $language='zh-CN';
```
## 注册
在/path/to/phalapi/config/di.php文件中，注册：  
```php
$di->sms = function() {
	return new \PhalApi\tencentsms\Lite();
};
```

## 使用
1. 发送模板短信
```php
//模板参数
$params['code']='2046';
//模板Id
$params['templateID']='496702';
//接收方手机号
$params['phoneNumberSet0']='18002595257';
\PhalApi\DI()->sms->SendSms($params);
```
