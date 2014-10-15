<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use ICanBoogie\Updater\Update;
use ICanBoogie\Updater\AssertionFailed;

/**
 * @module editor
 */
class Update20120101 extends Update
{
	/**
	 * Replace editor `moo` with editor `rte` in page contents.
	 */
	public function update_editor_moo()
	{
		$model = $this->app->models['pages/contents'];
		$count = $model->filter_by_editor('moo')->count;

		if (!$count)
		{
			throw new AssertionFailed(__FUNCTION__, [ 'moo' ]);
		}

		$model('UPDATE {self} SET editor = "rte" WHERE editor = "moo"');
	}
}