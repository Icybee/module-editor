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

/**
 * "Image" editor.
 */
class ImageEditor implements Editor
{
	/**
	 * Returns content as is.
	 *
	 * @inheritdoc
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns serialized content as is.
	 *
	 * @inheritdoc
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}
	/**
	 * @return ImageEditorElement
	 *
	 * @inheritdoc
	 */
	public function from(array $attributes)
	{
		return new ImageEditorElement($attributes);
	}

	/**
	 * Returns image active record.
	 *
	 * @return \Icybee\Modules\Images\Image
	 *
	 * @inheritdoc
	 */
	public function render($content)
	{
		return $content ? \ICanBoogie\app()->models['images'][$content] : null;
	}
}
