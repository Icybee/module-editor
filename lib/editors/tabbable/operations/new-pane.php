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

use ICanBoogie\Errors;
use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Icybee\Binding\Core\PrototypedBindings;

/**
 * Returns a new pane for the {@link TabbableEditor}.
 */
class TabbableNewPaneOperation extends Operation
{
	use PrototypedBindings;

	/**
	 * The `control_name` parameter is request.
	 *
	 * @inheritdoc
	 */
	protected function validate(Errors $errors)
	{
		if (!$this->request['control_name'])
		{
			$errors->add('control_name', "The %identifier is required.", [ 'identifier' => 'control_name' ]);
		}

		return true;
	}

	/**
	 * Returns a pane HTML string.
	 *
	 * Adds the following response properties:
	 *
	 * - (string) tab: The tab element associated with the pane.
	 * - (array) assets: The assets required by the elements.
	 *
	 * @inheritdoc
	 */
	protected function process()
	{
		$request = $this->request;
		$properties = [

			'name' => $request['control_name'] . '[' . uniqid() . ']',
			'title' => 'New tab',
			'editor_id' => 'rte',
			'serialized_content' => null

		];

		$tab = TabbableEditorElement::create_tab($properties);
		$pane = TabbableEditorElement::create_pane($properties);

		$tab->add_class('active');
		$pane->add_class('active');

		$tab = (string) $tab;
		$pane = (string) $pane;

		$this->response['tab'] = $tab;
		$this->response['assets'] = $this->app->document->assets;

		return $pane;
	}
}
