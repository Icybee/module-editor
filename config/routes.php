<?php

namespace Icybee\Modules\Editor;

use ICanBoogie\HTTP\Request;

return [

	'api:editors/new-pane' => [

		'pattern' => '/api/editors/tabbable/new-pane',
		'controller' => TabbableNewPaneOperation::class,
		'via' => Request::METHOD_GET

	]

];
