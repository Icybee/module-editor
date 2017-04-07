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

use function ICanBoogie\app;

/**
 * RTE editor.
 */
class RTEEditor implements Editor
{
	/**
	 * Returns the content as is.
	 *
	 * @inheritdoc
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns the serialized content as is.
	 *
	 * @inheritdoc
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}

	/**
	 * Replaces managed images with width or height attributes by thumbnails, and transform markup
	 * when the original image can be displayed in a lightbox.
	 *
	 * @inheritdoc
	 */
	public function render($content)
	{
		if (strpos($content, '<img ') === false)
		{
			return $content;
		}

		$app = app();

		$transform_img = new TransformImg(function($id) use ($app) {

			return $app->models['images'][$id];

		});

		return preg_replace_callback('#<img\s+[^>]+>#', function($match) use ($transform_img) {

			return $transform_img($match[0]);

		}, $content);
	}

	/**
	 * @return RTEEditorElement
	 *
	 * @inheritdoc
	 */
	public function from(array $attributes)
	{
		return new RTEEditorElement($attributes);
	}
}
