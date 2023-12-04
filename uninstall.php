<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}


require_once(__DIR__ . '/db.php');

TagGalleryDB::drop_table();