<?php

namespace Icybee\Modules\Editor;

use ICanBoogie\Module\Descriptor;

return array
(
	Descriptor::CATEGORY => 'features',
	Descriptor::DESCRIPTION => "Provides an API to manage and use editors.",
	Descriptor::PERMISSION => false,
	Descriptor::REQUIRED => true,
	Descriptor::NS => __NAMESPACE__,
	Descriptor::TITLE => "Editor API"
);
