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

use ICanBoogie\Binding\PrototypedBindings;

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

/**
 * A selector for the available editors.
 */
class SelectorElement extends Element
{
	use PrototypedBindings;

	public function __construct(array $attributes = [])
	{
		$options = [];

		foreach ($this->app->editors as $id => $editor)
		{
			$options[$id] = $this->t($id, [], [ 'scope' => 'editor_title' ]);
		}

		parent::__construct('select', $attributes + [

			Element::OPTIONS => $options

		]);
	}

	/**
	 * @throws ElementIsEmpty if the element has no options.
	 *
	 * @inheritdoc
	 */
	protected function render_outer_html()
	{
		if (!$this[Element::OPTIONS])
		{
			throw new ElementIsEmpty;
		}

		return parent::render_outer_html();
	}
}
