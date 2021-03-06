/*
---

script: MooEditable.Image.js

description: Extends MooEditable to insert image with manipulation options.

license: MIT-style license

authors:
- Olivier Laviale

requires:
# - MooEditable
# - MooEditable.UI
# - MooEditable.Actions

provides: [MooEditable.UI.ImageDialog, MooEditable.Actions.image]

usage: |
	Add the following tags in your html
	<link rel="stylesheet" href="MooEditable.css">
	<link rel="stylesheet" href="MooEditable.Image.css">
	<script src="mootools.js"></script>
	<script src="MooEditable.js"></script>
	<script src="MooEditable.Image.js"></script>

	<script>
	window.addEvent('domready', function(){
		var mooeditable = $('textarea-1').mooEditable({
			actions: 'bold italic underline strikethrough | image | toggleview'
		});
	});
	</script>

...
*/

MooEditable.UI.ImageDialog = (function() {

	var DEFAULT_IMAGE =

		'data:image/gif;base64,' +
		'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ' +
		'bWFnZVJlYWR5ccllPAAAAAZQTFRF////IiIiHNlGNAAAAAF0Uk5TAEDm2GYAAAAeSURBVHjaYmBk' +
		'YMCPKJVnZBi1YtSKUSuGphUAAQYAxEkBVsmDp6QAAAAASUVORK5CYII='

	return new Class
	({
		Extends: MooEditable.UI.Dialog,

		initialize: function(editor)
		{
			this.editor = editor
			this.unique = Math.random()

			this.dummy_el = new Element('div', { styles: { display: 'none' }})
		},

		toElement: function()
		{
			return this.dummy_el
		},

		click: function()
		{
			this.fireEvent('click', arguments)

			return this
		},

		close: function()
		{
			if (this.popover)
			{
				this.popover.hide()
			}

			this.fireEvent('close', this)

			return this
		},

		open: function()
		{
			// Get the node to edit. If none a new one is created with the default image and
			// inserted

			this.node = this.editor.selection.getNode()

			if (!this.node || this.node.get('tag') != 'img')
			{
				this.node = this.editor.doc.createElement('img')
				this.node.src = DEFAULT_IMAGE
				this.editor.selection.getRange().insertNode(this.node)
			}

			this.node.addEvent('load', function(ev) {

				if (this.popover) this.popover.reposition()

			}.bind(this))

			this.previousImage = this.getValidSrc()

			// We create the adjust element if it's not created yet

			if (this.popover)
			{
				this.popover.attachAnchor(this.node)
				this.popover.adjust.value = this.getValidNode()
				this.popover.show()
			}
			else
			{
				if (!this.fetchAdjustOperation)
				{
					this.fetchAdjustOperation = new Request.Widget('adjust-thumbnail/popup', this.setupPopover.bind(this))
				}

				this.fetchAdjustOperation.get({ selected: this.getValidSrc() })
			}
		},

		getValidNode: function() {

			var node = this.node

			return (!node || !node.src || node.src.test(/^data:/)) ? null : node

		},

		getValidSrc: function() {

			var node = this.getValidNode()

			return node ? node.get('src') : null
		},

		setupPopover: function(popElement)
		{
			const AdjustPopover = require('icybee/adjust-popover')
			const popover = this.popover = new AdjustPopover(popElement, {

				anchor: this.node,
				iframe: this.editor.iframe
			})

			const adjust = popover.adjust

			popover.observeAction(ev => {

				const action = ev.action
				let src = this.getValidSrc()

				if (action == 'cancel')
				{
					src = this.previousImage
					this.node.src = src || ''
				}
				else if (action == 'remove')
				{
					src = null
				}

				if (!src || src.match(/^data:/))
				{
					this.node.destroy()

					delete this.node
				}

				this.close()

			})

			adjust.value = this.getValidNode()

			/**
			 * @param {Icybee.Images.AdjustThumbnail.ChangeEvent} ev
			 */
			adjust.observeChange(ev => {

				const options = ev.options
				const node = this.node

				node.src = this.editor.baseHREF + ev.value
				node.set('data-nid', ev.nid)

				if (options.lightbox)
				{
					node.set('data-lightbox', true)
				}
				else
				{
					node.removeAttribute('data-lightbox')
				}

				if (options.width && options.method != 'surface' && options.method != 'constrained')
				{
					node.set('width', options.width)
				}
				else
				{
					node.removeAttribute('width')
				}

				if (options.height && options.method != 'surface' && options.method != 'constrained')
				{
					node.set('height', options.height)
				}
				else
				{
					node.removeAttribute('height')
				}
			})

			popover.show()
		}
	})

}) ()

MooEditable.Actions.image =
{
	title: 'Add/Edit Image',

	options:
	{
		shortcut: 'm',
		icon: 'picture'
	},

	dialogs:
	{
		prompt: function(editor)
		{
			return new MooEditable.UI.ImageDialog(editor)
		}
	},

	command: function()
	{
		this.dialogs.image.prompt.open()
	}
}
