li3_quality: Enforcing quality in your Lithium applications since 2011
======================================================================
This project is intended to be a pure-lithium based replacement for the [li3_qa](https://github.com/UnionOfRAD/li3_qa) library, which depends on [phpca](https://github.com/UnionOfRAD/phpca). It runs on windows by default, has color-highlighting enabled and integrates into the web-based test dashboard.

Installation
------------
Clone the repository in your libraries path and then add this line to the `config/bootstrap/libraries.php` file:

```php
	/**
	 * Add some plugins:
	 */
	Libraries::add('li3_quality');
```

Usage
-----
In your application directory, you now have the commands available on the console:

```bash
	$ li3 quality
	Lithium console started in the development environment. Use the --env=environmet key to alter this.
	USAGE
		li3 quality syntax
	DESCRIPTION
		The Quality command helps you to run static code analysis on your codebase.
	OPTIONS
		syntax
			Checks the syntax of your class files through static code analysis.
		--namespace=<>
			The namespace to run the quality checks on.
		--silent=<>
			If `--silent` is used, only failures are shown.
```

If you don't supply any further parameter, it checks the syntax of your `app` directory against the Lithium ruleset:

```bash
	$ li3 quality syntax
	--------------------
	Lithium Syntax Check
	--------------------
	Performing 16 rules on 6 classes.
	[FAIL] app\models\Authors
	Line    Position        Violation
	----    --------        ---------
	7       1               Trailing whitespace found
	[OK] app\models\Groups
	[OK] app\models\Posts
	[FAIL] app\controllers\HelloWorldController
	Line    Position        Violation
	----    --------        ---------
	17      -               Function "to_string" is not in camelBack style
	21      -               Function "to_json" is not in camelBack style
	24      1               Trailing whitespace found
	[FAIL] app\controllers\PagesController
	Line    Position        Violation
	----    --------        ---------
	32      1               Trailing whitespace found
	34      1               Trailing whitespace found
	28      -               Protected Method "view" does not start with "_"
	33      -               Protected Method "foobar" does not start with "_"
```

If you have lots of fils to check (for example if you test against the lithium core), you can pass the `--silent` option to only show errors:

```bash
	$ li3 quality syntax --silent --namespace=lithium
	--------------------
	Lithium Syntax Check
	--------------------
	Performing 16 rules on 375 classes.
	[FAIL] lithium\tests\cases\net\http\RouteTest
	Line    Position        Violation
	----    --------        ---------
	383     103             Maximum line length exceeded
	[FAIL] lithium\tests\cases\console\command\LibraryTest
	Line    Position        Violation
	----    --------        ---------
	241     101             Maximum line length exceeded
	[FAIL] lithium\test\Unit
	Line    Position        Violation
	----    --------        ---------
	1017    102             Maximum line length exceeded
	[FAIL] lithium\data\Entity
	Line    Position        Violation
	----    --------        ---------
	379     111             Maximum line length exceeded
	381     110             Maximum line length exceeded
```

If you open the test dashboard (under `/test` in your browser), you can should have an additional `Syntax` button to check the files directly in your browser.

Planned
-------
This library only implements a subset of all rules defined by the coding standard. You can find a list of implemented rules [here](). I'm planning to implement this list as complete as possible, so any help would be greatly appreciated! I also plan to implement functionality for covered classes, documentation blocks and so on. Composer support will follow!