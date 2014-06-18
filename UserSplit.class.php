<?php
/**
 * Created by taopeng@meilishuo.com.
 * User: 2014
 * Date: 14-6-17
 * Time: 下午10:13
 */

class UserSplit {
    # 用户切分的最大区间
    private static $hashResidual = 100;
    # 全局唯一的配置文件路径
    private static $yamlFile = "user_splitter.json";
    # 配置文件内容
    private static $conf;
    # singleton
    private static $instance;

    # 命中用户切分的字符串，类似 A1_B3_C1
    private $split_string = "";

    function __construct() {
        # yaml related: http://www.php.net/manual/en/yaml.examples.php
        # $yaml = yaml_parse(file_get_contents(self::$yamlFile));
        $yaml = json_decode(file_get_contents(self::$yamlFile), true);
        self::$conf = self::mapYaml2Kv($yaml);
    }

    /**
     * 将yaml的规范json转化为相对简单的kv json
     * res['A'] = {'hashcode': xxxx, 'segment':{('B1', 0, 9), ('B2', 10, 19)}}
     * TODO: 进行严格的正确性检查。segment命名不冲突，切分区间顺序不重叠。
     */
    private static function mapYaml2Kv($yaml) {
        $res = array();

        foreach ($yaml as $layer) {
            $layerName = $layer['layer'];
            $hashCode = $layer['hashcode'];
            $res[$layerName] = array(
				     'hashcode' => $hashCode,
				     'segment' => array());
            foreach ($layer['segment'] as $segment) {
                $segName = $segment['name'];
                $start = $segment['start'];
                $end = $segment['end'];
                $res[$layerName]['segment'][] = array($segName, $start, $end);
            }
        }


        return $res;
    }

    public static function instance() {
        is_null(self::$instance) && self::$instance = new self();
        return self::$instance;
    }

    public function getSplitString() {
        return $this->split_string;
    }

    public function split($tag, $layerName) {

        if (isset($_GET['__mls__force__split'])) {
            $this->split_string = $_GET['__mls__force__split'];
            return $this->split_string;
        }

        $layer = self::$conf[$layerName];
        $buf = $layer['hashcode'] . $tag;
	# echo "buf=". $buf;
	echo "tag=". $tag;
	echo "\n";
	echo "hex=". hexdec(substr(md5($buf), -10, 10));
	echo "\n";
	# md5字符串太长，hexdec得到浮点数
        $residual = hexdec(substr(md5($buf), -10, 10)) % self::$hashResidual;
	echo "residual=".$residual."\n";

        $this->split_string = "";
        $segments = $layer['segment'];
	
        foreach ($segments as $seg) {
            list($name, $start, $end) = $seg;
            if ($residual < $end) {
                if ($start <= $residual) {
                    $this->split_string = $name;
                    return $name;
                }else {
                    return "";
                }
            }
        }

        return "";
    }

    /**
     * 高级接口，用户提供关于user的定义，指定将user切分为layer上的segment。
     * 优先级：
     *     1. 有user_id，则按照user_id切分。
     *     2. 无user_id, 按照mlsid的逻辑顺序，先iso后android
     *        参考：https://app.yinxiang.com/shard/s9/sh/93a4223c-7bcd-4c40-938d-e67aa64ea653/1a8764d90e9a06bc191a935abdb1af65
     */
    public function splitWithUserInfo($userInfo, $layer) {

        if(isset($userInfo['user_id'])) {
            return $this->split($userInfo['user_id'], $layer);
        }

        if (isset($userInfo['device_token'])) {
            return $this->split($userInfo['device_token'], $layer);
        }

        if (isset($userInfo['open_udid'])) {
            return $this->split($userInfo['open_udid'], $layer);
        }

        if (isset($userInfo['imei'])) {
            return $this->split($userInfo['imei'], $layer);
        }

        if (isset($userInfo['macid'])) {
            return $this->split($userInfo['macid'], $layer);
        }

        if (isset($userInfo["session_id"])) {
            return $this->split($userInfo['session_id'], $layer);
        }

        return "";

    }

    /**
     * Web上的默认切分， 按照meilishuo_global_key将用户切分，因为用户登录会重置旧key，登录可能改变切分结果。
     */
    public function splitInWeb($layer) {
        $session_id = $_COOKIE['MEILISHUO_GLOBAL_KEY'];
        return $this->split($session_id, $layer);
    }

    /**
     * TODO: 实现获取mob上的几种用户id， 比如device_token, imei等。
     *    类似： $dispatcher->get_accessToken()->device_token
     */
    public function splitInMob($layer) {
        $userInfo = array();
        $userInfo['device_token'] = 'device_token';
        $userInfo['open_udid'] = 'open_udid';
        $userInfo['imei'] = 'imei';
        $userInfo['macid'] = 'macid';

        return $this->splitWithUserInfo($userInfo, $layer);
    }

}