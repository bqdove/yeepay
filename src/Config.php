<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/30
 * Time: 17:48
 */

namespace YeePay;
class Config {

    const account = '10000447996';

    const merchantPrivateKey = 'jj3Q1h0H86FZ7CD46Z5Nr35p67L199WdkgETx85920n128vi2125T9KY2hzv';


    const BASE_URL = 'http://o2o.yeepay.com/zgt-api/api';
    const PAY_URL = '/pay';


    public static function getAccount() {
        return self::account;
    }

    public static function getPrivateKey() {
        return self::merchantPrivateKey;
    }

    public static function getAesKey() {
        return substr(self::getPrivateKey(), 0, 16);
    }

    public static function getRemoteCode() {
        return "UTF-8";
    }

    public static function getLocaleCode() {
        return "UTF-8";
    }
}