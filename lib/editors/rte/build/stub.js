!function (Brickrouge) {

	var instances = []

	Brickrouge.observe('update', function() {

		Array.prototype.forEach.call(document.body.querySelectorAll('textarea.moo'), function(el) {

			var uid = Brickrouge.uidOf(el)

			if (uid in instances) return

			var options = Brickrouge.Dataset.from(el)

			if (options.externalCss)
			{
				options.externalCSS = JSON.decode(options.externalCss)
			}

			if (options.baseUrl)
			{
				options.baseURL = options.baseUrl
			}

			instances[uid] = el.mooEditable(options)
		})
	})

} (Brickrouge);
