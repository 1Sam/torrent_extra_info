<?php

/**
 * @brief Function to display torrent extra information.
 */

function torrentExtraInfoTrans($matches) {

  //return  print_r($matches,true);//"<".$matches[1]." ".$matches[2].">".$matches[3].".torrent".$matches[4]."</".$matches[5].">";

	//$orig_text = preg_replace('/' . preg_quote($matches[5], '/') . '<\/' . $matches[6] . '>$/', '', $matches[0]);
	
	//sid 검출
	//preg_match('/sid=([A-Za-z0-9\-_]+)/is', $matches[1], $match);
	//return print_r($match,true);
	//return $match[1];
	
	// sid로 파일 위치값 구하기
	//$args = new stdClass();
	$args->sid = $matches[1];
	$args->isvalid = 'Y';
	$output = executeQuery('addons.torrent_extra_info.getFileBySid', $args);
	//$uploaded_filename = $output->data;	
	
	
	//return print_r($uploaded_filename,true);
	//실제 파일 위치
	//return print_r($match,true);//$output->data->uploaded_filename;

	// Filename of torrent to load
	$torrent_file = $output->data->uploaded_filename;

	// Load the .torrent file contents. This could be done via file upload.
	// Example loads a local file specified in $torrent_file
	$data = file_get_contents($torrent_file);

	if ($data == false) {
		//return "<".$matches[1]." ".$matches[2].">".$matches[3].$matches[4].$matches[6]."</".$matches[6].">";
		// 에러일 경우 원본 다시 보냄
		//return $matches[0];
    	exit("1. Failed to read from". $torrent_file."<br>".print_r($matches,true));
	}
	//    exit(print_r($data,true));
	// Create a torrent object
	$torrent = new torrent();

	// Load torrent file data obtained above 
	if ($torrent->load($data) == false) {
    	exit("An error occured: {$torrent->error}");
	}

	//return print_r($torrent,true);
	// Add the new tracker to the torrent
	$files = $torrent->getFiles();

	// Display metadata
	$date = date('Y/m/d h:i:s', $torrent->getCreationDate());
	//echo("Creation Date: $date\n<br>");
	//echo("Comment: {$torrent->getComment()}\n<br>");
	//echo("Created By: {$torrent->getCreatedBy()}\n<br>");
	//echo("Piece Length: {$torrent->getPieceLength()} bytes\n<br>");

	//return print_r($files,true);

	// 토렌트 파일 내의 파일 총용량
	$total_length = 0;
	// 배열 정렬 시작
	// 참고 http://www.akiyan.com/blog/archives/2007/10/phparray_multis.html

	foreach ($files as $key => $row) {
		//$a.=$key;
    	$name[$key]  = $row->name; //오브젝트 형태이기에 $row['name']은 안됨
	    $length[$key] = $row->length;
		$total_length += $row->length;
	}
	array_multisort($length, SORT_DESC,$name, SORT_DESC, $files);
	// 배열 정렬 끝
	
	// return print_r($files,true);
	// reset($files);

	// return print_r($files,true);

	// Loop through the trackers and display them
	$count = 0;

	//$t_files = array();

	$t_files = Null;
	$add_code = Null;
	
	//파일 리스트를 구별하기 위해 랜덤으로 data-target 명에 붙여 줌
	//$random_srl = md5(rand(rand(1111111,4444444),rand(4444445,9999999)));//rand(1,99);
	for($i=0,$random_srl='';$i<3;$i++)$random_srl.=mt_rand(0,99);	

	$t_files_info .= "
	<p class='list-group-item-text'><i class='fa fa-calendar' title='Creation Date'></i>: ".$date."</p>
	<p class='list-group-item-text'><i class='fa fa-user' title='Created By'></i>: ".$torrent->getCreatedBy()."</p>
	<p class='list-group-item-text'><i class='fa fa-comment fa-flip-horizontal' title='Comment'></i>: ".$torrent->getComment()."</p>
	<p class='list-group-item-text'><i class='fa fa-puzzle-piece' title='Piece Length'></i>: ".getFileSize_($torrent->getPieceLength())."</p>";


	foreach ($files as $file) {
		$internal = pathinfo ($file->name);
		$int_name = $internal['filename'];
		$int_ext = strtoupper($internal['extension']);

		//$internal['basename'] 출력해보면 파일명에 포함된 . 앞 한글명이 미출력 되는 문제로 임시로 아래 코드 이용
		$name = substr($file->name, 0, strlen($file->name) - (strlen($internal['extension']) + 1) );
		
		$count++;
		//echo("$count: {$file->name} - {$file->length} bytes\n");
		//$t_files[$count] = "$count: {$file->name} - {$file->length} bytes"."<br/>";
		$t_files .= 
		'<li class="torrent-li-files list-group-item list-group-item-warning">
			<span class="file-size-form pull-left badge">
				<span class="int_ext">'. $int_ext.'</span>
				<span class="file-size">'. getFileSize_($file->length).'</span> 
			</span>  
			<span class="file-number badge pull-left">'. sprintf("%02d",$count).'</span>
			<span class="file-name">'. $name.'</span>
		</li>';
	}

	
	$add_code = 
	'<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script language="JavaScript">jQuery(function(){$(".torrent-popover-info-button").popover();});</script> -->
	<span type="button" class="torrent-collapse-button btn btn-info btn-xs" data-toggle="collapse" data-target="#t_files_'.$random_srl.'"> '.getFileSize_($total_length).' <span class="torrent-collapse-badge badge">'.$count.'</span></span>
	<span class="torrent-tooltip-info-button btn btn-default" data-toggle="tooltip" data-placement="top" data-html="true" data-trigger="click" title="'.$t_files_info.'"><i class="fa fa-info"></i></span>
		<div id="t_files_'.$random_srl.'" class="collapse">
			<ul class="torrent-ul list-group">'.$t_files.'</ul>
		</div>';

	/*
	'!<(div|span|a)([^\>]*)>([^\>]*)(.torrent)(.*?)\<\/(div|span|a)\>!is'

	Array ( 
	[0] => Open.Grave.2013.1080p.BluRay.DTS.x264-PublicHD.torrent [File Size:40.5KB]./files/attach/binaries/416/650/057/524efb94fca6903f8a0163de9418439a
	[1] => a 
	[2] => href="/?module=file&act=procFileDownload&file_srl=57651&sid=7d87bf2329c4c909a69b8802f62e265c" 
	[3] => Open.Grave.2013.1080p.BluRay.DTS.x264-PublicHD 
	[4] => .torrent 
	[5] => [File Size:40.5KB]./files/attach/binaries/416/650/057/524efb94fca6903f8a0163de9418439a 
	[6] => a 
	) 
	*/

	//원래 코드
	//$add_code = '<span class="label label-info">파일리스트</span>';
	//return "<".$matches[1]." ".$matches[2].">".$matches[3].$matches[4].$matches[5]."</".$matches[6].">".$add_code;

	return $matches[0].$add_code;
}


function getFileSize_($size, $float = 0) { 
     $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'); 
     for ($L = 0; intval($size / 1024) > 0; $L++, $size/= 1024); 
     if (($float === 0) && (intval($size) != $size)) $float = 2; 
     return number_format($size, $float, '.', ',') ." <span class='file-size-unit'>".$unit[$L]."</span>";
}

// 직접 출력 코드를 변경해 보세요.
function torrentExtraInfoTrans2($matches) {
	//sid 검출
	//preg_match('/sid=([A-Za-z0-9\-_]+)/is', $matches[1], $match);
	// sid로 파일 위치값 구하기
	//$args = new stdClass();
	$args->sid = $matches[1];
	$args->isvalid = 'Y';
	$output = executeQuery('addons.torrent_extra_info.getFileBySid', $args);

	$torrent_file = $output->data->uploaded_filename;
	$data = file_get_contents($torrent_file);

	if ($data == false) {
	    exit("Failed to read from $torrent_file.");
	}
	
	$torrent = new torrent();

	if ($torrent->load($data) == false) {
	    exit("An error occured: {$torrent->error}");
	}
	$files = $torrent->getFiles();
	$count = 0;

	$t_files = Null;
	$add_code = Null;

	foreach ($files as $file) {
    	$count++;
		$t_files .= "<li class='torrent_list'>".$count.". {$file->name} - {$file->length} bytes"."</li>";
	}

	$add_code = 
	'<button type="button" class="torrent_btn btn btn-danger btn-xs" data-toggle="collapse" data-target="#t_files"> '
	.$count.' files</button>
	<div id="t_files" class="collapse">
	  <ul class="torrent_ul list-group">'.$t_files.'</ul>
	</div>';

	//return "<".$matches[1]." ".$matches[2].">".$matches[3].$matches[4].$matches[5]."</".$matches[6].">".$add_code;

	return $matches[0].$add_code;
}

/* End of file torrent_extra_info.lib.php */
/* Location: ./addons/torrent_extra_info/torrent_extra_info.lib.php */
