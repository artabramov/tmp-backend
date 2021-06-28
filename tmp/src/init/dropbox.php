<?php

use \Kunnu\Dropbox\Dropbox;
use \Kunnu\Dropbox\DropboxApp;
use \Kunnu\Dropbox\DropboxFile;

//Configure Dropbox Application
$app_key = 'ir3ndpahsbnyru0';
$app_secret = '1bwvj9amy1ai55f';
$access_token = 'WSpPvkMfiVEAAAAAAAAAAe-DJD3Ot3stp7ci2Mpvi_hZhvdbYJjSrtfYTdKPD3Rm';
$dropbox_app = new DropboxApp( $app_key, $app_secret, $access_token );

//Configure Dropbox service
$dropbox = new Dropbox( $dropbox_app );
return $dropbox;
