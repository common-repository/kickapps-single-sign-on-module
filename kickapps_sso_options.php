<?php
/*
    KickApps Single Sign-On Wordpress Plugin
    Copyright (C) 2007  KickApps Inc.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/
?>
<div class="wrap">
<h2>KickApps Single Sign-On Options</h2>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options') ?>
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></p>
<table class="optiontable">
<tr valign="top">
<th scope="row"><?php _e('KickApps Site Name:') ?></th>
<td><input type="text" name="kickapps_site_name" value="<?php echo get_option('kickapps_site_name'); ?>" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('KickApps Email:') ?></th>
<td><input type="text" name="kickapps_email" value="<?php echo get_option('kickapps_email'); ?>" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('KickApps Username:') ?></th>
<td><input type="text" name="kickapps_username" value="<?php echo get_option('kickapps_username'); ?>" size="40" /></td>
</tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="kickapps_site_name,kickapps_email,kickapps_username" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></p>
</form>
</div>
