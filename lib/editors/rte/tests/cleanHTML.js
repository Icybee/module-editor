var chai = require('chai')
, expect = chai.expect
, cleanPaste = require('../mooeditable/Source/MooEditable/MooEditable.CleanPaste.js')
, cleanHtml = cleanPaste.cleanHtml

describe('CleanPaste', function() {

	describe('cleanHtml', function() {

		[
			[ "should remove 'style' attribute"
			, '<strong style="some-class">A</strong>'
			, '<strong>A</strong>' ],

			[ "should preserve h3 class, but not Mso*"
			, '<h3 class="madonna MsoNormal">Madonna</h3>'
			, '<h3 class="madonna">Madonna</h3>' ]


		].forEach(function(testCase) {

			it(testCase[0], function() {

				expect(cleanHtml(testCase[1])).to.equal(testCase[2])

			})

		})

	})

})
