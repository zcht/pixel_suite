<?php
/**
 *  ImageMultiUpload Settings
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

class PixelSuiteSettings
{
     /**
     * Admin settings for the ImageUpload plugin
     */
    public function settings($h)
    {
        // If the form has been submitted, go and save the data...
        if ($h->cage->post->getAlpha('submitted') == 'true') { 
            $this->saveSettings($h); 
        }    
        
        echo "<h1>" . $h->lang["pixel_suite_settings_header"] . "</h1>\n";
        
        $h->showMessage(); // Saved / Error message
        
        // Get settings from database if they exist...
        $iu_settings = $h->getSerializedSettings();
        
//        $thumb_size = $iu_settings['thumb_size'];
//        $embed_size = $iu_settings['embed_size'];
        $max_file_size = $iu_settings['max_file_size'];
        
        $h->pluginHook('pixel_suite_settings_get_values');
        
        if (!$max_file_size) { $max_file_size = 1024; }
        

        
		/* extended image upload */
        $min_size = @$iu_settings['min_size'];
        $max_size = @$iu_settings['max_size'];
        $thumb1_size = @$iu_settings['thumb1_size'];
        $thumb2_size = @$iu_settings['thumb2_size'];
        $thumb3_size = @$iu_settings['thumb3_size'];
        $thumb4_size = @$iu_settings['thumb4_size'];

		$roles = $h->getRoles('all');
		if ($roles) {
			foreach ($roles as $r) {
				$r = str_replace( '-', '_', $r );
				$var = 'max_images_'.$r;
				$$var = @$iu_settings[ $var ];
				if (!$$var) { $$var = 1; }
			}
		}
        
        //...otherwise set to defaults:
        if (!$min_size) { $min_size = array('w'=>90, 'h'=>60 ); } //minimum image size
        if (!$max_size) { $max_size = array('w'=>1280, 'h'=>1024 ); } // maximum image size
        if (!$thumb1_size) { $thumb1_size = array('w'=>90, 'h'=>60 ); } // image size thumbnail th_
        if (!$thumb2_size) { $thumb2_size = array('w'=>250, 'h'=>150 ); } // original size 
        if (!$thumb3_size) { $thumb3_size = array('w'=>250, 'h'=>150 ); } // zoom I z2_
        if (!$thumb4_size) { $thumb4_size = array('w'=>800, 'h'=>600 ); } // zoom II z3_
        
        if (!$max_images_member) { $max_images_member = 1; }
        if (!$max_images_moderator) { $max_images_moderator = 1; }
        if (!$max_images_supermod) { $max_images_supermod = 1; }
        if (!$max_images_admin) { $max_images_admin = 3; }

        
        //form
        echo "<form name='pixel_suite_settings_form' action='" . BASEURL . "admin_index.php?page=plugin_settings&amp;plugin=pixel_suite' method='post'>\n";
		
        include('templates/pixel_suite_settings.php');
		
        $h->pluginHook('pixel_suite_settings_form2');
    
        echo "<br />\n";
        
        echo "<input type='hidden' name='submitted' value='true' />\n";
        echo "<input type='submit' value='" . $h->lang["main_form_save"] . "' />\n";
        echo "<input type='hidden' name='csrf' value='" . $h->csrfToken . "' />\n";
        echo "</form>\n";
        
    }
    
    
    /**
     * Save ImageUpload Settings
     */
    public function saveSettings($h) 
    {
        // Get current settings 
        $iu_settings = $h->getSerializedSettings();
        
        // Thumbnails
        
        // width:
        $thumb_width = $h->cage->post->testInt('thumb_width'); 
        if (!$thumb_width) { 
            $thumb_width = $iu_settings['thumb_size']['w'];
        } 
        
        // height:
        $thumb_height = $h->cage->post->testInt('thumb_height'); 
        if (!$thumb_height) { 
            $thumb_height = $iu_settings['thumb_size']['h'];
        } 
        
        // zoom:
        $thumb_zoom = $h->cage->post->testInt('thumb_zoom'); 
        if (!$thumb_zoom) { 
            $thumb_zoom = $iu_settings['thumb_size']['zc'];
        } 
        
        // Image Embed
        
        // width:
        $embed_width = $h->cage->post->testInt('embed_width'); 
        if (!$embed_width) { 
            $embed_width = $iu_settings['embed_size']['w'];
        } 
        
        // height:
        $embed_height = $h->cage->post->testInt('embed_height'); 
        if (!$embed_height) { 
            $embed_height = $iu_settings['embed_size']['h'];
        } 
        
        // zoom:
        $embed_zoom = $h->cage->post->testInt('embed_zoom'); 
        if (!$embed_zoom) { 
            $embed_zoom = $iu_settings['embed_size']['zc'];
        } 
        
    	// MAX FILE SIZE
    	
        $max_file_size = $h->cage->post->testInt('max_file_size'); 
        if (!$max_file_size) { 
            $max_file_size = $iu_settings['max_file_size'];
        } 
		
		// extended image upload save
		$iu_settings['min_size'] = array( 
			'w' => ( $h->cage->post->testInt('min_width') ? $h->cage->post->testInt('min_width') : $iu_settings['min_size']['w'] ), 
			'h' => ( $h->cage->post->testInt('min_height') ? $h->cage->post->testInt('min_height') : $iu_settings['min_size']['h'] )
		);
		$iu_settings['max_size'] = array( 
			'w' => ( $h->cage->post->testInt('max_width') ? $h->cage->post->testInt('max_width') : $iu_settings['max_size']['w'] ), 
			'h' => ( $h->cage->post->testInt('max_height') ? $h->cage->post->testInt('max_height') : $iu_settings['max_size']['h'] )
		);
		$iu_settings['thumb1_size'] = array( 
			'w' => $h->cage->post->testInt('thumb1_width'), 
			'h' => $h->cage->post->testInt('thumb1_height')
		);
		$iu_settings['thumb2_size'] = array( 
			'w' => $h->cage->post->testInt('thumb2_width'), 
			'h' => $h->cage->post->testInt('thumb2_height')
		);
		$iu_settings['thumb3_size'] = array( 
			'w' => $h->cage->post->testInt('thumb3_width'), 
			'h' => $h->cage->post->testInt('thumb3_height')
		);
		$iu_settings['thumb4_size'] = array( 
			'w' => $h->cage->post->testInt('thumb4_width'), 
			'h' => $h->cage->post->testInt('thumb4_height')
		);

		$roles = $h->getRoles('all');
		if ($roles) {
			foreach ($roles as $r) {
				$r = str_replace( '-', '_', $r );
				$var = 'max_images_'.$r;
				$iu_settings[ $var ] = ( $h->cage->post->testInt( $var ) ? $h->cage->post->testInt( $var ) : $iu_settings[ $var ] );
			}
		}

		/* FINISHED CHANGE */
		
        
        $h->pluginHook('pixel_suite_save_settings');
        
        $iu_settings['thumb_size'] = array('w'=>$thumb_width, 'h'=>$thumb_height, 'zc'=>$thumb_zoom);
        $iu_settings['embed_size'] = array('w'=>$embed_width, 'h'=>$embed_height, 'zc'=>$embed_zoom);
        $iu_settings['max_file_size'] = $max_file_size;
    
        $h->updateSetting('pixel_suite_settings', serialize($iu_settings));
        
        $h->message = $h->lang["main_settings_saved"];
        $h->messageType = "green";
        
        return true;    
    }
    
}
?>
