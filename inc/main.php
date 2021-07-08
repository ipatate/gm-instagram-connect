<?php

namespace GMInstagramConnect\inc;

require_once dirname(__FILE__) . '/config_panel.php';
require_once dirname(__FILE__) . '/routes.php';
require_once dirname(__FILE__) . '/scripts.php';

function main()
{
	enqueue_scripts();
}
