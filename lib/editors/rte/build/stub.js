!function (Brickrouge) {

	const instances = []

	Brickrouge.observe(Brickrouge.EVENT_UPDATE, ev => {

		ev.fragment.querySelectorAll('textarea.moo').forEach(el => {

			const uid = Brickrouge.uidOf(el)

			if (uid in instances) return

			const options = Brickrouge.Dataset.from(el)

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
