Image Multi Upload Plugin for Hotaru CMS
---------------------------------
Created by: Andreas Votteler
Based on Inspiration and Source by: 
- Nick Ramsay (Image Upload Plugin)
- Sebastian Tschan (https://github.com/blueimp/jQuery-File-Upload)
- Jack Moore (http://www.jacklmoore.com/colorbox/)

Description:
-----------
This plugin allows to insert multiple images per post. The number of images can be configured through User Roles. The images are automatically resized and do not require additional plug-in to display.

Features:
-----------
- Automatic resize of images
- Automatically creates a "uploads/img" folder, if not available
- Automatically creates a subfolder "USERID / YEAR" for each user, if not available
- Uses Colorbox for displaying images 

Configuration options:
    minimum size of the image
    maximum size of the image
    maximum file size
    testing of user roles for the number of possible uploads
    size of the thumbnail adjustable

Bonus:
    SEO optimization: automatic rename the filename (post title = image name)
    SEO optimization: Title and Alt tags are inserted automatically checking (post title = title and alt-tag)

Instructions
------------
1. Upload the "pixel_suite" folder to your plugins folder.
2. Install it from Plugin Management in Admin.
3. Edited Settings in Admin -> Plugin Settings -> Pixel Suite
4. Search the following code in submit2.php and submit_edit.php:
<form name='submit_2' action='<?php echo BASEURL; ?>index.php?page=submit2' method='post'>
5. Replace old code with the following code in submit2.php and submit_edit.php:
<form name='submit_2' id='submit_2_form' action='<?php echo BASEURL; ?>index.php?page=submit2' method='post'>
6. Search and delete the following code in submit2.php und submit_edit.php:
    <tr><td>&nbsp; </td><td style='text-align:right;'><input type='submit' onclick="javascript:safeExit=true;" class='submit' name='submit' value='<?php echo $h->lang['main_form_next']; ?>' /></td></tr>
7. add the following below the </ form> tag:
<?php $h->pluginHook('pixel_suite'); ?>  


Changelog
---------
v.0.4 2013/05/31 - narc add colorbox, thumbs code with base_64, autocromb, rename to pixel suite
v.0.3 2013/05/28 - shibuya246 - update for jquery 1.9.1 - replace .live() with .on()
v.0.2 2013/05/03 - narc - new installations routinely; changed default settings for image sizes; hooks updated, amended and new; autoUpload disabled; jQuery library removed (control over theme)
v.0.1 2013/04/27 - narc - Released first alpha version. Hooks added. jQuery library version 1.8.3 inserted. small fixes.