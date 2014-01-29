# Remote Select Box #

Version: 1.0

This extension provides a simple combo box field that uses values from a remote source, via ajax.

### REQUIREMENTS ###

- Symphony CMS version 2.3.3 and up (as of the day of the last release of this extension)

### INSTALLATION ###

- `git clone` / download and unpack the tarball file
- Put into the extension directory
- Enable/install just like any other extension

See <http://getsymphony.com/learn/tasks/view/install-an-extension/>

*Voila !*

Come say hi! -> <http://www.deuxhuithuit.com/>

### HOW TO USE ###

- Add a Remote Select Box field to your section.
- Set the `data url` options with a valid url
	- This url must return an array of JSON objects
	- Those object must have a `value` and a `text` member, i.e. 
````js
[{"value":"the saved value","text":"the text the user sees"}, {...}]
````
- You can use XMLHttpRequest friendly external sources or even a frontend page as your data source.

### KNOWN LIMITATIONS ###

- When updating an entry, the value will be deleted if it can't find it in the datasource or if the datasource is broken.
- Only the value is saved, not the text.
- Only the value is displayed in the table view

If you feel that this is wrong, do no hesitate to fork an improve it!