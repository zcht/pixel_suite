<table class="table table-striped">
  <thead>      
    <tr>
      <th style="width: 25%">Option:</th>  
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_max_file_size"]; ?></td>
        <td><label>KB</label><input type='text' size=4 name='max_file_size' value='<?php echo $max_file_size; ?>' /></td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_minsize"]; ?></td>
        <td>
            <ul>
                <li><label><?php echo $h->lang["pixel_suite_width"]; ?></label><input type='text' size=4 name='min_width' value='<?php echo $min_size['w']; ?>' /></li>
                <li><label><?php echo $h->lang["pixel_suite_height"]; ?></label><input type='text' size=4 name='min_height' value='<?php echo $min_size['h']; ?>' /></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_maxsize"]; ?></td>
        <td>
            <ul>
                <li><label><?php echo $h->lang["pixel_suite_width"]; ?></label><input type='text' size=4 name='max_width' value='<?php echo $max_size['w']; ?>' /></li>
                <li><label><?php echo $h->lang["pixel_suite_height"]; ?></label><input type='text' size=4 name='max_height' value='<?php echo $max_size['h']; ?>' /></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_thumbsize1"]; ?></td>
        <td>
            <ul>
                <li><label><?php echo $h->lang["pixel_suite_width"]; ?></label><input type='text' size=4 name='thumb1_width' value='<?php echo $thumb1_size['w']; ?>' /></li>
                <li><label><?php echo $h->lang["pixel_suite_height"]; ?></label><input type='text' size=4 name='thumb1_height' value='<?php echo $thumb1_size['h']; ?>' /></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_thumbsize2"]; ?></td>
        <td>
            <ul>
                <li><label><?php echo $h->lang["pixel_suite_width"]; ?></label><input type='text' size=4 name='thumb2_width' value='<?php echo $thumb2_size['w']; ?>' /></li>
                <li><label><?php echo $h->lang["pixel_suite_height"]; ?></label><input type='text' size=4 name='thumb2_height' value='<?php echo $thumb2_size['h']; ?>' /></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_thumbsize3"]; ?></td>
        <td>
            <ul>
                <li><label><?php echo $h->lang["pixel_suite_width"]; ?></label><input type='text' size=4 name='thumb3_width' value='<?php echo $thumb3_size['w']; ?>' /></li>
                <li><label><?php echo $h->lang["pixel_suite_height"]; ?></label><input type='text' size=4 name='thumb3_height' value='<?php echo $thumb3_size['h']; ?>' /></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_settings_thumbsize4"]; ?></td>
        <td>
            <ul>
                <li><label><?php echo $h->lang["pixel_suite_width"]; ?></label><input type='text' size=4 name='thumb4_width' value='<?php echo $thumb4_size['w']; ?>' /></li>
                <li><label><?php echo $h->lang["pixel_suite_height"]; ?></label><input type='text' size=4 name='thumb4_height' value='<?php echo $thumb4_size['h']; ?>' /></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><?php echo $h->lang["pixel_suite_role"]; ?></td>
        <td>
            <ul>
        <?php 
            $roles = $h->getRoles('all');
            if ($roles) {
                    foreach ($roles as $r) {
                            $r = str_replace( '-', '_', $r );
                            $var = 'max_images_'.$r;
                            echo "<li><label>" .  make_name($r) . "</label>";
                            echo "<input type='text' size=4 name='".$var."' value='" . $$var . "' /></li>\n";
                    }
            }
        
        ?>
            </ul>
        </td>
    </tr>
  </tbody>
</table>