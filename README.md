# Editor [![Build Status](https://travis-ci.org/Icybee/module-editor.png?branch=master)](https://travis-ci.org/Icybee/module-editor)

The Editor module (`editor`) provides an API to manage and use editors, as well as several editors.

Different editors are used to enter the contents of the CMS [Icybee](http://icybee.org/). Whether
it's the body of an article or its excerpt, the description of a file, the content of a page… from
the simplest string to the different elements of a schema, or some element with rich text. These
editors allow the user to enter varied types of content. They are also used to select images,
records, forms, the view to display as the body of a page, and many other things.

The following editors are provided by the module:

* `rte` - A Rich Text Editor.
* `textmark` - An editor for the TextMark/Markdown syntax.
* `image` - A selector for a managed image.
* `node` - A selector for a node.
* `patron` - An editor for the Patron template engine.
* `php` - An editor for PHP code.
* `raw` - Lets you use HTML code.
* `tabbable` - An editor with tabbed content, where each tab can use its own editor.
* `widgets` - An editor that lets you pick and sort widgets.





## Defining an editor

The API provided by the module defines the interface common to all editors. They
must be able to serialize/unserialize and render the content type they support. They also must
provide the UI element used to edit that content. For instance, this is the `TextEditor` class
that provides the `text` editor:

```php
<?php

namespace ICanBoogie\Modules\Editor;

class TextEditor implements Editor
{
	public function serialize($content)
	{
		return $content;
	}

	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}

	public function render($content)
	{
		return $content;
	}

	public function from(array $attributes)
	{
		return new TextEditorElement($attributes);
	}
}
```

Because the content type supported by this editor is very basic, the (un)serialize and render
methods are very simple. Editors supporting more complex content types may use arrays and
serialize their content using JSON.





### Serialize and unserialize

The `serialize()` method is used to transform the internal representation of the content type
supported by the editor into a plain string that can be easily stored in a database:

```php
<?php

$content = "Madonna!";
$editor = $core->editor['text'];
$serialized_content = $editor->serialize($content);

// the serialized content can be stored in the database 
```

Editors only work with unserialized contents. If the content is to be rendered or used as the value
of a UI element, it needs to be unserialized:

```php
<?php

// $serialized_content is coming from the database

$editor = $core->editor['text'];
$content = $editor->unserialize($serialized_content);
$rendered_content = $editor->render($content);
```




### Rendering content

Content is rendered using the `render()` method, which returns a string, or an object that
can be used as a string. For instance, the `render()` method of the `image` editor takes
the identifier of an image and returns an active record that is rendered into an `IMG`
element when used as a string.

Thus, if we ask the editor to render the content we'll obtain an active record. If we use it
as a string we'll obtain an HTML string. But we could use the object to obtain a thumbnail
instead:

```php
<?php

$editor = $core->editor['image'];
$image = $editor->render('12');

if ($image)
{
    echo $image->thumbnail('article-view');
}
```





### UI element

Each editor provides a UI element that is used to edit the supported content type. The element
is created using the `from()` method. For instance, `TextEditor` creates instances of
`TextEditorElement`:

```php
<?php

namespace ICanBoogie\Modules\Editor;

class TextEditor implements Editor
{
    // …

    public function from(array $attributes)
    {
        return new TextEditorElement($attributes);
    }
}
```

```php
<?php

namespace ICanBoogie\Modules\Editor;

class TextEditorElement extends \Brickrouge\Text implements EditorElement
{
    public function __construct(array $attributes=array())
    {
        parent::__construct
        (
            $attributes + array
            (
                'class' => 'editor editor--raw'
            )
        );
    }
}
```

The returned elements must be instances of the [Element](http://brickrouge.org/docs/class-Brickrouge.Element.html)
class, or one of its subclasses. For instance, `TextEditorElement` extends [Text]([Element](http://brickrouge.org/docs/class-Brickrouge.Text.html)
which extends [Element](http://brickrouge.org/docs/class-Brickrouge.Element.html). One can use the
many attributes of the [Element](http://brickrouge.org/docs/class-Brickrouge.Element.html) class
to obtain a fatisfactory element:

```php
<?php

use Brickrouge\Element;
use Brickrouge\Group;

$editor = $core->editors['text'];
$element = $editor->from(array
(
    Group::LABEL => 'Title',
    Element::REQUIRED => true,
    
    'name' => 'title',
    'value' => $editor->unserialize($serialized_content)
));
```

Note that the content is specified to the editor element using the `value` attribute.





## The editor collection

The editor collection contains the definition of the available editors. It is used to instantiate
editors, and by extension their UI element:

```php
<?php

namespace Icybee\Modules\Editors;

use Brickrouge\Group;
use Brickrouge\Element;

$editors = new Collection
(
	array
	(
		'rte' => __NAMESPACE__ . '\RTEEditor',
		'textmark' => __NAMESPACE__ . '\TextmarkEditor',
		'raw' => __NAMESPACE__ . '\RawEditor',
	)
);

$editor = $editors['rte'];
$editor_element = $editors['rte']->form(array(
	Group::LABEL => "Body",
	Element::REQUIRED => true
));
```

An editor definition can be modified until it has been used to instanciate an editor. A
`EditorAlreadyInstantiated` exception is thrown in attempt to modify a definition that was
used to instantiate an editor. The `EditorNotDefined` exception is thrown in attempt to obtain
an editor whose definition is not defined.





### The _core_ collection

Although one can create and manage its own editor collection, it is recommended to use the _core_
collection which is attached to the _core_ object through a lazy getter:

```php
<?php

$core->editors;
```

This collection is created from the `editors` config and can be altered by attaching an event hook
to the `Icybee\Modules\Editor\Collection::alter` event.





### Defining the editors of the _core_ collection

The `editors` config is used to define the editors of the _core_ collection.

```php
<?php

namespace Icybee\Modules\Editor;

return array
(
	'rte' => __NAMESPACE__ . '\RTEEditor',
	'textmark' => __NAMESPACE__ . '\TextmarkEditor',
	'raw' => __NAMESPACE__ . '\RawEditor',
	'text' => __NAMESPACE__ . '\TextEditor',
	'patron' => __NAMESPACE__ . '\PatronEditor',
	'php' => __NAMESPACE__ . '\PHPEditor',
	'image' => __NAMESPACE__ . '\ImageEditor',
	'node' => __NAMESPACE__ . '\NodeEditor',
	'widgets' => __NAMESPACE__ . '\WidgetsEditor',
	'tabbable' => __NAMESPACE__ . '\TabbableEditor'
);
```




### Altering the _core_ collection

Third parties may use the The `Icybee\Modules\Editor\Collection::alter` event of class
`Icybee\Modules\Editor\Collection\AlterEvent` to alter the _core_ collection once it has been
created with the `editors` config.

```php
<?php

use Icybee\Modules\Editors\Collection;

$core->events->attach(function(Collection\AlterEvent $event, Collection $target) {

	$target['rte'] = 'MyRTEEditor';

});
```




## Requirement

The package requires PHP 5.3 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require":
	{
		"icybee/module-editor": "*"
	}
}
```

Note: This module is part of the modules required by [Icybee](http://icybee.org).





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-editor), its repository can be
cloned with the following command line:

	$ git clone git://github.com/Icybee/module-editor.git editor





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/module-editor.png?branch=master)](https://travis-ci.org/Icybee/module-editor)





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## License

The module is licensed under the New BSD License - See the LICENSE file for details.