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

use ICanBoogie\Modules\Thumbnailer\Thumbnail;
use ICanBoogie\Operation;

use Brickrouge\Element;

/**
 * RTE editor.
 */
class RTEEditor implements Editor
{
	/**
	 * Returns the content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns the serialized content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}

	/**
	 * Replaces managed images with width or height attributes by thumbnails, and transform markup
	 * when the original image can be displayed in a lightbox.
	 *
	 * @see Icybee\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		return preg_replace_callback
		(
			'#<img\s+[^>]+>#', function($match)
			{
				global $core;

				preg_match_all('#([\w\-]+)\s*=\s*\"([^"]+)#', $match[0], $attributes);

				$attributes = array_combine($attributes[1], $attributes[2]);
				$attributes = array_map(function($v) { return html_entity_decode($v, ENT_COMPAT, \ICanBoogie\CHARSET); }, $attributes);
				$attributes += array
				(
					'width' => null,
					'height' => null,
					'data-nid' => null
				);

				$w = $attributes['width'];
				$h = $attributes['height'];
				$nid = $attributes['data-nid'];
				$src = $attributes['src'];

				$route = $core->routes->find($attributes['src'], $captured);

				if ($route && $route->id == 'api:files:get' && ($w || $h))
				{
					$thumbnail = new Thumbnail($core->models['images'][$nid ?: hexdec($captured['hexnid'])], "w:{$w};h:{$h};");

					$attributes['src'] = $thumbnail->url;
				}

				$path = null;

				if (isset($attributes['data-lightbox']) && $nid)
				{
					$attributes['src'] = preg_replace('#(\?|\&)lightbox=(on|true)#', '', $attributes['src']);
					$path = $core->models['images'][$nid]->url('get');
				}

				unset($attributes['data-nid']);
				unset($attributes['data-lightbox']);

				$rc = (string) new Element('img', $attributes);

				if ($path)
				{
					$rc = '<a href="' . \ICanBoogie\escape($path) . '" rel="lightbox[]">' . $rc . '</a>';
				}

				return $rc;
			},

			$content
		);
	}

	/**
	 * @return RTEEditorElement
	 *
	 * @see Icybee\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new RTEEditorElement($attributes);
	}
}