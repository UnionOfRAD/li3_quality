Improve the code quality of your Lithium applications
=====================================================
This project is a 100% Lithium based replacement for the [li3_qa](https://github.com/UnionOfRAD/li3_qa) library, which in turn depends on [phpca](https://github.com/UnionOfRAD/phpca).

Here are some of the key features:

- Detect coding-standard violations.
- Find weak- or untested classes/methods.
- No external dependencies.
- Color-Highlighting.
- Integrates into the Lithium test dashboard.
- Runs on Windows without hassle.
- Cool shortcuts to ease your workflow.

Installation
------------
Clone the repository in your libraries path and then add this line to the `config/bootstrap/libraries.php` file:

```php
/**
 * Add some plugins:
 */
Libraries::add('li3_quality');
```
If you open the test dashboard (under `/test` in your browser), you can should have an additional `Syntax` button to check the files directly in your browser.

Usage
-----
If you run the `li3` command from your console, you'll now find the `syntax` command with its various params:

```bash
$ li3 quality
Lithium console started in the development environment. Use the --env=environment key to alter this.
USAGE
    li3 quality syntax
    li3 quality documented
    li3 quality coverage
DESCRIPTION
    The Quality command helps you to run static code analysis on your codebase.
OPTIONS
    syntax
        Checks the syntax of your class files through static code analysis.
    documented
        Checks for undocumented classes or methods inside the library.
    coverage
        Lists code coverage for a given threshold (100 by default).
    --library=<>
        The library to run the quality checks on.
    --silent=<>
        If `--silent` is used, only failures are shown.
    --threshold=<>
        If `--slient NUM` is used, only classes below this coverage are shown.
```

The "syntax" command
--------------------
If you just run it with `li3 quality syntax`, it will run all rules against your `app` library.

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

If you have lots of fils to check (for example if you test against the lithium core), you can pass the `--silent` option to only show errors. The `--library` param allows you to run the checks against a different library:

```bash
$ li3 quality syntax --silent --library=lithium
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

GIT Pre Commit Hook
--------------------

This pre commit hook is based upon the example found in `.git/hooks/pre-commit.sample`. Copy the sample script to `/path/to/project/.git/hooks/pre-commit` and make it executable. Then, replace the code in the script with the code shown below and adjust the paths to Lithium QA and the li3 command.

```
cd /path/to/project
cp .git/hooks/pre-commit.sample .git/hooks/pre-commit
chmod a+x .git/hooks/pre-commit
```

Now add the following code to .git/hooks/pre-commit and adjust the `APP` and `LI3` values.

```bash
#!/bin/sh

APP=/path/to/li3_quality_enabled/app/
LI3=/path/to/lithium/libraries/lithium/console/li3

if git-rev-parse --verify HEAD >/dev/null 2>&1
then
    AGAINST=HEAD
else
    # Initial commit: diff against an empty tree object
    AGAINST=4b825dc642cb6eb9a060e54bf8d69288fbee4904
fi

EXIT_STATUS=0
PROJECT=`pwd`

for FILE in `git diff-index --cached --name-only --diff-filter=AM ${AGAINST}`
do
    cd ${APP} && ${LI3} quality syntax ${PROJECT}/${FILE}
    test $? != 0 && EXIT_STATUS=1
done

exit ${EXIT_STATUS}
```

Now when committing each file the syntax is checked. The commit is aborted if a check failed. If you don't want to have the hook run on commit pass the `--no-verify` option to git commit.

The "coverage" command
----------------------
With `li3 quality coverage` you can get a summary of how well your classes are covered with tests. This makes use of some `xdebug` functions, so make sure to have it installed.

```bash
$ li3 quality coverage
---------------------
Lithium Code Coverage
---------------------
Checking coverage on 6 classes.
   no test |     n/a | app\models\Authors
   no test |     n/a | app\models\Groups
   no test |     n/a | app\models\Posts
   no test |     n/a | app\controllers\HelloWorldController
   no test |     n/a | app\controllers\PagesController
   no test |     n/a | app\controllers\PostsController
```

You can also reuse the `--library` argument as well. In addition, this command provides an optional `--threshold` argument that only displays coverage below the given amount. This defaults to 100, so all classes will be shown. If you have coloring on your shell (likely not on windows), then the classes are colored to reflect the coverage policy of the Lithium framework (0% or no test is red, 85% or higher is green and the rest is yellow).

The "documented" command
------------------------
This command needs to be implemented.

Planned
-------
See the issue tracker for all tickets that are currently marked as "enhancement".