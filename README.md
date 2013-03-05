httpd-logger
============

A listener to be used with Apache to do anything with the log.

Example
=======

The could start the app like so:

```php
<?php

include 'src/autoload.php';

$app = new N7_LogParser(
	new N7_DataSource_Stdin(),
	new N7_Processor_Mongo(),
	new N7_LogFormat_Super(),
	new N7_NullLogger()
);
```

Now you can have Apache set so that it pipes output to your app. You could test it first
by trying this:

```shell
$ cat logfile.log | php myApp.php
```

And that's it. Explore the different data sources, log formats, and processors available to you
in `src/`. Keep in mind this app was written with PHP 5.2.17 compatibility, hence all the
ugly non-namespaced classes.
