!function (Brickrouge) {

	const Editor = new Class({

		Implements: [ Options ],

		options:
		{
			contentsName: 'contents',
			selectorName: 'editor'
		},

		initialize: function(el, options)
		{
			this.element = el
			this.setOptions(options)
			this.setOptions(this.element.get('dataset'))

			const selector = this.element.getFirst('.editor-options').getElement('select')

			if (selector)
			{
				selector.addEvent('change', (ev) => {

					this.change(ev.target.get('value'))

				})
			}

			this.form = this.element.getParent('form')
		},

		change: function(editor)
		{
			this.element.set('tween', { property: 'opacity', duration: 'short', link: 'cancel' })
			this.element.get('tween').start(.5)

			const op = new Request.Element ({

				url: '/api/editor/' + editor + '/change',
				onSuccess: this.handleResponse.bind(this)

			})

			const key = this.form[ICanBoogie.Operation.KEY]
			const constructor = this.form[ICanBoogie.Operation.DESTINATION].value
			const textarea = this.element.getElement('textarea');

			op.get ({

				contents_name: this.options.contentsName,
				selector_name: this.options.selectorName,

				contents: textarea ? textarea.value : '',

				nid: key ? key.value : null,
				constructor: constructor

			})
		},

		handleResponse: function(el)
		{
			el.inject(this.element, 'after')

			this.element.destroy()

			this.initialize(el)

			document.fireEvent('editors')
		}
	});

	const instances = []

	Brickrouge.observeUpdate(() => {

		Array.prototype.forEach.call(document.body.querySelectorAll('div.editor-wrapper'), (el) => {

			const uid = Brickrouge.uidOf(el)

			if (uid in instances) return

			instances[uid] = new Editor(el)
		})
	})

} (Brickrouge);
