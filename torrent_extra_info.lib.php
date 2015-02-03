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


class torrent
{
    // Private class members
    private $torrent;
    private $info;
    
    // Public error message, $error is set if load() returns false
    public $error;
    
    // Load torrent file data
    // $data - raw torrent file contents
    public function load( &$data )
    {
        $this->torrent = BEncode::decode( $data );

        if ( $this->torrent->get_type() == 'error' )
        {
            $this->error = $this->torrent->get_plain();
            return false;
        }
        else if ( $this->torrent->get_type() != 'dictionary' )
        {
            $this->error = 'The file was not a valid torrent file.';
            return false;
        }
        
        $this->info = $this->torrent->get_value('info');
        if ( !$this->info )
        {
            $this->error = 'Could not find info dictionary.';
            return false;
        }
        
        return true;
    }
    
    // Get comment
    // return - string
    public function getComment() {
        return $this->torrent->get_value('comment') ? $this->torrent->get_value('comment')->get_plain() : null;
    }
    
    // Get creatuion date
    // return - php date
    public function getCreationDate() {
        return  $this->torrent->get_value('creation date') ? $this->torrent->get_value('creation date')->get_plain() : null;
    }
    
    // Get created by
    // return - string
    public function getCreatedBy() {
        return $this->torrent->get_value('created by') ? $this->torrent->get_value('created by')->get_plain() : null;
    }
    
    // Get name
    // return - filename (single file torrent)
    //          directory (multi-file torrent)
    // see also - getFiles()
    public function getName() {
        return $this->info->get_value('name')->get_plain();
    }
    
    // Get piece length
    // return - int
    public function getPieceLength() {
        return $this->info->get_value('piece length')->get_plain();
    }
    
    // Get pieces
    // return - raw binary of peice hashes
    public function getPieces() {
        return $this->info->get_value('pieces')->get_plain();
    }
    
    // Get private flag
    // return - -1 public, implicit
    //           0 public, explicit
    //           1 private
    public function getPrivate() {
        if ( $this->info->get_value('private') )
        {
            return $this->info->get_value('private')->get_plain();
        }
        return -1;
    }
    
    // Get a list of files
    // return - array of Torrent_File
    public function getFiles() {
        // Load files
        $filelist = array();
        $length = $this->info->get_value('length');
        
        if ( $length )
        {
            $file = new Torrent_File();
            $file->name = $this->info->get_value('name')->get_plain();
            $file->length =  $this->info->get_value('length')->get_plain();
            array_push( $filelist, $file );
        }
        else if ( $this->info->get_value('files') )
        {
            $files = $this->info->get_value('files')->get_plain();
            while ( list( $key, $value ) = each( $files ) )
            {
                $file = new Torrent_File();

                $path = $value->get_value('path')->get_plain();
                while ( list( $key, $value2 ) = each( $path ) )
                {
                    $file->name .= "/" . $value2->get_plain();
                }
                $file->name = ltrim( $file->name, '/' );
                $file->length =  $value->get_value('length')->get_plain();

                array_push( $filelist, $file );
            }
        }
        
        return $filelist;
    }
    
    // Get a list of trackers
    // return - array of strings
    public function getTrackers() {
        // Load tracker list
        $trackerlist = array();
        
        if ( $this->torrent->get_value('announce-list') )
        {
            $trackers = $this->torrent->get_value('announce-list')->get_plain();
            while ( list( $key, $value ) = each( $trackers ) )
            {
                if ( is_array( $value->get_plain() ) ) {
                    while ( list( $key, $value2 ) = each( $value ) )
                    {
                        while ( list( $key, $value3 ) = each( $value2 ) )
                        {
                            array_push( $trackerlist, $value3->get_plain() );
                        }
                    }
                } else {
                    array_push( $trackerlist, $value->get_plain() );
                }
            }
        }
        else if ( $this->torrent->get_value('announce') )
        {
            array_push( $trackerlist, $this->torrent->get_value('announce')->get_plain() );
        }
        
        return $trackerlist;
    }
      
    // Helper function to make adding a tracker easier
    // $tracker_url - string
    public function addTracker( $tracker_url )
    {
        $trackers = $this->getTrackers();
        $trackers[] = $tracker_url;
        $this->setTrackers( $trackers );
    }
    
    // Replace the current trackers with the supplied list
    // $trackerlist - array of strings
    public function setTrackers( $trackerlist )
    {
        if ( count( $trackerlist ) >= 1 )
        {
            $this->torrent->remove('announce-list');
            $string = new BEncode_String( $trackerlist[0] );
            $this->torrent->set( 'announce', $string );
        }
            
        if ( count( $trackerlist ) > 1 )
        {
            $list = new BEncode_List();
            

            while ( list( $key, $value ) = each( $trackerlist ) )
            {
                $list2 = new BEncode_List();
                $string = new BEncode_String( $value );
                $list2->add( $string );
                $list->add( $list2 );
            }
            
            $this->torrent->set( 'announce-list', $list );
        }
    }
    
    // Update the list of files
    // $filelist - array of Torrent_File
    public function setFiles( $filelist )
    {
        // Load files
        $length = $this->info->get_value('length');

        if ( $length )
        {
            $filelist[0] = str_replace( '\\', '/', $filelist[0] );
            $string = new BEncode_String( $filelist[0] );
            $this->info->set( 'name', $string );
        }
        else if ( $this->info->get_value('files') )
        {
            $files = $this->info->get_value('files')->get_plain();
            for ( $i = 0; $i < count( $files ); ++$i )
            {
                $file_parts = split( '/', $filelist[$i] );
                $path = new BEncode_List();
                foreach ( $file_parts as $part )
                {
                    $string = new BEncode_String( $part );
                    $path->add( $string );
                }
                $files[$i]->set( 'path', $path );
            }
        }
    }
    
    // Set the comment field
    // $value - string
    public function setComment( $value )
    {
        $type = 'comment';
        $key = $this->torrent->get_value( $type );
        if ( $value == '' ) {
            $this->torrent->remove( $type );
        } elseif ( $key ) {
            $key->set( $value );
        } else {
            $string = new BEncode_String( $value );
            $this->torrent->set( $type, $string );
        }
    }
    
    // Set the created by field
    // $value - string
    public function setCreatedBy( $value )
    {
        $type = 'created by';
        $key = $this->torrent->get_value( $type );
        if ( $value == '' ) {
            $this->torrent->remove( $type );
        } elseif ( $key ) {
            $key->set( $value );
        } else {
            $string = new BEncode_String( $value );
            $this->torrent->set( $type, $string );
        }
    }
    
    
    // Set the creation date
    // $value - php date
    public function setCreationDate( $value )
    {
        $type = 'creation date';
        $key = $this->torrent->get_value( $type );
        if ( $value == '' ) {
            $this->torrent->remove( $type );
        } elseif ( $key ) {
            $key->set( $value );
        } else {
            $int = new BEncode_Int( $value );
            $this->torrent->set( $type, $int );
        }
    }
    
    // Change the private flag
    // $value - -1 public, implicit
    //           0 public, explicit
    //           1 private
    public function setPrivate( $value )
    {
        if ( $value == -1 ) {
            $this->info->remove( 'private' );
        } else {
            $int = new BEncode_Int( $value );
            $this->info->set( 'private', $int );
        }
    }
    
    // Bencode the torrent
    public function bencode()
    {
        return $this->torrent->encode();
    }
    
    // Return the torrent's hash
    public function getHash()
    {
        return strtoupper( sha1( $this->info->encode() ) );
    }
}

// Simple class to encapsulate filename and length
class Torrent_File
{
    public $name;
    public $length;
}


class BEncode
{
    public static function &decode( &$raw, &$offset=0 )
    {   
        if ( $offset >= strlen( $raw ) )
        {
            return new BEncode_Error( "Decoder exceeded max length." ) ;
        }
        
        $char = $raw[$offset];
        switch ( $char )
        {
            case 'i':
                $int = new BEncode_Int();
                $int->decode( $raw, $offset );
                return $int;
                
            case 'd':
                $dict = new BEncode_Dictionary();

                if ( $check = $dict->decode( $raw, $offset ) )
                {
                    return $check;
                }
                return $dict;
                
            case 'l':
                $list = new BEncode_List();
                $list->decode( $raw, $offset );
                return $list;
                
            case 'e':
                return new BEncode_End();
                
            case '0':
            case is_numeric( $char ):
                $str = new BEncode_String();
                $str->decode( $raw, $offset );
                return $str;

            default:
                return new BEncode_Error( "Decoder encountered unknown char '$char' at offset $offset." );
        }
    }
}

class BEncode_End
{
    public function get_type()
    {
        return 'end';
    }
}

class BEncode_Error
{
    private $error;
    
    public function BEncode_Error( $error )
    {
        $this->error = $error;
    }
    
    public function get_plain()
    {
        return $this->error;
    }
    
    public function get_type()
    {
        return 'error';
    }
}

class BEncode_Int
{
    private $value;
    
    public function BEncode_Int( $value = null )
    {
        $this->value = $value;
    }

    public function decode( &$raw, &$offset )
    {
            $end = strpos( $raw, 'e', $offset );
            $this->value = substr( $raw, ++$offset, $end - $offset );
            $offset += ( $end - $offset );
    }

    public function get_plain()
    {
        return $this->value;
    }

    public function get_type()
    {
        return 'int';
    }
    
    public function encode()
    {
        return "i{$this->value}e";
    }
    
    public function set( $value )
    {
        $this->value = $value;
    }
}

class BEncode_Dictionary
{
    public $value = array();
    
    public function decode( &$raw, &$offset )
    {
            $dictionary = array();

            while ( true )
            {
                $name = BEncode::decode( $raw, ++$offset );

                if ( $name->get_type() == 'end' )
                {
                    break;
                }
                else if ( $name->get_type() == 'error' )
                {
                    return $name;
                }
                else if ( $name->get_type() != 'string' )
                {
                    return new BEncode_Error( "Key name in dictionary was not a string." );
                }

                $value = BEncode::decode( $raw, ++$offset );

                if ( $value->get_type() == 'error' )
                {
                    return $value;
                }

                $dictionary[$name->get_plain()] = $value;
            }

            $this->value = $dictionary;
    }
    
    public function get_value( $key )
    {
        if ( isset( $this->value[$key] ) )
        {
            return $this->value[$key];
        }
        else
        {
            return null;
        }
    }
    
    public function encode()
    {
        $this->sort();

        $encoded = 'd';
        while ( list( $key, $value ) = each( $this->value ) )
        {
            $bstr = new BEncode_String();
            $bstr->set( $key );
            $encoded .= $bstr->encode();
            $encoded .= $value->encode();
        }
        $encoded .= 'e';
        return $encoded;
    }

    public function get_type()
    {
        return 'dictionary';
    }
    
    public function remove( $key )
    {
        unset( $this->value[$key] );
    }
    
    public function set( $key, $value )
    {
        $this->value[$key] = $value;
    }
    
    private function sort()
    {
        ksort( $this->value );
    }
    
    public function count()
    {
        return count( $this->value );
    }
}

class BEncode_List
{
    private $value = array();
    
    public function add( $bval )
    {
        array_push( $this->value, $bval );
    }

    public function decode( &$raw, &$offset )
    {
            $list = array();

            while ( true )
            {
                $value = BEncode::decode( $raw, ++$offset );

                if ( $value->get_type() == 'end' )
                {
                    break;
                }
                else if ( $value->get_type() == 'error' )
                {
                    return $value;
                }
                array_push( $list, $value );
            }

            $this->value = $list;
    }
    
    public function encode()
    {
        $encoded = 'l';
        
        for ( $i = 0; $i < count( $this->value ); ++$i )
        {
            $encoded .= $this->value[$i]->encode();
        }
        $encoded .= 'e';
        return $encoded;
    }
    
    public function get_plain()
    {
        return $this->value;
    }

    public function get_type()
    {
        return 'list';
    }
}

class BEncode_String
{
    private $value;
    
    public function BEncode_String( $value = null )
    {
        $this->value = $value;
    }
    
    public function decode( &$raw, &$offset )
    {
            $end = strpos( $raw, ':', $offset );
            $len = substr( $raw, $offset, $end - $offset );
            $offset += ($len + ($end - $offset));
            $end++;
            $this->value = substr( $raw, $end, $len );
    }
    
    public function get_plain()
    {
        return $this->value;
    }
    
    public function get_type()
    {
        return 'string';
    }
    
    public function encode()
    {
        $len = strlen( $this->value );
        return  "$len:{$this->value}";
    }
    
    public function set( $value )
    {
        $this->value = $value;
    }
}
