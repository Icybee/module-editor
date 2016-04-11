CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
CSS_COMPRESSED = assets/editor.css
CSS_UNCOMPRESSED = assets/editor-uncompressed.css

CSS_FILES = \
	mooeditable/Assets/MooEditable/MooEditable.css \
	mooeditable/Assets/MooEditable/MooEditable.Paste.css

JS_COMPRESSOR = curl -s \
	-d compilation_level=SIMPLE_OPTIMIZATIONS \
	-d output_format=text \
	-d output_info=compiled_code \
	--data-urlencode "js_code@$^" \
	http://closure-compiler.appspot.com/compile

JS_COMPRESSED = assets/editor.js
JS_UNCOMPRESSED = assets/editor-uncompressed.js

JS_FILES = \
	mooeditable/Source/MooEditable/MooEditable.js \
	mooeditable/Source/MooEditable/MooEditable.UI.MenuList.js \
	mooeditable/Source/MooEditable/MooEditable.Extras.js \
	mooeditable/Source/MooEditable/MooEditable.Paste.js \
	mooeditable/Source/MooEditable/MooEditable.CleanPaste.js \
	mooeditable/Source/MooEditable/MooEditable.Outline.js \
	mooeditable/Source/MooEditable/MooEditable.Image.js \
	build/stub.js

all: $(CSS_UNCOMPRESSED) $(CSS_COMPRESSED) $(JS_UNCOMPRESSED) $(JS_COMPRESSED)

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	cat $^ > $@

$(CSS_COMPRESSED): $(CSS_UNCOMPRESSED)
	$(CSS_COMPRESSOR) > $@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ > $@

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) > $@

test: node_modules
	@node_modules/mocha/bin/mocha tests/*.js

node_modules:
	npm install mocha chai mootools

clean:
	@rm -f .README.md.html
	@rm -fR docs
	@rm -fR node_modules
