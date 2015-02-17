<?php

require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/model.php' );
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/users-model.php' );
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/sites-model.php' );
require_once( ORGANIZATION_HUB_PLUGIN_PATH.'/classes/model/upload-model.php' );

require_once( dirname(__FILE__).'/pages/users.php' );
require_once( dirname(__FILE__).'/pages/sites.php' );
require_once( dirname(__FILE__).'/pages/upload.php' );
require_once( dirname(__FILE__).'/pages/log.php' );

require_once( dirname(__FILE__).'/tabs/users/list.php' );
require_once( dirname(__FILE__).'/tabs/users/edit.php' );

require_once( dirname(__FILE__).'/tabs/upload/overview.php' );
require_once( dirname(__FILE__).'/tabs/upload/users.php' );
require_once( dirname(__FILE__).'/tabs/upload/sites.php' );
require_once( dirname(__FILE__).'/tabs/upload/content.php' );

