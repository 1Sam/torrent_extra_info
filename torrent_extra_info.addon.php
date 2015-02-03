<?php
/* Copyright (C) 1SamOnline <http://1sam.kr> */

if(!defined('__XE__')) exit();

/**
 * @file torrent_extra_info.addon.php
 * @author 1Sam (csh@korea.com)
 * @brief torrent_extra_info addon
 *
 * 첨부된 토렌트 파일의 정보를 자동으로 출력해 주는 애드온입니다.
 * Bootstrap 속성을 사용하여 출력합니다.
 **/

// return unless before_display_content
if($called_position != "before_display_content" || Context::get('act') == 'dispPageAdminContentModify' || Context::getResponseMethod() != 'HTML' || isCrawler()) return;

Context::addCSSFile('./addons/torrent_extra_info/css/addon.css');

require_once(_XE_PATH_ . 'addons/torrent_extra_info/torrent_extra_info.lib.php');
require_once(_XE_PATH_ . 'addons/torrent_extra_info/torrent_extra_info.api.php');

// 콜백으로 사용될 함수명 지정
$torrentExtraInfoTrans = $addon_info->torrent_css == "Y" ? 'torrentExtraInfoTrans' : 'torrentExtraInfoTrans2';

if($addon_info->torrent_tag != 'all') {

	// <a([^\>]*)>((.|\n|\r)*?)(torrent)((.|\n|\r)*?)\<\/a\>
	//$temp_output = preg_replace_callback('!<(div|span|a)([^\>]*)>([^\>]*)('.$addon_info->torrent_extention.')(.*?)\<\/(div|span|a)\>!is', $torrentExtraInfoTrans, $output);
	
	//$regex = '/<a([^\>]*)>(.*?)('.$addon_info->torrent_extention.')((.|\n)*?)\<\/a\>/i';
	//$regex = "/<(?:a|div|span)[^>]*.\Wsid=([\w]{32}).*?.".$addon_info->torrent_extention."(?:.|\n|\r)*?\<\/(?:a|div|span)>/i";
	$regex = "/<a[^>]*.\Wsid=([\w]{32}).*?.".$addon_info->torrent_extention."(?:.|\n|\r)*?\<\/a>/i";
	$temp_output = preg_replace_callback($regex, $torrentExtraInfoTrans, $output);
	
	
	
	//return $temp_output;
} else {
	//href
	//$temp_output = preg_replace_callback('!<'.$addon_info->torrent_tag.'([^\>]*)>([^\>]*)('.$addon_info->torrent_extention.')(.*?)\<\/'.$addon_info->torrent_tag.'\>!is', $torrentExtraInfoTrans, $output);
	// 직접 sid 검출
	//$temp_output = preg_replace_callback("/<".$addon_info->torrent_tag."[^>]*sid=*([^'\"]+)['\"]*[^>]*>.+?".$addon_info->torrent_extention.".+?<\/".$addon_info->torrent_tag.">?/", $torrentExtraInfoTrans, $output);

	// 직접 sid 검출 source_filename 문자열에 "sid"가 있을 경우를 대비해 sid 앞에 오는 &(&amp;) 추가

	// 정규식끝에 i 추가해서 torrent or TORRENT 대소문자 구별 안하게 함
	//$temp_output = preg_replace_callback("/<".$addon_info->torrent_tag."[^>]*&amp;sid=*([^'\"]+)&amp;['\"]*[^>]*>.*?".$addon_info->torrent_extention.".+?<\/".$addon_info->torrent_tag.">?/i", $torrentExtraInfoTrans, $output);
	
	// <a([^\>]*)>((.|\n|\r)*?)(torrent)((.|\n|\r)*?)\<\/a\>
	//<a[^>]*.\Wsid=(.*?)\W.*?.torrent(?:.|\n|\r)*?\<\/a>
	
	// \w : 숫자, 영문, _(언더바)
	// \W : 숫자, 영문, _(언더바)를 제외한 특수문자
	// /i : 대소문자 구별안함
	// .*? : 개행문자로 시작하지 않는 모든 문자열
	// \n*? : 개행문자로 시작하는 모든 문자열
	// (?:) : 그룹을 배열에 추가하지 않음
	
	$regex = "/<".$addon_info->torrent_tag."[^>]*.\Wsid=([\w]{32}).*?.".$addon_info->torrent_extention."(?:.|\n|\r)*?\<\/".$addon_info->torrent_tag.">/i";
	$temp_output = preg_replace_callback($regex, $torrentExtraInfoTrans, $output);
	
	// 직접 sid 검출 source_filename 문자열에 "sid"가 있을 경우를 대비해 sid 앞에 오는 &(&amp;) 추가
	//$temp_output = preg_replace_callback("/<".$addon_info->torrent_tag.".*&amp;sid=([^\"]+).*".$addon_info->torrent_extention.".*<\/".$addon_info->torrent_tag.">/", $torrentExtraInfoTrans, $output);

}

//$temp_output = preg_replace_callback("/<[div|span|a][^>]*href=['\"]*([^'\"]+)['\"]*[^>]*>.+?".$addon_info->torrent_extention.".+?<\/[div|span|a]>?/", $torrentExtraInfoTrans, $output);

if($temp_output) $output = $temp_output;

unset($temp_output);

/* End of file torrent_extra_info.addon.php */
/* Location: ./addons/torrent_extra_info/torrent_extra_info.addon.php */
