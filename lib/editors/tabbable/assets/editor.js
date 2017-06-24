!function (Brickrouge) {

	Brickrouge.Tabbable = new Class({

		/**
		 * Initialize the `nav` and `content` properties with the `.nav-tabs` and `.tab-content`
		 * descendants.
		 *
		 * @param {Element} el A `.tabbable` element.
		 * @param {object} options
		 */
		initialize: function(el, options)
		{
			this.element = el

			this.nav = el.querySelector('.nav-tabs')
			this.content = el.querySelector('.tab-content')

			this.element.addEvent('keydown', this.onKeyDown.bind(this))

			this.onChange()
		},

		/**
		 * Returns the tab at the specified position.
		 *
		 * @param {number|Element} i The position can be specified as an index offset, a tab element or
		 * a pane element.
		 *
		 * @return {Element}
		 */
		resolveTab: function(i)
		{
			if (typeOf(i) === 'number')
			{
				return this.nav.getChildren()[i]
			}

			if (this.nav.contains(i))
			{
				return i
			}

			i = this.content.getChildren().indexOf(i)

			if (i === -1)
			{
				return null
			}

			return this.nav.getChildren()[i]
		},

		/**
		 * Returns the index position of the specified tab.
		 *
		 * @param tab[optional] If the tab is not specified the active tab is as instead.
		 *
		 * @return number
		 */
		getPosition: function(tab)
		{
			if (!tab)
			{
				const link = this.nav.querySelector('.nav-link.active')

				if (link)
				{
					tab = link.closest('.nav-item')
				}
			}

			return this.nav.getChildren().indexOf(tab)
		},

		onChange: function()
		{
			this.element[this.content.getChildren().length === 1 ? 'addClass' : 'removeClass']('lonely-tab')
		},

		onKeyDown: function(ev)
		{
			if (ev.target.tagName.match(/^INPUT|SELECT|TEXTAREA$/)) return

			switch (ev.key)
			{
				case 'left':
					ev.stop()
					let i = this.getPosition()
					this.activateTab(i ? i - 1 : this.content.getChildren().length - 1)
					break
				case 'right':
					ev.stop()
					let i = this.getPosition()
					this.activateTab(i === this.content.getChildren().length - 1 ? 0 : i + 1)
					break
			}
		},

		/**
		 * Activates a tab.
		 *
		 * @param {number|Element} i The tab to activate. If an element is provided it can either be a
		 * tab or a pane.
		 */
		activateTab: function(i)
		{
			const nav = this.nav
			const content = this.content
			const tabs = this.nav.getChildren()
			const panes = this.content.getChildren()
			const links = this.nav.querySelectorAll('.nav-link')

			if (typeOf(i) === 'element')
			{
				const el = i

				i = tabs.indexOf(el)

				if (i === -1)
				{
					i = panes.indexOf(el)
				}

				if (i === -1)
				{
					throw new Error('The element provided is not a tab nor a pane.', el)
				}
			}

			if (i < 0 || i > panes.length)
			{
				throw new Error('Position is out of range.', i)
			}

			console.log('activate tab:', i, links[i])

			links.forEach(function (link, k) {

				console.log(k, i, link)

				link.classList[ k === i ? 'add' : 'remove']('active')

			})

			const activePane = content.querySelector('.active')

			if (activePane)
			{
				activePane.removeClass('active')
			}

			console.log(tabs, panes)

			// tabs[i].querySelector('.nav-link').addClass('active')
			// panes[i].addClass('active')
		},

		/**
		 * Removes a tab.
		 *
		 * @param {number|Element} i The tab to remove.
		 */
		removeTab: function(i)
		{
			const tabs = this.nav.getChildren()
			const panes = this.content.getChildren()
			const tab = this.resolveTab(i)

			if (tabs.length === 2)
			{
				alert('The last tab cannot be removed.')

				return
			}

			i = tabs.indexOf(tab)

			this.activateTab(i ? i - 1 : 1)

			tabs[i].destroy()
			panes[i].destroy()

			this.onChange()
		}
	})

	/**
	 *
	 */
	const TabbableEditor = new Class({

		Extends: Brickrouge.Tabbable,

		Implements: [ Options ],

		options: {

			controlName: null

		},

		initialize: function(el, options)
		{
			this.parent($(el).getFirst('.tabbable'))
			this.setOptions(options)
			this.addTabTrigger = this.nav.getElement('a[data-create="tab"]')
			this.addTabTriggerContainer = this.addTabTrigger.getParent('li')
			this.controlAnchorMap = {}
			this.attachedControls = {}

			this.addTabTrigger.addEvent('click', function(ev) {

				ev.stop()

				this.addTab()

			}.bind(this))

			this.nav.addEvent('click:relay([data-removes="tab"])', function(ev, el) {

				ev.stop()

				this.removeTab(el.getParent('li'))

			}.bind(this))

			this.attach()
		},

		updateOrders: function()
		{
			this.tabsOrder = this.nav.getChildren()
			this.tabsOrder.pop() // remove addTabTrigger

			this.panesOrder = this.content.getChildren()
		},

		attach: function()
		{
			const sortable = new Sortables(this.nav, {

				handle: 'a',
				unDraggableTags: [],
				onComplete: () =>
				{
					this.addTabTriggerContainer.inject(this.nav, 'bottom')

					const tabs = this.nav.getChildren()
					const order = []
					let changes = 0

					tabs.each((el, y) => {

						const i = this.tabsOrder.indexOf(el)

						if (i === -1) return
						if (i !== y) changes++

						order.push(this.panesOrder[i])

					})

					if (changes)
					{
						this.content.adopt(order)
						this.updateOrders()
					}
				}
			})

			this.updateOrders();

			sortable.removeItems(this.addTabTriggerContainer)

			//
			//
			//

			this.content.getElements('.tab-pane [data-provides="title"]').each(function(control) {

				const container = control.getParent('.tab-content')
				const uniqueNumber = control.uniqueNumber

				if (container !== this.content || this.attachedControls[uniqueNumber] !== undefined) return

				this.attachedControls[uniqueNumber] = control

				control.addEvents({

					change: this.onTitleChange.bind(this),
					keyup: this.onTitleChange.bind(this)

				})

			}, this)
		},

		onTitleChange: function(ev)
		{
			const control = ev.target
			const value = control.get('value')
			const anchor = this.getTitleReciever(control)

			anchor.set('text', value ? value : '?')
		},

		/**
		 * Returns the element that should be updated when the title of the tab is modified.
		 *
		 * @param control The control used to edit the title of the tab.
		 *
		 * @returns Element
		 */
		getTitleReciever: function(control)
		{
			const uniqueNumber = control.uniqueNumber

			let receiver = this.controlAnchorMap[uniqueNumber]

			if (receiver)
			{
				return receiver
			}

			const tabPane = control.getParent('.tab-pane')
			const index = this.content.getElements('.tab-pane').indexOf(tabPane)
			const receivers = this.nav.getElements('[data-receives="title"]')

			receiver = receivers[index]

			this.controlAnchorMap[uniqueNumber] = receiver

			return receiver
		},

		addTab: function()
		{
			var tempPane = new Element('div.tab-pane', { html: "<em>Loading pane editor…</em>" })
			, anchor = new Element('a.nav-link[tabindex="-1"][data-toggle="tab"]', {
				html: '<span data-receives="text">?</span><span class="close" data-removes="tab">&times;</span>',
				href: '#'
			})
			, tempTab = new Element('li.nav-item').adopt(anchor)
			, i = this.getPosition()

			try
			{
				new Request.Element({

					url: 'editors/tabbable/new-pane',
					onSuccess: function(pane, response)
					{
						if (response.tab)
						{
							var tab = Elements.from(response.tab).shift()

							tab.replaces(tempTab)
						}
						else
						{
							anchor.set('text', pane.querySelector('[data-provides="title"]').value)
						}

						pane.replaces(tempPane)

						this.attach()

					}.bind(this)

				}).get({ control_name: this.options.controlName })
			}
			catch (e)
			{
				alert('Unable to load pane.')

				console.error(e)
			}

			tempTab.inject(this.nav.getChildren()[i], 'after')
			tempPane.inject(this.content.getChildren()[i], 'after')

			this.activateTab(tempTab)
			this.onChange()
		}
	})

	Brickrouge.register('TabbableEditor', (element, options) => new TabbableEditor(element, options))

} (Brickrouge);
