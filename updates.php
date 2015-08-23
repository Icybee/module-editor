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
use ICanBoogie\Modules\Thumbnailer\Version;

/**
 * @module editor
 */
class Update20120101 extends Update
{
	/**
	 * Replace editor `moo` with editor `rte` in contents.
	 */
	public function update_editor_moo_in_contents()
	{
		$model = $this->app->models['contents'];
		$count = $model->filter_by_editor('moo')->count;

		if (!$count)
		{
			throw new AssertionFailed(__FUNCTION__, [ 'moo' ]);
		}

		$model('UPDATE {self} SET editor = "rte" WHERE editor = "moo"');
	}

	/**
	 * Replace editor `moo` with editor `rte` in page contents.
	 */
	public function update_editor_moo_in_page_contents()
	{
		$model = $this->app->models['pages/contents'];
		$count = $model->filter_by_editor('moo')->count;

		if (!$count)
		{
			throw new AssertionFailed(__FUNCTION__, [ 'moo' ]);
		}

		$model('UPDATE {self} SET editor = "rte" WHERE editor = "moo"');
	}

	/**
	 * Replace editor `adjustnode` with editor `node` in contents.
	 */
	public function update_editor_adjustnode_in_contents()
	{
		$model = $this->app->models['contents'];
		$count = $model->filter_by_editor('adjustnode')->count;

		if (!$count)
		{
			throw new AssertionFailed(__FUNCTION__, [ 'adjustnode' ]);
		}

		$model('UPDATE {self} SET editor = "node" WHERE editor = "adjustnode"');
	}

	/**
	 * Replace editor `adjustnode` with editor `node` in page contents.
	 */
	public function update_editor_adjustnode_in_page_contents()
	{
		$model = $this->app->models['pages/contents'];
		$count = $model->filter_by_editor('adjustnode')->count;

		if (!$count)
		{
			throw new AssertionFailed(__FUNCTION__, [ 'adjustnode' ]);
		}

		$model('UPDATE {self} SET editor = "node" WHERE editor = "adjustnode"');
	}

	public function update_editor_adjustnode_data()
	{

	}
}

/**
 * @module editor
 */
class Update20140619 extends Update
{
	public function update_rte_thumbnails_in_pages()
	{
		$model = $this->app->models['pages/contents'];

		/* @var $contents \Icybee\Modules\Pages\Content[] */

		$contents = $model
		->filter_by_editor('rte')
		->where('content LIKE "%/api/images/%/thumbnail?%"')
		->all;

		if (!$contents)
		{
			throw new AssertionFailed(__FUNCTION__, [ "/api/images/%/thumbnail?" ]);
		}

		foreach ($contents as $content)
		{
			$html = $content->content;
			$html = preg_replace_callback('#(/api/images/(\d+)/thumbnail\?[^"\s]+)#', function($matches) {

				list( ,$url, $nid) = $matches;

				$encoded = strpos($url, '&amp');

				if ($encoded)
				{
					$url = html_entity_decode($url);
				}

				$components = parse_url($url);
				$path = $components['path'];
				$query = $components['query'];

				parse_str($query, $query);

				if (isset($query['quality']) && $query['quality'] == 80)
				{
					unset($query['quality']);
				}

				if (isset($query['q']) && $query['q'] == 80)
				{
					unset($query['q']);
				}

				$url = "/api/images/$nid/" . new Version($query);

				return $url;

			}, $html);

			$content->content = $html;
			$content->save();
		}
	}
}
