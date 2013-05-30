<?php
#http://DOMAIN.TLD/content/plugins/pixel_suite/cronjob/delete_old_images.php?pass=123456
if( $_REQUEST['pass'] != '123456' ) die();
define( 'PATH', FILEBASE . 'img/' );
if($handle = opendir( PATH ) ) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            if( is_dir( PATH.$file ) )
            {
                    $file .= '/'.date('Y').'/';
                    if( !file_exists( PATH.$file ) ) continue;
                    if($handle2 = opendir( PATH.$file ) ) {
                            while (false !== ($file2 = readdir($handle2))) {
                                    if( strpos( $file2, '_unneeded_' ) !== false && !is_dir( PATH.$file.$file2 ) )
                                    {
                                            unlink( PATH.$file.$file2 );

                                    }
                            }
                            closedir($handle2);
                    }			
            }
        }
    }
    closedir($handle);
}