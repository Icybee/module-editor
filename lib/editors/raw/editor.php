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
 * "Raw" editor.
 */
class RawEditor implements Editor
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
	 * @return RawEditorElement
	 *
	 * @inheritdoc
	 */
	public function from(array $attributes)
	{
		return new RawEditorElement($attributes);
	}

	/**
	 * Returns content as is.
	 *
	 * @inheritdoc
	 */
	public function render($content)
	{
		return $content;
	}
}
