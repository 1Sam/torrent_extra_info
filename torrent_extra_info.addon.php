<?php
/* Copyright (C) 1SamOnline <http://1sam.kr> */

if(!defined('__XE__')) exit();

/**
 * @file torrent_extra_info.addon.php
 * @author 1Sam (csh@korea.com)
 * @brief torrent_extra_info addon
 *
 * 첨부된 토렌트 파일의 정보를 자동으로 출력해 주는 애드온입니다.
 **/

// return unless before_display_content
if($called_position != "before_display_content" || Context::get('act') == 'dispPageAdminContentModify' || Context::getResponseMethod() != 'HTML' || isCrawler())
{
	return;
}

Context::addCSSFile('./addons/torrent_extra_info/css/addon.css');

require_once(_XE_PATH_ . 'addons/torrent_extra_info/torrent_extra_info.lib.php');

require_once(_XE_PATH_ . 'addons/torrent_extra_info/torrent_extra_info.api.php');

if($addon_info->torrent_css == "Y") {
	//Bootstrap 태그로 작성
	$torrentExtraInfoTrans = 'torrentExtraInfoTrans';
} else {
	// 대충 작성
	$torrentExtraInfoTrans = 'torrentExtraInfoTrans2';
}

if($addon_info->torrent_tag == 'all') {
	$temp_output = preg_replace_callback('!<(div|span|a)([^\>]*)>([^\>]*)('.$addon_info->torrent_extention.')(.*?)\<\/(div|span|a)\>!is', $torrentExtraInfoTrans, $output);
	//return $temp_output;
} else {
	//href
	//$temp_output = preg_replace_callback('!<'.$addon_info->torrent_tag.'([^\>]*)>([^\>]*)('.$addon_info->torrent_extention.')(.*?)\<\/'.$addon_info->torrent_tag.'\>!is', $torrentExtraInfoTrans, $output);
	// 직접 sid 검출
	//$temp_output = preg_replace_callback("/<".$addon_info->torrent_tag."[^>]*sid=*([^'\"]+)['\"]*[^>]*>.+?".$addon_info->torrent_extention.".+?<\/".$addon_info->torrent_tag.">?/", $torrentExtraInfoTrans, $output);

	// 직접 sid 검출 source_filename 문자열에 "sid"가 있을 경우를 대비해 sid 앞에 오는 &(&amp;) 추가
	$temp_output = preg_replace_callback("/<".$addon_info->torrent_tag."[^>]*&amp;sid=*([^'\"]+)&amp;['\"]*[^>]*>.*?".$addon_info->torrent_extention.".+?<\/".$addon_info->torrent_tag.">?/i", $torrentExtraInfoTrans, $output);
	
	// 직접 sid 검출 source_filename 문자열에 "sid"가 있을 경우를 대비해 sid 앞에 오는 &(&amp;) 추가
	//$temp_output = preg_replace_callback("/<".$addon_info->torrent_tag.".*&amp;sid=([^\"]+).*".$addon_info->torrent_extention.".*<\/".$addon_info->torrent_tag.">/", $torrentExtraInfoTrans, $output);
}

//$temp_output = preg_replace_callback("/<[div|span|a][^>]*href=['\"]*([^'\"]+)['\"]*[^>]*>.+?".$addon_info->torrent_extention.".+?<\/[div|span|a]>?/", $torrentExtraInfoTrans, $output);

if($temp_output) $output = $temp_output;

unset($temp_output);

/* End of file torrent_extra_info.addon.php */
/* Location: ./addons/torrent_extra_info/torrent_extra_info.addon.php */
