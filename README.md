# Editor 

[![Packagist](https://img.shields.io/packagist/v/icybee/module-editor.svg)](https://packagist.org/packages/icybee/module-editor)
[![Build Status](https://img.shields.io/travis/Icybee/module-editor.svg)](http://travis-ci.org/Icybee/module-editor)
[![HHVM](https://img.shields.io/hhvm/Icybee/module-editor.svg)](http://hhvm.h4cc.de/package/Icybee/module-editor)
[![Code Quality](https://img.shields.io/scrutinizer/g/Icybee/module-editor.svg)](https://scrutinizer-ci.com/g/Icybee/module-editor)
[![Code Coverage](https://img.shields.io/coveralls/Icybee/module-editor.svg)](https://coveralls.io/r/Icybee/module-editor)
[![Downloads](https://img.shields.io/packagist/dt/icybee/module-editor.svg)](https://packagist.org/packages/icybee/module-editor/stats)

The Editor module (`editor`) provides an API to manage and use editors, and comes with several
editors.

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





## Editors

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

namespace ICanBoogie\Modules\Editor;

$content = "Madonna!";
$editor = new TextEditor;
$serialized_content = $editor->serialize($content);

// the serialized content can be stored in the database 
```

Editors only work with unserialized contents. If the content is to be rendered or used as the value
of a UI element, it needs to be unserialized:

```php
<?php

namespace ICanBoogie\Modules\Editor;

// $serialized_content is coming from the database

$editor = new TextEditor;
$content = $editor->unserialize($serialized_content);
$rendered_content = $editor->render($content);
```




### Rendering content

Content is rendered using the `render()` method, which returns a string, or an object that
can be used as a string. For instance, the `render()` method of the `image` editor takes
the identifier of an image and returns an active record that is rendered into an `IMG`
element when used as a string.

Thus, if the editor is asked to render the content, an active record is returned. Used as a string
the active record is rendered as an HTML string. But it could be used to obtain a thumbnail
instead:

```php
<?php

namespace ICanBoogie\Modules\Editor;

$editor = new ImageEditor;
$image = $editor->render('12');

if ($image)
{
    echo $image->thumbnail('article-view');
}
```





### GUI element

Each editor provides a GUI element that is used to edit the supported content type. The element
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
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes + [
        
            'class' => 'editor editor--raw'
            
        ]);
    }
}
```

The UI element must be instances of the [Element](http://brickrouge.org/docs/class-Brickrouge.Element.html)
class, or one of its subclasses. For instance, `TextEditorElement` extends [Text][]
which extends [Element](http://brickrouge.org/docs/class-Brickrouge.Element.html). The many
attributes of the [Element](http://brickrouge.org/docs/class-Brickrouge.Element.html) class
can be used to obtain a satisfactory element:

```php
<?php

use Brickrouge\Element;
use Brickrouge\Group;

$editor = new TextEditor;
$element = $editor->from([

    Group::LABEL => 'Title',
    Element::REQUIRED => true,
    
    'name' => 'title',
    'value' => $editor->unserialize($serialized_content)
    
]);
```

Note that the content is specified to the GUI element using the `value` attribute.





## Editor collection

An editor collection contains the definition of the available editors. It is used to instantiate
editors, and by extension their GUI element:

```php
<?php

namespace Icybee\Modules\Editor;

use Brickrouge\Group;
use Brickrouge\Element;

$editors = new Collection([
	
	'rte' => RTEEditor::class,
	'textmark' => TextmarkEditor::class,
	'raw' => RawEditor::class,

]);

$editor = $editors['rte'];

$editor_element = $editors['rte']->from([

	Group::LABEL => "Body",
	Element::REQUIRED => true

]);
```

An editor definition can be modified until it has been used to instantiate an editor. A
`EditorAlreadyInstantiated` exception is thrown in attempt to modify a definition that was
used to instantiate an editor. The `EditorNotDefined` exception is thrown in attempt to obtain
an editor whose definition is not defined.





### The _core_ collection

Although custom collections can be created to manage editors, it is recommended to use the _core_
collection which is attached to the _core_ object through a lazy getter:

```php
<?php

$app->editors;

$editor = $app->editors['rte'];

$editor_element = $app->editors['rte']->from([
	Group::LABEL => "Body",
	Element::REQUIRED => true
]);
```

This collection is created from the `editors` config and can be altered by attaching an event hook
to the `Icybee\Modules\Editor\Collection::alter` event.





#### Defining the editors of the _core_ collection

The `editors` config is used to define the editors of the _core_ collection. It is recommended to
define editors this way, unless you don't want an editor to be available to the whole CMS.

```php
<?php

namespace Icybee\Modules\Editor;

return [

	'rte' => RTEEditor::class,
	'textmark' => TextmarkEditor::class,
	'raw' => RawEditor::class,
	'text' => TextEditor::class,
	'patron' => PatronEditor::class,
	'php' => PHPEditor::class,
	'image' => ImageEditor::class,
	'node' => NodeEditor::class,
	'widgets' => WidgetsEditor::class,
	'tabbable' => TabbableEditor::class

];
```




#### Altering the _core_ collection

Third parties may use the The `Icybee\Modules\Editor\Collection::alter` event of class
`Icybee\Modules\Editor\Collection\AlterEvent` to alter the _core_ collection once it has been
created with the `editors` config.

```php
<?php

use Icybee\Modules\Editor\Collection;

$app->events->attach(function(Collection\AlterEvent $event, Collection $target) {

	$target['rte'] = 'MyRTEEditor';

});
```





## A multi-editor

Having so many editors to play with is very nice and it would be a shame to provide only an RTE
editor when a Markdown editor or a raw HTML editor could also be used, if not prefered by the user.
In order to answer to this situation, the module provides a multi-editor, a shell that can swap
editors to edit content.

The [Contents](https://github.com/Icybee/module-contents) module uses this editor so that the user
can decide which editor to use to edit and render its content:

```php
<?php

namespace Icybee\Modules\Contents;

// …

use Icybee\Modules\Editor\MultiEditorElement;

class EditBlock extends \Icybee\Modules\Nodes\EditBlock
{
	protected function get_children()
	{
		// …
		
		Content::BODY => new MultiEditorElement($values['editor'] ? $values['editor'] : $default_editor, [
			
			Element::LABEL_MISSING => 'Contents',
			Element::GROUP => 'contents',
			Element::REQUIRED => true,

			'rows' => 16
			
		])
		
		// …
	}
}
```

The `tabbable` editor uses this editor for each of its tabs, allowing the user to use
an RTE editor in the first, a Markdown editor is the second and a `tabbable` editor in
the third (Inception !).

Currently using the multi-editor requires an extra field to store the editor configured by the
user. Its name can be specified using the `SELECTOR_NAME` attribute, it defaults to `editor`.





## Requirement

The package requires PHP 5.5 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```
$ composer require icybee/module-editor
```

**Note:** This module is part of the modules required by [Icybee](http://icybee.org).





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-editor), its repository can be
cloned with the following command line:

	$ git clone git://github.com/Icybee/module-editor.git editor





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/Icybee/module-editor.svg)](http://travis-ci.org/Icybee/module-editor)
[![Code Coverage](https://img.shields.io/coveralls/Icybee/module-editor.svg)](https://coveralls.io/r/Icybee/module-editor)





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## License

The module is licensed under the New BSD License - See the LICENSE file for details.





[Text]: http://brickrouge.org/docs/class-Brickrouge.Text.html
