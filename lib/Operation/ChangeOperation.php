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

use ICanBoogie\Errors;
use ICanBoogie\Operation;

use Icybee\Binding\PrototypedBindings;
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

	protected function validate(Errors $errors)
	{
		$request = $this->request;

		if (!$this->key)
		{
			$errors['editor_id'] = $errors->format('The %property is required.', [ 'property' => 'editor_id' ]);
		}

		if (empty($request['selector_name']))
		{
			$errors['selector_name'] = $errors->format('The %property is required.', [ 'property' => 'selector_name' ]);
		}

		if (empty($request['contents_name']))
		{
			$errors['contents_name'] = $errors->format('The %property is required.', [ 'property' => 'contents_name' ]);
		}

		return !$errors->count();
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
