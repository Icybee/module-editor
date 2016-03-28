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

use Brickrouge\Document;
use Brickrouge\Element;

/**
 * "Patron" editor element.
 */
class PatronEditorElement extends Element implements EditorElement
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(__DIR__ . '/assets/editor.css');
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('textarea', $attributes + [

			'class' => 'form-control editor patron'

		]);
	}
}
