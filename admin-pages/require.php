<?php

require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/model.php' );
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/users-model.php' );
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/sites-model.php' );
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/upload-model.php' );

require_once( dirname(__FILE__).'/pages/users.php' );
require_once( dirname(__FILE__).'/pages/sites.php' );
require_once( dirname(__FILE__).'/pages/upload.php' );
require_once( dirname(__FILE__).'/pages/settings.php' );

require_once( dirname(__FILE__).'/tabs/users/edit.php' );
require_once( dirname(__FILE__).'/tabs/users/list.php' );
require_once( dirname(__FILE__).'/tabs/users/log.php' );
require_once( dirname(__FILE__).'/tabs/users/upload.php' );

require_once( dirname(__FILE__).'/tabs/sites/list.php' );
require_once( dirname(__FILE__).'/tabs/sites/log.php' );

require_once( dirname(__FILE__).'/tabs/upload/list.php' );
require_once( dirname(__FILE__).'/tabs/upload/log.php' );
require_once( dirname(__FILE__).'/tabs/upload/settings.php' );
require_once( dirname(__FILE__).'/tabs/upload/upload.php' );

