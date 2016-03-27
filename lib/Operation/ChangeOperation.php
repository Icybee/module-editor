<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor\Operation;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Operation;

use Icybee\Binding\Core\PrototypedBindings;
use Icybee\Modules\Editor\MultiEditorElement;

/**
 * Changes multieditor editor.
 */
class ChangeOperation extends Operation
{
	use PrototypedBindings;

	protected function get_controls()
	{
		return [

			self::CONTROL_AUTHENTICATION => true

		] + parent::get_controls();
	}

	/**
	 * @inheritdoc
	 */
	protected function validate(ErrorCollection $errors)
	{
		$request = $this->request;

		if (!$this->key)
		{
			$errors->add('editor_id', "The %property is required.", [ 'property' => 'editor_id' ]);
		}

		if (empty($request['selector_name']))
		{
			$errors->add('selector_name', "The %property is required.", [ 'property' => 'selector_name' ]);
		}

		if (empty($request['contents_name']))
		{
			$errors->add('contents_name', "The %property is required.", [ 'property' => 'contents_name' ]);
		}

		return $errors;
	}

	protected function process()
	{
		$request = $this->request;

		$editor = (string) new MultiEditorElement($this->key, [

			MultiEditorElement::SELECTOR_NAME => $request['selector_name'],

			'name' => $request['contents_name'],
			'value' => $request['contents']

		]);

		$this->response['assets'] = $this->app->document->assets;

		return $editor;
	}
}
