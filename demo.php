<?php
/**
 * Created by taopeng@meilishuo.com.
 * User: 2014
 * Date: 14-6-18
 * Time: 上午10:51
 */

require_once("SampleVariable.class.php");
require_once("ABtestLog.class.php");


$sv = SampleVariable::instance();

/*
 * 简单用法
 * $close_alipay = $sv->getSVInMob("close_alipay");
 */

$fh = fopen('tmp.user_id', 'r');
while (!feof($fh)) {
  
  $line = fgets($fh);
  $user_id = trim($line);
  # $userInfo = array("user_id" => $_REQUEST['user_id']);
  # $userInfo = array("user_id" => '0000242342042342');
  $userInfo = array('user_id' => trim($line));
  $close_alipay = $sv->getSVWithUserInfo("close_alipay", $userInfo);
  $swap_fguide = $sv->getSVWithUserInfo("swap_fguide", $userInfo);

  echo $user_id."\t".$close_alipay."\t".$swap_fguide."\t".ABtestLog::dump()."\n";
  ABtestLog::clear();

}

fclose($fh);

  

