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
 * "Textmark" editor element.
 */
class TextmarkEditorElement extends Element implements EditorElement
{
	static protected function add_assets(Document $document)
	{
		$document->css->add(__DIR__ . '/element.css');
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('textarea', $attributes + [

			'class' => 'editor editor--markdown form-control'

		]);
	}
}
