<?php defined( 'ABSPATH' ) || exit;
require_once IPYTW_PLUGIN_DIR_PATH . 'functions.php'; // Подключаем файл функций

require_once IPYTW_PLUGIN_DIR_PATH . 'common-libs/icopydoc-useful-functions-1-1-8.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'common-libs/wc-add-functions-1-0-2.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'common-libs/class-icpd-feedback-1-0-3.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'common-libs/class-icpd-promo-1-1-0.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'common-libs/class-icpd-set-admin-notices.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'common-libs/backward-compatibility.php';

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/class-import-from-yml.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/class-ipytw-data-arr.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/class-ipytw-error-log.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/class-ipytw-interface-hocked.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/pages/debug-page/class-ipytw-debug-page.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/pages/extensions-page/class-ipytw-extensions-page.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/pages/settings-page/class-ipytw-settings-page.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/pages/settings-page/class-ipytw-wp-list-table.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/updates/class-ipytw-plugin-form-activate.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/system/updates/class-ipytw-plugin-upd.php';

require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-xml.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-xml-helper.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-parsing.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-create-product-attributes.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-download-pictures.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-add-simple-proudct.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-add-variable-proudct.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-import-add-external-proudct.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-remove-attachments-pictures.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-sorting-categories.php';
require_once IPYTW_PLUGIN_DIR_PATH . 'classes/import/class-ipytw-xml-reader.php';