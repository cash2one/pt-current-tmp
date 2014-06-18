<?php
/**
 * Created by taopeng@meilishuo.com.
 * User: 2014
 * Date: 14-6-18
 * Time: 上午10:51
 */


$sv = SampleVariable::instance();

/*
 * 简单用法
 * $close_alipay = $sv->getSVInMob("close_alipay");
 */

$userInfo = array("user_id" => $_REQUEST['user_id']);

$close_alipay = $sv->getSVWithUserInfo("close_alipay", $userInfo);


if($close_alipay) {
    do_close_alipay();
}else {
    do_open_alipay();
}