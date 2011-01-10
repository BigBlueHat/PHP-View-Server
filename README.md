# PHP View Server for CouchDB

Back in 2007, Jan Lehnardt started the process of building a [PHP-based View Server for CouchDB](http://jan.prima.de/~jan/plok/archives/93-CouchDb-Views-with-PHP.html). This is a continuation and update of that view server to work with CouchDB 0.11 and 1.x. So far, it's only been tested on 1.0.1, but you're welcome to test it elsewhere--I'd love to know how it goes.

So far:

* map functions work (a bit differently than JS...see below)

Known Issues:

* **only** map functions work
* map function must return an array of key/value pairs in array($key, $value) format--unlike JS which has an emit() accumulator function

## License
Apache License 2.0
