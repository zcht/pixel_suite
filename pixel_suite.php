<?php
/**
 * name: Pixel Suite
 * description: Upload, attach and zoom multiple images per post
 * version: 0.4
 * folder: pixel_suite
 * class: PixelSuite
 * hooks: install_plugin, pixel_suite, submit_2_fields, show_post_content_post, show_post_content_list, post_add_post, post_update_post, header_include, header_include_raw, theme_index_top, admin_sidebar_plugin_settings, admin_plugin_settings
 * author: Andreas Votteler
 * authorurl: http://www.trendkraft.de
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    Nick Ramsay <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

class PixelSuite
{
 
	private $settings = false;
	private $succesm = false;

        
        public function __construct()
        { 
                // define shorthand paths for other CDN if need
                if (!defined('FILEBASE')) {
                        define("FILEBASE", BASE . 'content/uploads/');
                        define("IMGURL", BASEURL . 'content/uploads/img/');
                }
        }
        
    public function install_plugin($h)
    {
    	// create content/uploads folder if it doesn't already exist
    	if (!is_dir(FILEBASE . 'img/'))
    	{
    		// attempt to create folder
			if (!mkdir(FILEBASE . 'img', 0777, true))
			{
				$h->messages['Could not create "uploads" folder in "/content"'] = 'red';
			} else {
				$h->messages['"Uploads" folder created successfully in "/content"'] = 'green';
			} 
		}


		/* Attempt to move anything in "uploads/1" to "uploads" and then remove "1" because SITEID is no longer used */
		if (is_dir(FILEBASE . 'img/1'))
		{
			$uploads = FILEBASE . 'img/';
			recurse_copy($uploads . '1', $uploads); // copy files to "uploads".
			recursive_remove_directory($uploads . '1'); // remove "1" directory tree
		}
		
               
		// Default settings 
		$iu_settings = $h->getSerializedSettings();
	//	if (!isset($iu_settings['thumb_size'])) { $iu_settings['thumb_size'] = array('w'=>90, 'h'=>60, 'zc'=>1); }
	//	if (!isset($iu_settings['embed_size'])) { $iu_settings['embed_size'] = array('w'=>500, 'h'=>250, 'zc'=>1); }
		if (!isset($iu_settings['max_file_size'])) { $iu_settings['max_file_size'] = 1024; }

		/* initsetup extended image upload */
                                if (!$iu_settings['min_size']) { $iu_settings['min_size'] = array('w'=>90, 'h'=>60 ); }
                                if (!$iu_settings['max_size']) { $iu_settings['max_size'] = array('w'=>1280, 'h'=>1024 ); }
                                if (!$iu_settings['thumb1_size']) { $iu_settings['thumb1_size'] = array('w'=>90, 'h'=>60 ); }
                                if (!$iu_settings['thumb2_size']) { $iu_settings['thumb2_size'] = array('w'=>0, 'h'=>0 ); } // tmp_
                                if (!$iu_settings['thumb3_size']) { $iu_settings['thumb3_size'] = array('w'=>250, 'h'=>150 ); } // z2_
                                if (!$iu_settings['thumb4_size']) { $iu_settings['thumb4_size'] = array('w'=>800, 'h'=>600 ); } // z3_
		$roles = $h->getRoles('all');
		if ($roles) {
			foreach ($roles as $r) {
				$r = str_replace( '-', '_', $r );
				$var = 'max_images_'.$r;
				if( @!$iu_settings[ $var ] )
					$iu_settings[ $var ] = 1;
			}
		}
		$this->settings = $iu_settings;
		$h->updateSetting('pixel_suite_settings', serialize($iu_settings));
	}
	
	
    /**
     * get image names attached to this post
     */
    public function getImages( $h = false )
    {
		$pid = @$h->post->id;
		// override post_id with get if post isnt loaded yet
		if( !$pid )
			$pid = $h->cage->get->testInt('post_id');
                                      if ($pid) {
			$sql = "SELECT postmeta_value FROM " . TABLE_POSTMETA . " WHERE postmeta_postid = %d AND postmeta_key = %s";
			$query = $h->db->prepare($sql, $pid, 'pixel_suite');
			
			$h->smartCache('on', 'posts', 60, $query); // start using cache
			$value = $h->db->get_var($query);
			
			
			if ($value) { 
				$h->vars['post_images'] = unserialize(urldecode($value));

				// REMOVE PICTURE
				$picToRemove = $h->cage->get->keyExists('removepic');
				if( $picToRemove !== NULL )
				{
					// need date for folder but $h->post isnt defined yet
					$sql = "SELECT post_date FROM " . TABLE_POSTS . " WHERE post_id = %d";
					$query = $h->db->prepare($sql, $pid);
					$value = $h->db->get_var($query);
					$year = date('Y', unixtimestamp( $value ) ) . '/';
					$month = date('m', unixtimestamp( $value )) . '/';
					$day = date('d', unixtimestamp( $value )) . '/';
					$fff = $h->currentUser->id.'/'.$year;
					if( @$pid )
						$fff = $h->post->author.'/'.$year;
					$imageURL = FILEBASE.'img/' . $fff;
	
					$nv = array();
					foreach( $h->vars['post_images'] as $k => $v )
						if( $picToRemove != $k ) $nv[] = $v;
					else
					{
						#unlink( $imageURL.$v );
						if( !$this->settings ) $this->settings = $h->getSerializedSettings();
						$this->removeImages( $imageURL, $v );
					}
					$h->vars['post_images'] = $nv;
					// SAVE CHANGES
					
					$images = urlencode(serialize($h->vars['post_images'])); 
					$sql = "UPDATE " . TABLE_POSTMETA . " SET postmeta_value = %s WHERE postmeta_postid = %d AND postmeta_key = %s";
					$h->db->query($h->db->prepare($sql, $images, $pid, 'pixel_suite'));
				}
				$h->smartCache('off'); // stop using cache


				// override old mechanism doesnt work any longer
				if( count( $h->vars['post_images'] ) ) return true;
				// return true if an image exists in first array element
				if (isset($h->vars['post_images'][0]) && $h->vars['post_images'][0]) { return true; }
			}
		}
		$h->smartCache('off'); // stop using cache
		@$h->vars['post_images'] = "";
		
		return false;
    }


    /**
     * include pixel_suite CSS only in meged archive, not the .js file
     */
    public function header_include($h)
    {
    	$h->includeCss('pixel_suite');
                $h->includeJs('pixel_suite');
    }
    
    
    /**
     * include pixel_suite javascript just for specific pages (instead of caching a .js file)
     */
    public function header_include_raw($h)
    {
    	if (in_array($h->pageName, array('submit2', 'edit_post'))) {
//                                $h->includeOnceJs(array('jquery-1.8.3.min')); // dev
    		$h->includeOnceJs(array('pixel_suite_upload'));
//    		$h->includeOnceJs(array('jquery.form'));
    	}
    }
    
    /**
     * add the attached image name to the main form
     */
    public function submit_2_fields($h)
    {
        echo "<input type='hidden' name='attachment' value='imageplacehold' id='attachment' />";
    }


	/**
	 * Show the image upload form
	 */
	public function pixel_suite($h)
	{
		$iu_settings = $h->getSerializedSettings();
		// Note: $h->vars['image'] set in submit_2_fields function above
		global $image_exists;
		$image_exists = array();
		
		
		if( @$h->post->url )
		{
			$_SESSION['last_edit_post'] = $h->post->url;
			$_SESSION['last_edit_post_author'] = $h->post->author;
			$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
			for( $x = 0; $x < $iu_settings[$var]; $x++ )
			{
				$year = date('Y') . '/';
				$fff = $h->post->author.'/'.$year;
				$image = basename( $h->post->url ).'_'.$x.'.';
				$file_name = FILEBASE . 'img/' . $fff . $image;
				if( file_exists( $file_name.'png' ) )
				{
					$image_exists[] = (object)array(
							'name' => $image .'png',
							'type' => 'image/png',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST',
							'thumbnail_url' => IMGURL . $fff . $image .'png', 
							'size' => filesize( $file_name.'png')					
					);
				}
				else if( file_exists( $file_name.'jpg' ) )
				{
					$image_exists[] = (object)array(
							'name' => $image .'jpg',
							'type' => 'image/jpg',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST',
							'thumbnail_url' => IMGURL . $fff . $image .'jpg', 
							'size' => filesize( $file_name.'jpg')					
					);
				}
				else if( file_exists( $file_name.'gif' ) )
				{
					$image_exists[] = (object)array(
							'name' => $image .'gif',
							'type' => 'image/gif',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST',
							'thumbnail_url' => IMGURL . $fff . $image .'gif', 
							'size' => filesize( $file_name.'gif')					
					);
				}
			}
		}
		else
		{
			$_SESSION['last_edit_post'] = false;
			$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
			for( $x = 0; $x < $iu_settings[$var]; $x++ )
			{
				$year = date('Y') . '/';
				$fff = $h->currentUser->id.'/'.$year;
				$file_name = FILEBASE . "img/" . $fff . basename( $h->currentUser->name ).'_unneeded_'.$x.'.';
				if( file_exists( $file_name.'png' ) )
				{
					$image_exists[] = (object)array(
							'name' => 'Bild '.($x+1),
							'type' => 'image/png',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST',
							'thumbnail_url' => IMGURL . $fff . basename( $h->currentUser->name ).'_unneeded_'.$x.'.' .'png', 
							'size' => filesize( $file_name.'png')					
					);
				}
				else if( file_exists( $file_name.'jpg' ) )
				{
					$image_exists[] = (object)array(
							'name' => 'Bild '.($x+1),
							'type' => 'image/jpg',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST',
							'thumbnail_url' => IMGURL . $fff . basename( $h->currentUser->name ).'_unneeded_'.$x.'.' .'jpg', 
							'size' => filesize( $file_name.'jpg')					
					);
				}
				else if( file_exists( $file_name.'gif' ) )
				{
					$image_exists[] = (object)array(
							'name' => 'Bild '.($x+1),
							'type' => 'image/gif',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST',
							'thumbnail_url' => IMGURL . $fff . basename( $h->currentUser->name ).'_unneeded_'.$x.'.' .'gif', 
							'size' => filesize( $file_name.'gif')					
					);
				}
			}
		}
		
		$h->displayTemplate('pixel_suite_form');
                                //Submit 2 Next Buttons
                                //echo "tmp";
                
                
                
                
                
                
	}

        public function show_post_content_list($h) {
                            // list
             if (!$this->getImages($h)) { return false; }
            $iu_settings = $h->getSerializedSettings();
            $image = ($h->vars['post_images']);
            
    	$image = ($h->vars['post_images']);

                $year = date('Y', unixtimestamp($h->post->date)) . '/';
                $month = date('m', unixtimestamp($h->post->date)) . '/';
                $day = date('d', unixtimestamp($h->post->date)) . '/';
                $fff = $h->post->author.'/'.$year;


                        $image[0] = str_replace("C:fakepath", "", $image[0]);
                        $thumb = 'tb_'.$image[0];
                        
                        $imgfile = IMGURL. $fff . $thumb;
                        $endung = strrchr($imgfile,".");

                         if($endung == ".png") {
                             $src = "data:image/png;base64," . base64_encode(file_get_contents($imgfile));
                         }

                         else if($endung == ".gif") {
                             $src = "data:image/gif;base64," . base64_encode(file_get_contents($imgfile));
                         }
                         else {
                            $src = "data:image/jpeg;base64," . base64_encode(file_get_contents($imgfile));
                         } 

                        echo "<img class='pixel_suite_list' width='". $iu_settings['thumb1_size']['w']."' height='". $iu_settings['thumb1_size']['h']."' src='".$src."' alt='".$h->post->title."' title='".$h->post->title."' />\n";
            
        }
        
        
        
        
        public function show_post_content_post($h) {
            
            if (!$this->getImages($h)) { return false; }
            $iu_settings = $h->getSerializedSettings();
            $image = ($h->vars['post_images']);
                
            // post
            $year = date('Y', unixtimestamp($h->post->date)) . '/';
            $month = date('m', unixtimestamp($h->post->date)) . '/';
            $day = date('d', unixtimestamp($h->post->date)) . '/';
            $fff = $h->post->author.'/'.$year;

                            echo "<div class='pixel-suite'>";
                            foreach( $image as $i )
                            {
                                    $i = str_replace("C:fakepath", "", $i);
                                    $thumb = 'z2_'.$i;
                            echo "<a href='".IMGURL. $fff . 'z3_' . $i."' data-pixel-suite='trendkraft.de'><img src='".IMGURL. $fff . $thumb . "' title='".$h->post->title."' alt='".$h->post->title."' /></a>\n";
                            }
                            echo "</div>";
            }            
            

        
        
	
	/**
	 * Check if we should save an uploaded image
	 */
	public function theme_index_top($h)
	{
	
		if( $h->pageName == 'edit_post' && $h->cage->post->testAlpha('image') != 'true' )
		{
			$this->getImages( $h );
			//$h->vars['image'] = $this->getImages( $h );

		}
		if (($h->pageName == 'submit2' || $h->pageName == 'edit_post') && ( $h->cage->post->testAlpha('image') == 'true' || $h->cage->get->getRaw( 'imagedelete' ) )) {
		  
			$iu_settings = $h->getSerializedSettings();	
			// iterate upload
			$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
			for( $x = 0; $x < $iu_settings[$var]; $x++ )
			{
				if( $h->cage->get->getRaw( 'imagedelete' ) )
				{
					if( $h->cage->get->keyExists('delete_'.$x) )
					{
						$year = date('Y') . '/';
						$month = date('m') . '/';
						$day = date('d') . '/';
						$fff = $h->currentUser->id.'/'.$year;
						$file_name = FILEBASE . "img/" . $fff . basename( $h->currentUser->name ).'_unneeded_'.$x.'.';
						if( file_exists( $file_name.'png' ) ) unlink( $file_name.'png' );
						else if( file_exists( $file_name.'jpg' ) ) unlink( $file_name.'jpg' );
						else if( file_exists( $file_name.'gif' ) ) unlink( $file_name.'gif' );
						else if( @$_SESSION['last_edit_post'] )
						{
							$image = basename( $_SESSION['last_edit_post'] ).'_'.$x.'.';
							$fff = $_SESSION['last_edit_post_author'].'/'.$year;
							$file_name = FILEBASE . 'img/' . $fff . $image;
							if( file_exists( $file_name.'png' ) )
							{
								@unlink( $file_name.'png' );
								$i1 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'tb_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'png';
								$i2 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'temp_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'png';
								$i3 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'z2_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'png';
								$i4 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'z3_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'png';
								@unlink( $i1 );
								@unlink( $i2 );
								@unlink( $i3 );
								@unlink( $i4 );
							}
							else if( file_exists( $file_name.'jpg' ) )
							{
								@unlink( $file_name.'jpg' );
								$i1 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'tb_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'jpg';
								$i2 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'temp_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'jpg';
								$i3 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'z2_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'jpg';
								$i4 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'z3_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'jpg';
								@unlink( $i1 );
								@unlink( $i2 );
								@unlink( $i3 );
								@unlink( $i4 );
							}
							else if( file_exists( $file_name.'gif' ) )
							{
								@unlink( $file_name.'gif' );
								$i1 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'tb_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'gif';
								$i2 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'temp_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'gif';
								$i3 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'z2_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'gif';
								$i4 = substr( $file_name, 0, strrpos( $file_name, '/' ) + 1 ).'z3_'.substr( $file_name, strrpos( $file_name, '/' ) + 1 ).'gif';
								@unlink( $i1 );
								@unlink( $i2 );
								@unlink( $i3 );
								@unlink( $i4 );
							}
						}
					}
				}
				else
				{
					$msg = $this->saveUploadedFile($h,$x);
					if( $msg !== false )
					{
						$resp = (object)array( 'files' => array( array(
							'name' => 'Bild '.($x+1),
							'type' => 'image/png',
							'delete_url' => BASEURL.'index.php?page='.$h->pageName.($h->pageName == 'edit_post'?'&post_id='.$h->post->id:'').'&delete_'.$x.'=1&imagedelete=1',
							'delete_type' => 'POST'
						) ) );
						$resp->files[0] = (object) array_merge( $resp->files[0], $msg );
						die( json_encode( $resp ) );
					}
				}
			}
			exit;
		}
		
		// get thumbnail sizes (better here ONCE than multiple times in show_post_content_list)
		if ($h->pageType == 'list') {
			$iu_settings = $h->getSerializedSettings();
			$h->vars['iu_width'] = $iu_settings['thumb_size']['w'];
			$h->vars['iu_height'] = $iu_settings['thumb_size']['h'];
			$h->vars['iu_zoom'] = $iu_settings['thumb_size']['zc'];
		}
	}
	
	
	public function getNextFreePicId( $h )
	{
		$file_name = basename( $h->currentUser->name ).'_unneeded_[COUNT].';
	    $post_id = $h->cage->get->testInt('post_id');
	    if ($post_id) {
	    	$h->readPost($post_id);
			// overwrite filename with title
			$file_name = basename( $h->post->url ).'_[COUNT].';
			if ($h->post->date) {
				$year = date('Y', unixtimestamp($h->post->date)) . '/';
				$month = date('m', unixtimestamp($h->post->date)) . '/';
				// add day
				$day = date('d', unixtimestamp($h->post->date)) . '/';
				$fff = $h->post->author.'/'.$year;
			}
		}
		
		// other wise, get the current date
		if (!isset($year) || !isset($month)) {
			$year = date('Y') . '/';
			$month = date('m') . '/';
			// add day
			$day = date('d') . '/';
			$fff = $h->currentUser->id.'/'.$year;
		
		}
		
	    $destination = FILEBASE . "img/" . $fff;
		
		$iu_settings = $h->getSerializedSettings();	
		// iterate upload
		$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
		for( $x = 0; $x < $iu_settings[$var]; $x++ )
		{
			$f_name = str_replace( '[COUNT]', $x, $file_name );
			$s1 = $destination . $f_name . 'jpg';
			$s2 = $destination . $f_name . 'png';
			$s3 = $destination . $f_name . 'gif';
			if( !@file_exists( $s1 ) && !@file_exists( $s2 ) && !@file_exists( $s3 ) ) return $x;
		}
		return -1;
	    
	
	}
	
	/**
	 * Save uploaded image
	 */
	public function saveUploadedFile($h,$count = 0)
	{
	    /* *****************************
	     * ****************************/
		 
	    $iu_settings = $h->getSerializedSettings('pixel_suite');
	    if (isset($iu_settings['max_file_size']) && $iu_settings['max_file_size']) {
	    	$size_limit = $iu_settings['max_file_size'] * 1024; // convert to bytes
		} else {
		    $size_limit = 524288; // 512 KB
		}	    
		
	    /* *****************************
	     * ****************************/
	    //$tmp_filepath = $h->cage->files->getRaw('/file_'.$count.'/tmp_name');
	    $tmp_filepath = $h->cage->files->getRaw('/files/tmp_name');
		if( !$tmp_filepath || $count != $this->getNextFreePicId( $h ) ) return false;
	    //$file_name = basename($h->cage->files->sanitizeTags('/file_'.$count.'/name'));
	    $file_name = $h->cage->files->getRaw('/files/name');
		// switch to another filename
 		$extension = explode( '.', $file_name[0] );
		$extension = $extension[ count( $extension ) - 1 ];
		$file_name = basename( $h->currentUser->name ).'_unneeded_'.$count.'.'.$extension;
		$base_file_name = basename( $h->currentUser->name ).'_unneeded_'.$count.'.';
	    //$file_type = $h->cage->files->testPage('/file_'.$count.'/type');
	    //$file_size = $h->cage->files->testInt('/file_'.$count.'/size');
	    //$file_error = $h->cage->files->testInt('/file_'.$count.'/error');
	    $file_type = $h->cage->files->testPage('/files/type');
		$file_type = $file_type[0];
	    $file_size = $h->cage->files->getRaw('/files/size');
		$file_size = intval( $file_size[0] );
	    $file_error = $h->cage->files->getRaw('/files/error');
		$file_error = intval( $file_error[0] );
	    // If editing an old post, we need the post date, not the current date
	    $post_id = $h->cage->get->testInt('post_id');
	    if ($post_id) {
	    	$h->readPost($post_id);
			// overwrite filename with title
			$file_name = basename( $h->post->url ).'_'.$count.'.'.$extension;
			$base_file_name = basename( $h->post->url ).'_'.$count.'.';
			if ($h->post->date) {
				$year = date('Y', unixtimestamp($h->post->date)) . '/';
				$month = date('m', unixtimestamp($h->post->date)) . '/';
				// add day
				$day = date('d', unixtimestamp($h->post->date)) . '/';
				$fff = $h->post->author.'/'.$year;
		
			}
		}
		
		// other wise, get the current date
		if (!isset($year) || !isset($month)) {
			$year = date('Y') . '/';
			$month = date('m') . '/';
			// add day
			$day = date('d') . '/';
			$fff = $h->currentUser->id.'/'.$year;
		
		}
		
	    $destination = FILEBASE . "img/" . $fff;
	    $this->createFolder($destination);
		
	    $types = array('image/jpeg', 'image/gif', 'image/png');
	    
	    if (in_array($file_type, $types) && $file_size < $size_limit)
	    {
	        if ($file_error > 0)
	        {
                            return array( 'error' => 'Image '.( $count + 1 ).': '."Error: code " . $file_error );
	            $h->message = 'Image '.( $count + 1 ).': '."Error: code " . $file_error;
	            $h->messageType = "red";
	            $h->showMessage();
	            return false;
	        }
	        else
	        {
				
				// extended image upload errors
				$img_size = getimagesize( $tmp_filepath[0] );
				if( $iu_settings[ 'min_size' ][ 'w' ] > $img_size[ 0 ] || $iu_settings[ 'min_size' ][ 'h' ] > $img_size[ 1 ] ){
					$m = $h->lang['pixel_suite_too_small_error'];
					$m = str_replace( '{width}', $iu_settings[ 'min_size' ][ 'w' ], $m );
					$m = str_replace( '{height}', $iu_settings[ 'min_size' ][ 'h' ], $m );
					return array( 'error' => 'Bild '.( $count + 1 ).': '.$m );
	                $h->message = 'Bild '.( $count + 1 ).': '.$m;
	                $h->messageType = "red";
	                $h->showMessage();
	                return false;
	            }
				if( $iu_settings[ 'max_size' ][ 'w' ] < $img_size[ 0 ] || $iu_settings[ 'max_size' ][ 'h' ] < $img_size[ 1 ] ){
					$m = $h->lang['pixel_suite_too_big_error'];
					$m = str_replace( '{width}', $iu_settings[ 'max_size' ][ 'w' ], $m );
					$m = str_replace( '{height}', $iu_settings[ 'max_size' ][ 'h' ], $m );
					return array( 'error' => 'Bild '.( $count + 1 ).': '.$m );
	                $h->message = 'Bild '.( $count + 1 ).': '.$m;
	                $h->messageType = "red";
	                $h->showMessage();
	                return false;
	            }
				// extended image upload errors end
				if( file_exists( $destination . $base_file_name.'png' ) )
					unlink( $destination . $base_file_name.'png' );
				if( file_exists( $destination . $base_file_name.'jpg' ) )
					unlink( $destination . $base_file_name.'jpg' );
				if( file_exists( $destination . $base_file_name.'gif' ) )
					unlink( $destination . $base_file_name.'gif' );

	            if (!move_uploaded_file($tmp_filepath[0], $destination . $file_name)) {
					return array( 'error' => 'Bild '.( $count + 1 ).': '.$h->lang['pixel_suite_move_error'] );
	                $h->message = 'Bild '.( $count + 1 ).': '.$h->lang['pixel_suite_move_error'];
	                $h->messageType = "red";
	                $h->showMessage();
	                return false;
	            }
	            if( !$this->succesm )
				{
					return array( 'success' => $h->lang['pixel_suite_success'],'thumbnail_url' => IMGURL . $fff . $file_name.'?clearcache='.time(), 'size' => filesize($destination . $file_name) );
					$h->message = $h->lang['pixel_suite_success'];
					$h->messageType = "green";
					$h->showMessage();
				}
	            $this->succesm = true;            
	            return $file_name;
	        }
	    }
	    else
	    {    
	        if (!in_array($file_type, $types)) {
				return array( 'error' => $h->lang['pixel_suite_type_error'] );
	            $h->message = $h->lang['pixel_suite_type_error'];
	        } elseif ($file_size >= $size_limit) {
				return array( 'error' => $h->lang['pixel_suite_size_error'].display_filesize($file_size) );
	            $h->message = $h->lang['pixel_suite_size_error'] . 
	                display_filesize($file_size);
	        }
	        
	        $h->messageType = "red";
	        $h->showMessage();
	        return false;
	    }
	}
    
    
    /**
     * create a new uploads folder for month/date
     *
     * @param string $destination - path to file
     */
    public function createFolder($destination = '')
    {
    	// create folder if it doesn't already exist
    	if (!is_dir($destination))
    	{
    		// attempt to create folder
			mkdir($destination, 0777, true);
		}
	}
    


    /**
     * Add image to the posts table
     */
    public function post_add_post($h)
    {
#		if (!isset($h->vars['submitted_data']['image'])) { return false; }
		// create new image array
		$images = array();
		$imagesToTransform = array();
		$year = date('Y') . '/';
		$month = date('m') . '/';
		$day = date('d') . '/';
		$fff = $h->currentUser->id.'/'.$year;
		
		
		$iu_settings = $h->getSerializedSettings();	
		// reconstruct images
		$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
		for( $x = 0; $x < $iu_settings[ $var ]; $x++ )
		{
			$fff = $h->post->author.'/'.$year;
		
			$image = basename( $h->post->url ).'_'.$x.'.';
			$newImageURL = "img/" . $fff . $image;
			
			$oldImageURL = "img/" . $fff . basename( $h->currentUser->name ).'_unneeded_'.$x.'.';
			if( file_exists( FILEBASE . $oldImageURL.'png' ) )
			{
				$images[] = $image.'png';
				$imagesToTransform[] = FILEBASE . $newImageURL.'png';
				rename( FILEBASE . $oldImageURL.'png', FILEBASE . $newImageURL.'png' );
				continue;
			}
			if( file_exists( FILEBASE . $oldImageURL.'jpg' ) )
			{
				$images[] = $image.'jpg';
				$imagesToTransform[] = FILEBASE . $newImageURL.'jpg';
				rename( FILEBASE . $oldImageURL.'jpg', FILEBASE . $newImageURL.'jpg' );
				continue;
			}
			if( file_exists( $oldImageURL.'gif' ) )
			{
				$images[] = $image.'gif';
				$imagesToTransform[] = FILEBASE . $newImageURL.'gif';
				rename( FILEBASE . $oldImageURL.'gif', FILEBASE . $newImageURL.'gif' );
				continue;
			}
		}
		if( !$this->settings ) $this->settings = $h->getSerializedSettings();
		$success = $this->transformImages( $imagesToTransform );
		if( !$success ) $images = array();
		$images = urlencode(serialize($images));
	
		$sql = "INSERT INTO " . TABLE_POSTMETA . " (postmeta_postid, postmeta_key, postmeta_value, postmeta_updateby) VALUES (%d, %s, %s, %d)";
		$query = $h->db->prepare($sql, $h->post->vars['last_insert_id'], 'pixel_suite', $images, $h->currentUser->id);
		$h->db->query($query);
    }
    
    
    /**
     * Update image in the posts table
     */
    public function post_update_post($h)
    {
		if( $h->cage->get->testAlnumLines('checkbox_action') == 'delete_selected' )
		{
			if (!$this->getImages($h)) { return false; }
			$iu_settings = $h->getSerializedSettings();
			$image = ($h->vars['post_images']);
			// setting thumbnailproperties

			$year = date('Y', unixtimestamp($h->post->date)) . '/';
			$fff = $h->post->author.'/'.$year;
		
        // echo the image
			foreach( $image as $i )
			{
				$i = str_replace("C:fakepath", "", $i);
				$i1 = FILEBASE.'img/'.$fff.'tb_'.$i;
				$i2 = FILEBASE.'img/'.$fff.$i;
				$i5 = FILEBASE.'img/'.$fff.'temp_'.$i;
				$i3 = FILEBASE.'img/'.$fff.'z2_'.$i;
				$i4 = FILEBASE.'img/'.$fff.'z3_'.$i;
				if( file_exists( $i1 ) ) unlink( $i1 );
				if( file_exists( $i2 ) ) unlink( $i2 );
				if( file_exists( $i3 ) ) unlink( $i3 );
				if( file_exists( $i4 ) ) unlink( $i4 );
				if( file_exists( $i5 ) ) unlink( $i5 );
			}
			
		}
		else
		{
		
		
			#		if (!isset($h->vars['submitted_data']['image'])) { return false; }
			// create new image array
			$images = array();
			$imagesToTransform = array();
			$year = date('Y', unixtimestamp($h->post->date)) . '/';
			$month = date('m', unixtimestamp($h->post->date)) . '/';
			$day = date('d', unixtimestamp($h->post->date)) . '/';
			
			$fff = $h->post->author.'/'.$year;
			
			$iu_settings = $h->getSerializedSettings();	
			// reconstruct images
			$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
			for( $x = 0; $x < $iu_settings[ $var ]; $x++ )
			{
				$image = basename( $h->post->url ).'_'.$x.'.';
				$old_image = basename( $_SESSION['last_edit_post'] ).'_'.$x.'.';

				$imageURL = "img/" . $fff . $image;
				$oldImageURL = "img/" . $fff . $old_image;
				if( $image != $old_image && $_SESSION['last_edit_post'] )
				{
					if( file_exists( FILEBASE . $oldImageURL.'png' ) )
					{
						rename( FILEBASE.$oldImageURL.'png', FILEBASE.$imageURL.'png' );
						if( file_exists( FILEBASE.'img/'.$fff.'tb_'.$old_image.'png' ) ) unlink( FILEBASE.'img/'.$fff.'tb_'.$old_image.'png' );
						if( file_exists( FILEBASE.'img/'.$fff.'temp_'.$old_image.'png' ) ) unlink( FILEBASE.'img/'.$fff.'temp_'.$old_image.'png' );
						if( file_exists( FILEBASE.'img/'.$fff.'z2_'.$old_image.'png' ) ) unlink( FILEBASE.'img/'.$fff.'z2_'.$old_image.'png' );
						if( file_exists( FILEBASE.'img/'.$fff.'z3_'.$old_image.'png' ) ) unlink( FILEBASE.'img/'.$fff.'z3_'.$old_image.'png' );
					}
					if( file_exists( FILEBASE . $oldImageURL.'jpg' ) )
					{
						rename( FILEBASE.$oldImageURL.'jpg', FILEBASE.$imageURL.'jpg' );
						if( file_exists( FILEBASE.'img/'.$fff.'tb_'.$old_image.'jpg' ) ) unlink( FILEBASE.'img/'.$fff.'tb_'.$old_image.'jpg' );
						if( file_exists( FILEBASE.'img/'.$fff.'temp_'.$old_image.'jpg' ) ) unlink( FILEBASE.'img/'.$fff.'temp_'.$old_image.'jpg' );
						if( file_exists( FILEBASE.'img/'.$fff.'z2_'.$old_image.'jpg' ) ) unlink( FILEBASE.'img/'.$fff.'z2_'.$old_image.'jpg' );
						if( file_exists( FILEBASE.'img/'.$fff.'z3_'.$old_image.'jpg' ) ) unlink( FILEBASE.'img/'.$fff.'z3_'.$old_image.'jpg' );
					}
					if( file_exists( FILEBASE . $oldImageURL.'gif' ) )
					{
						rename( FILEBASE.$oldImageURL.'gif', FILEBASE.$imageURL.'gif' );
						if( file_exists( FILEBASE.'img/'.$fff.'tb_'.$old_image.'gif' ) ) unlink( FILEBASE.'img/'.$fff.'tb_'.$old_image.'gif' );
						if( file_exists( FILEBASE.'img/'.$fff.'temp_'.$old_image.'gif' ) ) unlink( FILEBASE.'img/'.$fff.'temp_'.$old_image.'gif' );
						if( file_exists( FILEBASE.'img/'.$fff.'z2_'.$old_image.'gif' ) ) unlink( FILEBASE.'img/'.$fff.'z2_'.$old_image.'gif' );
						if( file_exists( FILEBASE.'img/'.$fff.'z3_'.$old_image.'gif' ) ) unlink( FILEBASE.'img/'.$fff.'z3_'.$old_image.'gif' );
					}
				}
				if( file_exists( FILEBASE . $imageURL.'png' ) )
				{
					$images[] = $image.'png';
					$imagesToTransform[] = FILEBASE . $imageURL.'png';
					continue;
				}
				if( file_exists( FILEBASE . $imageURL.'jpg' ) )
				{
					$images[] = $image.'jpg';
					$imagesToTransform[] = FILEBASE . $imageURL.'jpg';
					continue;
				}
				if( file_exists( FILEBASE . $imageURL.'gif' ) )
				{
					$images[] = $image.'gif';
					$imagesToTransform[] = FILEBASE . $imageURL.'gif';
					continue;
				}
			}
			if( !$this->settings ) $this->settings = $h->getSerializedSettings();
			$success = $this->transformImages( $imagesToTransform );
			if( !$success ) $images = array();
			$images = urlencode(serialize($images));
			
			$sql = "SELECT postmeta_id FROM " . TABLE_POSTMETA . " WHERE postmeta_postid = %d AND postmeta_key = %s";
			$result = $h->db->get_var($h->db->prepare($sql, $h->post->id, 'pixel_suite'));
			
			if ($result) {
				$sql = "UPDATE " . TABLE_POSTMETA . " SET postmeta_value = %s WHERE postmeta_postid = %d AND postmeta_key = %s";
				$h->db->query($h->db->prepare($sql, $images, $h->post->id, 'pixel_suite'));
			} else {
				$sql = "INSERT INTO " . TABLE_POSTMETA . " (postmeta_postid, postmeta_key, postmeta_value, postmeta_updateby) VALUES (%d, %s, %s, %d)";
				$h->db->query($h->db->prepare($sql, $h->post->id, 'pixel_suite', $images, $h->currentUser->id));
			}
		}
    }
    
   
   
    
    
    
    
    
	private function removeImages( $fff, $i )
	{
				$i = str_replace("C:fakepath", "", $i);
				$i1 = $fff.'tb_'.$i;
				$i2 = $fff.$i;
				$i5 = $fff.'temp_'.$i;
				$i3 = $fff.'z2_'.$i;
				$i4 = $fff.'z3_'.$i;
				if( file_exists( $i1 ) ) unlink( $i1 );
				if( file_exists( $i2 ) ) unlink( $i2 );
				if( file_exists( $i3 ) ) unlink( $i3 );
				if( file_exists( $i4 ) ) unlink( $i4 );
				if( file_exists( $i5 ) ) unlink( $i5 );
	
	
	}
	private function transformImages( $images )
	{
		$success = false;
		foreach( $images as $i )
		{
			$fin = explode( '.', $i );
			$fin = $fin[ count( $fin ) - 1 ];
			/*
			$i1 = str_replace( '.'.$fin, '_'.$this->settings['thumb1_size']['w'].'x'.$this->settings['thumb1_size']['h'].'.'.$fin, $i );
			$i2 = str_replace( '.'.$fin, '_'.$this->settings['thumb2_size']['w'].'x'.$this->settings['thumb2_size']['h'].'.'.$fin, $i );
			$i3 = str_replace( '.'.$fin, '_'.$this->settings['thumb3_size']['w'].'x'.$this->settings['thumb3_size']['h'].'.'.$fin, $i );
			$i4 = str_replace( '.'.$fin, '_'.$this->settings['thumb4_size']['w'].'x'.$this->settings['thumb4_size']['h'].'.'.$fin, $i );
			*/
			//$i1 = 'tb_'.$i;
			$i1 = substr( $i, 0, strrpos( $i, '/' ) + 1 ).'tb_'.substr( $i, strrpos( $i, '/' ) + 1 );
			$i2 = substr( $i, 0, strrpos( $i, '/' ) + 1 ).'temp_'.substr( $i, strrpos( $i, '/' ) + 1 );
			$i3 = substr( $i, 0, strrpos( $i, '/' ) + 1 ).'z2_'.substr( $i, strrpos( $i, '/' ) + 1 );
			$i4 = substr( $i, 0, strrpos( $i, '/' ) + 1 ).'z3_'.substr( $i, strrpos( $i, '/' ) + 1 );
			#if( !file_exists( $i1 ) )
			#{
				$success = $this->createThumb( $i, $i1, $this->settings['thumb1_size']['w'], $this->settings['thumb1_size']['h'] );
			#}
			#if( !file_exists( $i3 ) )
			#{
				$success = $this->createThumb( $i, $i3, $this->settings['thumb3_size']['w'], $this->settings['thumb3_size']['h'] );
			#}
			#if( !file_exists( $i4 ) )
			#{
				$success = $this->createThumb( $i, $i4, $this->settings['thumb4_size']['w'], $this->settings['thumb4_size']['h'] );
			#}
			#if( !file_exists( $i2 ) )
			#{
				$success = $this->createThumb( $i, $i2, $this->settings['thumb2_size']['w'], $this->settings['thumb2_size']['h'] );
			#}
		}
		return $success;
	}
	
	private function createThumb( $origI, $targetI, $new_width, $new_height )
	{
		if( strpos( $origI, '.png' ) !== false )
			$image = @imagecreatefrompng( $origI );
		elseif( strpos( $origI, '.jpg' ) !== false )
			$image = @imagecreatefromjpeg( $origI );
		elseif( strpos( $origI, '.gif' ) !== false )
			$image = @imagecreatefromgif( $origI );
		if( !$image ) return false;
		
		
		
		$width = imagesx($image);
		$height = imagesy($image);
		
		if( $new_width && !$new_height ) {
			$new_height = $height * ( $new_width / $width );
		} elseif($new_height && !$new_width) {
			$new_width = $width * ( $new_height / $height );
		} elseif(!$new_width && !$new_height) {
			$new_width = $width;
			$new_height = $height;
		}
		$canvas = imagecreatetruecolor( $new_width, $new_height );
		imagealphablending($canvas, false);
		$color = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
		imagefill($canvas, 0, 0, $color);
		imagesavealpha($canvas, true);
		imagecopyresampled( $canvas, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		$sharpenMatrix = array(
			array(-1,-1,-1),
			array(-1,16,-1),
			array(-1,-1,-1),
		);
		$divisor = 8;
		$offset = 0;

		
		if( strpos( $targetI, '.png' ) !== false )
		{
			$tgif = str_replace( '.png', '.gif', $targetI );
			$tjpg = str_replace( '.png', '.jpg', $targetI );
			if( file_exists( $tgif ) ) unlink( $tgif );
			if( file_exists( $tjpg ) ) unlink( $tjpg );
			imagepng( $canvas, $targetI, 3 );
		}
		elseif( strpos( $targetI, '.jpg' ) !== false )
		{
			$tgif = str_replace( '.jpg', '.gif', $targetI );
			$tpng = str_replace( '.jpg', '.png', $targetI );
			if( file_exists( $tgif ) ) unlink( $tgif );
			if( file_exists( $tpng ) ) unlink( $tpng );
			imagejpeg( $canvas, $targetI, 80 );
		}
		elseif( strpos( $targetI, '.gif' ) !== false )
		{
			$tpng = str_replace( '.gif', '.png', $targetI );
			$tjpg = str_replace( '.gif', '.jpg', $targetI );
			if( file_exists( $tpng ) ) unlink( $tpng );
			if( file_exists( $tjpg ) ) unlink( $tjpg );
			imagegif( $canvas, $targetI );
		}
		
		// remove image from memory
		imagedestroy($canvas);
		imagedestroy($image);
		return true;
	}
}


/* Used for moving everything out of the "1" folder in "content/uploads" for upgrade to v.0.2 */
function recurse_copy($src,$dst)
{
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				recurse_copy($src . '/' . $file,$dst . '/' . $file);
			}
			else {
				copy($src . '/' . $file,$dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}


/* Delete directory tree with files in it (used for upgrade to v.0.2)
 * http://lixlpixel.org/recursive_function/php/recursive_directory_delete/ 
 */
function recursive_remove_directory($directory, $empty=FALSE)
{
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}
	if(!file_exists($directory) || !is_dir($directory))
	{
		return FALSE;
	}elseif(is_readable($directory))
	{
		$handle = opendir($directory);
		while (FALSE !== ($item = readdir($handle)))
		{
			if($item != '.' && $item != '..')
			{
				$path = $directory.'/'.$item;
				if(is_dir($path)) 
				{
					recursive_remove_directory($path);
				}else{
					unlink($path);
				}
			}
		}
		closedir($handle);
		if($empty == FALSE)
		{
			if(!rmdir($directory))
			{
				return FALSE;
			}
		}
	}
	return TRUE;
}
?>