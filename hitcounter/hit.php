<?php

$id = intval($_GET['id']);
if (! $id)
$id = '0';

$lastip = $_SERVER['REMOTE_ADDR'];

$insert = "INSERT INTO hitcounter (id,count, lastip) VALUES ($id, 1, '$lastip')";
$update = "UPDATE hitcounter SET count=(count+1), lastip='$lastip' WHERE lastip<>'$lastip'";
$select = "SELECT count FROM hitcounter WHERE id=$id";

$link = mysql_connect( 'localhost:3306', 'riceballcom', 'ginchy' );
mysql_select_db( 'riceball_com', $link );

$res = mysql_query( $insert, $link );
if (!$res)
{
$res = mysql_query( $update, $link );
}

$res = mysql_query( $select, $link );
$row = mysql_fetch_array( $res );
$count = $row[0];

$img = imagecreate( 100, 24 );
$background = imagecolorallocate( $img, 128, 128, 128 );
$text = imagecolorallocate( $img, 255, 255, 255 );

imagestring( $img, 5, 5, 3, "$count", $text );

header( 'Content-type: image/gif' );
imagegif( $img );
exit;

?>

