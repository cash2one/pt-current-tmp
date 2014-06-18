<?php
/**
 * Created by taopeng@meilishuo.com.
 * User: 2014
 * Date: 14-6-17
 * Time: 下午10:14
 */

require_once("UserSplit.class.php");


class SampleVariable {
    # 全局唯一的配置文件路径
    private static $yamlFile = "sample_variable.json";
    # 配置内容
    private static $conf;
    # singleton
    private static $instance;


    function __construct() {
        # yaml related: http://www.php.net/manual/en/yaml.examples.php
        # $yaml = yaml_parse(file_get_contents(self::$yamlFile));
        # 线上php没有yaml扩展，先用json做配置
        $yaml = json_decode(file_get_contents(self::$yamlFile), true);
        self::$conf = self::mapYaml2Kv($yaml);
    }

    /**
     * 将yaml的规范json转化为相对简单的kv json
     * res['name1'] = {'default': 0, 'layer': 'A', 'condition':{'A1': 10, 'A2':100}}
     * TODO: 进行严格的正确性检查。segment命名不冲突，切分区间顺序不重叠。
     */
    private static function mapYaml2Kv($yaml) {
        $res = array();

        foreach ($yaml as $name => $vars ) {
            $default = $vars['default'];
            $layer = $vars['layer'];
            $condition = $vars['condition'];
            $seg2val = array();
            foreach ($condition as $pair) {
                $seg2val[$pair['segment']] = $pair['value'];
            }

            $res[$name] = array(
                'default' => $default,
                'layer'   => $layer,
                'condition' => $seg2val);
        }
	
        return $res;
    }

    public static function instance() {
        is_null(self::$instance) && self::$instance = new self();
        return self::$instance;
    }


    public function getSVInWeb($name) {

        $sv = self::$conf[$name];
        $layer = $sv['layer'];

        $split = UserSplit::instance();
        $segment = $split->splitInWeb($layer);

        return $this->getSV($name, $segment);
    }

    public function getSVInMob($name) {

        $sv = self::$conf[$name];
        $layer = $sv['layer'];

        $split = UserSplit::instance();
        $segment = $split->splitInMob($layer);

        return $this->getSV($name, $segment);

    }

    /**
     * 高级接口，根据指定的$userInfo用户定义切分。
     */
    public function getSVWithUserInfo($name, $userInfo) {

        $sv = self::$conf[$name];
        $layer = $sv['layer'];

        $split = UserSplit::instance();
        $segment = $split->splitWithUserInfo($userInfo, $layer);

        return $this->getSV($name, $segment);

    }

    /**
     *   根据指定的$split_string: A1_B2, 确认抽样变量$name的值。
     *   无condition命中则返回default。
     *   NOTE: 在这里写AB实验的日志。
     */
    public function getSV($name, $splitString) {
        $segNames = explode('_', trim($splitString));

        $sv = self::$conf[$name];
        $seg2val = $sv['condition'];

        foreach ($segNames as $s) {
            if (isset($seg2val[$s])) {
                ABtestLog::write($s);
                return $seg2val[$s];
            }
        }

        return $sv['default'];
    }

} 