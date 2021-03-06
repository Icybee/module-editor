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

use Icybee\Binding\Core\PrototypedBindings;

/**
 * RTE editor element.
 */
class RTEEditorElement extends Element implements EditorElement
{
	use PrototypedBindings;

	const ACTIONS = '#rteeditorelement-actions';

	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(__DIR__ . '/assets/editor.css');
		$document->js->add(__DIR__ . '/assets/editor.js');
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('textarea', $attributes + [

			'class' => 'editor moo',

			'rows' => 16

		]);
	}

	/**
	 * Alters the dataset with the `base-url`, `actions` and `external-css` attributes.
	 *
	 * @inheritdoc
	 */
	public function alter_dataset(array $dataset)
	{
		$dataset = parent::alter_dataset($dataset);

		$document = $this->app->document;

		$css = $this[self::STYLESHEETS] ?: [];

		$css[] = Document::resolve_url(\Brickrouge\ASSETS . 'brickrouge.css');

		if (!$css)
		{
			$info = \Icybee\Modules\Pages\Module::get_template_info('page.html');

			if (isset($info[1]))
			{
				$css = $info[1];
			}
		}

		array_unshift($css, Document::resolve_url('assets/body.css'));

		$try = \ICanBoogie\DOCUMENT_ROOT . 'public/page.css';

		if (file_exists($try))
		{
			$css[] = Document::resolve_url($try);
		}

		$actions = $this[self::ACTIONS] ?: 'standard';

		if ($actions == 'standard')
		{
			$actions = 'bold italic underline strikethrough | formatBlock justifyleft justifyright justifycenter justifyfull | insertunorderedlist insertorderedlist indent outdent | undo redo | createlink unlink | image | removeformat paste outline toggleview';

			if (0)
			{
				$actions .= ' / tableadd | tableedit | tablerowspan tablerowsplit tablerowdelete | tablecolspan tablecolsplit tablecoldelete';

				$document->css->add('mooeditable/Assets/MooEditable/MooEditable.Table.css');
				$document->js->add('mooeditable/Source/MooEditable/MooEditable.Table.js');
			}
		}
		else if ($actions == 'minimal')
		{
			$actions = 'bold italic underline strikethrough | insertunorderedlist insertorderedlist | undo redo | createlink unlink | removeformat paste toggleview';
		}

		$dataset['base-url'] = '/';
		$dataset['actions'] = $actions;
		$dataset['external-css'] = $css;

		return $dataset;
	}
}
