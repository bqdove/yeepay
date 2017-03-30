<?php

namespace YeePay;

use YeePay\Exceptions\Exception;
use YeePay\Http\ApiRequest;
use YeePay\Util\Util;

class Charge extends ApiRequest
{

    const BASE_URL = 'http://o2o.yeepay.com/zgt-api/api';
    const PAY_URL = '/pay';
    static $payNeedRequestHmac = array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "bankid", 10 => "period", 11 => "memo");
    static $payNeedResponseHmac = array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "externalid", 4 => "amount", 5 => "payurl");
    static $payRequest = array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "bankid", 10 => "period", 11 => "memo", 12 => "payproducttype", 13 => "userno", 14 => "ip", 15 => "cardname", 16 => "idcard", 17 => "bankcardnum",18=> "mobilephone",19 => "orderexpdate");
    static $payMustFillRequest = ["requestid","amount","callbackurl"];
    static $needCallbackHmac = array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "notifytype", 4 => "externalid", 5 => "amount", 6 => "cardno");

    public static function create($params = null)
    {
        $obj = new ApiRequest();
        $obj->setUrl(self::BASE_URL . self::PAY_URL);
        $obj->setPost($params,self::$payNeedRequestHmac,self::$payRequest);
        $obj->setNeedRequest(self::$payRequest);
        $obj->setNeedRequestHmac(self::$payNeedRequestHmac);
        $obj->setNeedResponseHmac(self::$payNeedResponseHmac);

        $response = $obj->send();
        var_dump($response);exit;
    }


    public static function callBack() {

        if ( !isViaArray($_REQUEST, "data") ) {

            throw new Exception("callback param data is null.");
        }


        $data = $_REQUEST["data"];

        $customernumber = Config::getAccount();
        $keyForHmac = Config::getPrivateKey();
        $keyForAES = Config::getAesKey();

        $responseData = Util\Util::getDeAes($data, $keyForAES);
        $result = json_decode($responseData, true);

//进行UTF-8->GBK转码
        $resultLocale = array();
        foreach ( $result as $rKey => $rValue ) {

            $resultLocale[$rKey] = iconv(getRemoteCode(), getLocaleCode(), $rValue);
        }


        if ( "1" != $result["code"] ) {

            throw new Exception("response error, errmsg = [" . $resultLocale["msg"] . "], errcode = [" . $resultLocale["code"] . "].", $result["code"]);
        }

        if ( array_key_exists("customError", $result)
            && "" != $result["customError"] ) {

            throw new Exception("response.customError error, errmsg = [" . $resultLocale["customError"] . "], errcode = [" . $resultLocale["code"] . "].", $result["code"]);
        }

        if ( $result["customernumber"] != $customernumber ) {

            throw new Exception("customernumber not equals, request is [" . $customernumber . "], response is [" . $result["customernumber"] . "].");
        }

//验证返回签名
        $hmacGenConfig = self::$needCallbackHmac;
        $hmacData = array();
        foreach ( $hmacGenConfig as $hKey => $hValue ) {

            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if ( Util::isViaArray($result, $hValue) && $result[$hValue] ) {

                $v = $result[$hValue];
            }

            //取得对应加密的明文的值
            $hmacData[$hKey] = $v;

        }
        $hmac = Util::getHmac($hmacData, $keyForHmac);

        if ( $hmac != $result["hmac"] ) {

            throw new Exception("hmac not equals, response is [" . $result["hmac"] . "], gen is [" . $hmac . "].");
        }
        return $resultLocale;
    }

    public static function createGo($params=null) {

    }

}
