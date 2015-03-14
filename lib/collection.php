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

use ICanBoogie\PropertyNotDefined;

/**
 * Editor collection.
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * Returns the global editor collection.
	 *
	 * The collection is created from the `editors` config and can be altered with an event hook
	 * on the `Icybee\Modules\Editor\Collection::alter` event.
	 *
	 * @param \ICanBoogie\Core $app
	 *
	 * @return \Icybee\Modules\Editor\Collection
	 */
	static public function prototype_get_editors(\ICanBoogie\Core $app)
	{
		$definitions = (array) $app->configs->synthesize('editors', 'merge');
		$collection = new static($definitions);

		new Collection\AlterEvent($collection);

		return $collection;
	}

	protected $definitions;
	protected $editors;

	/**
	 * Creates the collection.
	 */
	public function __construct(array $definitions=array())
	{
		$this->definitions = $definitions;
	}

	/**
	 * Checks if a editor exists.
	 */
	public function offsetExists($offset)
	{
		if ($offset == 'moo') // COMPAT
		{
			$offset = 'rte';
		}
		else if ($offset == 'adjustimage')
		{
			$offset = 'image';
		}

		return isset($this->definitions[$offset]);
	}

	/**
	 * Returns the definition of an editor.
	 *
	 * @throws EditorNotDefined in attempt to use an undefined editor.
	 */
	public function offsetGet($offset)
	{
		if ($offset == 'moo') // COMPAT
		{
			$offset = 'rte';
		}
		else if ($offset == 'adjustimage')
		{
			$offset = 'image';
		}

		if (isset($this->editors[$offset]))
		{
			return $this->editors[$offset];
		}

		if (!$this->offsetExists($offset))
		{
			throw new EditorNotDefined($offset);
		}

		$class = $this->definitions[$offset];
		$editor = new $class;

		return $this->editors[$offset] = $editor;
	}

	/**
	 * Sets the editor definition.
	 *
	 * @throws EditorAlreadyInstantiated if an editor has already been instanciated with a previous
	 * definition.
	 */
	public function offsetSet($id, $value)
	{
		if (isset($this->editors[$id]))
		{
			throw new EditorAlreadyInstantiated($id);
		}

		$this->definitions[$id] = $value;
	}

	/**
	 * Removes an editor definition.
	 *
	 * @throws EditorAlreadyInstantiated if an editor has already been instanciated the definition.
	 */
	public function offsetUnset($id)
	{
		if (isset($this->editors[$id]))
		{
			throw new EditorAlreadyInstantiated($id);
		}

		unset($this->definitions[$id]);
	}

	/**
	 * Returns an iterator for the editor definitions.
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->definitions);
	}
}

/**
 * Exception thrown in attempt to use an undefined editor.
 *
 * @property-read string $editor_id The identifier of the editor.
 */
class EditorNotDefined extends \ICanBoogie\OffsetNotDefined
{
	private $editor_id;

	public function __construct($editor_id, $code=500, \Exception $previous=null)
	{
		$this->editor_id = $editor_id;

		parent::__construct("Editor not defined: $editor_id.", $code, $previous);
	}

	public function __get($property)
	{
		if ($property == 'editor_id')
		{
			return $this->editor_id;
		}

		throw PropertyNotDefined(array($property, $this));
	}
}

/**
 * Exception thrown in attempt to alter a definition that was used to instantiate an editor.
 *
 * @property-read string $editor_id The identifier of the editor.
 */
class EditorAlreadyInstantiated extends \RuntimeException
{
	private $editor_id;

	public function __construct($editor_id, $code=500, \Exception $previous=null)
	{
		$this->editor_id = $editor_id;

		parent::__construct("An editor has already been instantiated: $editor_id.", $code, $previous);
	}

	public function __get($property)
	{
		if ($property == 'editor_id')
		{
			return $this->editor_id;
		}

		throw PropertyNotDefined(array($property, $this));
	}
}

namespace Icybee\Modules\Editor\Collection;

/**
 * Event class for the `Icybee\Modules\Editor\Collection::alter` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * The event is constructed with the type `alter`.
	 *
	 * @param \Icybee\Modules\Editor\Collection $target
	 */
	public function __construct(\Icybee\Modules\Editor\Collection $target)
	{
		parent::__construct($target, 'alter');
	}
}
