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

use function ICanBoogie\app;
use function ICanBoogie\format;
use function ICanBoogie\normalize;

/**
 * "Widgets" editor.
 */
class WidgetsEditor implements Editor
{
	/**
	 * Returns a JSON string.
	 *
	 * @inheritdoc
	 */
	public function serialize($content)
	{
		return json_encode(array_keys($content));
	}

	/**
	 * Returns unserialized JSON content.
	 *
	 * @inheritdoc
	 */
	public function unserialize($serialized_content)
	{
		return (array) json_decode($serialized_content);
	}

	/**
	 * @return WidgetsEditorElement
	 *
	 * @inheritdoc
	 */
	public function from(array $attributes)
	{
		return new WidgetsEditorElement($attributes);
	}

	/**
	 * Renders selected widgets.
	 *
	 * @inheritdoc
	 */
	public function render($content)
	{
		if (!$content)
		{
			return null;
		}

		$availables = app()->configs->synthesize('widgets', 'merge');

		if (!$availables)
		{
			return null;
		}

		$selected = array_flip($content);
		$undefined = array_diff_key($selected, $availables);

		if ($undefined)
		{
			throw new \Exception(format('Undefined widget(s): :list', [ ':list' => implode(', ', array_keys($undefined)) ]));
		}

		$list = array_intersect_key($availables, $selected);

		if (!$list)
		{
			return null;
		}

		$html = '';
		$list = array_merge($selected, $list);

		foreach ($list as $id => $widget)
		{
			$html .= '<div id="widget-' . normalize($id) . '" class="widget">' . $this->render_widget($widget, $id) . '</div>';
		}

		return $html;
	}

	private function render_widget($widget, $id)
	{
		if (isset($widget['file']))
		{
			$file = $widget['file'];

			if (substr($file, -4, 4) == '.php')
			{
				ob_start();

				require $file;

				return ob_get_clean();
			}
			else if (substr($file, -5, 5) == '.html')
			{
				$patron = \Patron\get_patron();

				return $patron(file_get_contents($file), null, [ 'file' => $file ]);
			}
			else
			{
				throw new \Exception(format('Unable to process file %file, unsupported type', [ '%file' => $file ]));
			}
		}
		else if (isset($widget['module']) && isset($widget['block']))
		{
			return app()->modules[$widget['module']]->getBlock($widget['block']);
		}
		else
		{
			throw new \Exception(format('Unable to render widget %widget, its description is invalid.', [ '%widget' => $id ]));
		}
	}
}
