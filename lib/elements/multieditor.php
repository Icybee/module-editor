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
 * An element that can change its editor.
 *
 * @property Element $editor The editor element.
 *
 * @property-read \ICanBoogie\Core|Binding\CoreBindings $app
 */
class MultiEditorElement extends Element
{
	use PrototypedBindings;

	const EDITOR_TAGS = '#meditor-tags';
	const SELECTOR_NAME = '#meditor-selector-name';
	const NOT_SWAPPABLE = '#meditor-not-wappable';

	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(__DIR__ . '/assets/elements.css');
		$document->js->add(__DIR__ . '/assets/elements.js');
	}

	protected $editor_id;

	public function __construct($editor, array $attributes)
	{
		$this->editor_id = $editor ? $editor : 'rte';

		parent::__construct('div', $attributes + [

			self::SELECTOR_NAME => 'editor',

			'class' => 'editor-wrapper'

		]);
	}

	protected function get_editor()
	{
		$editor_id = $this->editor_id;
		$editor = $this->app->editors[$editor_id];
		$element = $editor->from(($this[self::EDITOR_TAGS] ?: []) + [

			Element::REQUIRED => $this[self::REQUIRED],
			Element::DEFAULT_VALUE => $this[self::DEFAULT_VALUE],

			'name' => $this['name'],
			'value' => $this['value']

		]);

		if ($element->type == 'textarea')
		{
			$rows = $this['rows'];

			if ($rows !== null)
			{
				$element['rows'] = $rows;
			}
		}

		return $element;
	}

	/**
	 * Adds the `contents-name` and `selector-name` properties.
	 *
	 * @inheritdoc
	 */
	protected function alter_dataset(array $dataset)
	{
		$dataset = parent::alter_dataset($dataset);

		$dataset['contents-name'] = $this['name'];
		$dataset['selector-name'] = $this[self::SELECTOR_NAME];

		return $dataset;
	}

	/**
	 * The inner HTML of the element includes the editor element and the selector element.
	 *
	 * If the editor is not swappable an hidden element is used instead of the selector element.
	 *
	 * @inheritdoc
	 */
	protected function render_inner_html()
	{
		$html = (string) $this->editor;
		$editor_id = $this->editor_id;

		if ($this[self::NOT_SWAPPABLE])
		{
			$html .= new Element('hidden', [

				'name' => $this[self::SELECTOR_NAME],
				'value' => $editor_id

			]);
		}
		else
		{
			$options = (string) new SelectorElement([

				Element::LABEL => 'Editor',
				Element::LABEL_POSITION => 'before',

				'name' => $this[self::SELECTOR_NAME],
				'class' => 'editor-selector form-control form-control-inline',
				'value' => $editor_id

			]);

			if ($options)
			{
				$html .= '<div class="editor-options clearfix"><div style="float: right">' . $options . '</div></div>';
			}
		}

		return $html;
	}
}
