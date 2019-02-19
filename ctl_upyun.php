<?php

namespace ctrl;

use KPHP\Core\Config;
use KPHP\Core\Request;
use KPHP\Cache\Factory as CacheFactory;

/**
 * 又拍云
 *
 * @link    http://git.oschina.net/vzina
 * @version $Id$
 */
class ctl_upyun extends Basic
{
    private $APISecret;
    private $bucket;
    private $saveKey;

    public function _before()
    {
        if (!Request::getParam('is_allow', 0)) {
            parent::_before();
        }
        $this->cache = CacheFactory::getInstance();

        $this->APISecret = '3bSJyuo4772IOk3qtdX16oLL7Eg=';
        $this->bucket = 'hwupload';
        $this->saveKey = '/{year}/{mon}/{day}/upload_{random32}{.suffix}';
        if (empty($this->APISecret)
            || empty($this->bucket)
            || empty($this->saveKey)
        ) {
            $this->abort(601, '配置信息有误');
        }
    }

    /**
     * FORM API 上传文件请求签名
     *
     * @return [type] [description]
     */
    public function get_upload_sign()
    {
        $nowTime = time();
        // 请求体中的请求参数
        $bodyData['bucket'] = $this->bucket;
        $bodyData['expiration'] = $nowTime + 1800;
        $bodyData['save-key'] = $this->saveKey;
        $bodyData['ext-param'] = Request::getParam('ext_param', '');

        // 请求参数键值对转换为 JSON 字符串，计算 Policy
        $policy = base64_encode(json_encode($bodyData));

        $signature = md5($policy . '&' . $this->APISecret);

        return ["code" => 0, "data" => [
            'bucket' => $this->bucket,
            'ext_param' => $bodyData['ext-param'],
            'signature' => $signature,
            'policy' => $policy,
        ], "error" => "ok"];
    }

    public function md5file()
    {
        return [
            '597555240a270a4488373886cf6280dd',
            'bca7d512c5508e7892f08947afe553c6',
            '8edc80ba78fef4aea734e2c5ba3569c8',
            '498acad6b48cfa2abb96336bd3fd5686',
            'f9cb22108edef1186c35f929744df131',
            '36d75ec153314e55bf48c6a9419d3215',
            'ba31a0569cf630960e4e0406bf75a07c',
            '9fd0b58f02b60d6e4019e05e0dd400b4'
        ];
    }
}
