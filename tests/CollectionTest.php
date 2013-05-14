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

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException Icybee\Modules\Editor\EditorNotDefined
	 */
	public function testEditorNotDefined()
	{
		$c = new Collection();
		$editor = $c['undefined'];
	}

	public function testGetEditor()
	{
		$c = new Collection
		(
			array
			(
				'raw' => __NAMESPACE__ . '\RawEditor'
			)
		);

		$this->assertInstanceOf('Icybee\Modules\Editor\Editor', $c['raw']);
	}

	/**
	 * @expectedException Icybee\Modules\Editor\EditorAlreadyInstantiated
	 */
	public function testCannotModifyInstantiatedEditorDefinition()
	{
		$c = new Collection
		(
			array
			(
				'raw' => __NAMESPACE__ . '\RawEditor'
			)
		);

		$editor = $c['raw'];

		$c['raw'] = __NAMESPACE__ . '\RTEEditor';
	}
}