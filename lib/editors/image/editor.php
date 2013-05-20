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
	 * @see Icybee\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns serialized content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}
	/**
	 * @return ImageEditorElement
	 *
	 * @see Icybee\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new ImageEditorElement($attributes);
	}

	/**
	 * Returns image active record.
	 *
	 * @return Icybee\Modules\Images\Image
	 *
	 * @see Icybee\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		global $core;

		return $content ? $core->models['images'][$content] : null;
	}
}