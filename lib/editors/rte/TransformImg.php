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

use Brickrouge\Element;
use ICanBoogie\Modules\Thumbnailer\Version;
use Icybee\Modules\Images\Image;

/**
 * Transforms an `IMG` markup.
 */
class TransformImg
{
	private $record_provider;

	public function __construct(callable $record_provider)
	{
		$this->record_provider = $record_provider;
	}

	/**
	 * Transforms an `IMG` markup.
	 *
	 * @param string $markup
	 *
	 * @return string
	 */
	public function __invoke($markup)
	{
		$attributes = $this->extract_attributes($markup) + [

			'width' => null,
			'height' => null,
			'data-nid' => null,
			'data-lightbox' => null

		];

		$nid = $attributes['data-nid'];

		if (!$nid)
		{
			return $markup;
		}

		$v = Version::from_uri($attributes['src']);
		$image = $this->find_image($nid);
		$attributes['src'] = $image->thumbnail($v)->url;

		#

		$image_url = null;

		if ($attributes['data-lightbox'])
		{
			$attributes['src'] = preg_replace('#&amp;lightbox=true#', '', $attributes['src']);
			$image_url = $image->url('show');
		}

		unset($attributes['data-nid']);
		unset($attributes['data-lightbox']);

		$rc = (string) new Element('img', $attributes);

		if ($image_url)
		{
			$rc = '<a href="' . \ICanBoogie\escape($image_url) . '" rel="lightbox[]">' . $rc . '</a>';
		}

		return $rc;
	}

	/**
	 * Extract markup attributes.
	 *
	 * @param string $markup
	 *
	 * @return array
	 */
	protected function extract_attributes($markup)
	{
		preg_match_all('#([\w\-]+)\s*=\s*\"([^"]+)"#', $markup, $attributes);

		$attributes = array_combine($attributes[1], $attributes[2]);
		$attributes = array_map(function($v) { return html_entity_decode($v, ENT_COMPAT, \ICanBoogie\CHARSET); }, $attributes);

		return $attributes;
	}

	/**
	 * @param int $nid
	 *
	 * @return Image
	 */
	protected function find_image($nid)
	{
		$provider = $this->record_provider;

		return $provider($nid);
	}
}
