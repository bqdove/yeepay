# 易宝掌柜通接口

## 概述

易宝支付，掌柜通分账创建

## 运行环境
- PHP 5.3+
- cURL extension

提示：

- Ubuntu下可以使用apt-get包管理器安装php的cURL扩展 `sudo apt-get install php5-curl`

## 安装方法

1. 如果您通过composer管理您的项目依赖，可以在你的项目根目录运行：

        $ composer require yeepay/sdk-php

   或者在你的`composer.json`中声明对yeepay/sdk-php for PHP的依赖：

        "require": {
            "yeepay/sdk-php": "~1.0"
        }

   然后通过`composer install`安装依赖。composer安装完成后，在您的PHP代码中引入依赖即可：

        require_once __DIR__ . '/vendor/autoload.php';


3. 下载SDK源码，在您的代码中引入SDK目录下的`autoload.php`文件：

        require_once '/path/to/yeepay/autoload.php';

## 快速使用

### 创建支付

### 
