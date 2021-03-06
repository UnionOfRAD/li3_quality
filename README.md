# Quality
**This li₃ plugin adds code quality assurance to your toolbelt.**

## Key Features

- Detect coding-standard violations.
- Find weak- or untested classes/methods.
- No external dependencies.
- Color-Highlighting.
- Integrates into the Lithium test dashboard.
- Runs on Windows without hassle.
- Cool shortcuts to ease your workflow.

## Installation

The preferred installation method is via composer. You can add
the library as a dependency via:

```
composer require unionofrad/li3_quality
```

li₃ libraries must be registered within your application bootstrap phase 
as they use a different (faster) autoloader. 

```php
Libraries::add('li3_quality')
```

If you open the test dashboard (under `/test` in your browser), you should 
have an additional `Syntax` button to check the files directly in your browser.

## Usage: The "syntax" command

If you just run it with `li3 syntax`, it will run all rules against your `app` library.

```bash
$ li3 syntax --verbose path/to/code
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

## Usage: The "fix" command

The fix command helps in migrating code to newer versions. It'll rewrite 
existing source code files and update them in place. Please note that
you should use a version control system when using this command, as 
the results may not always be 100% what you'd expect.

```
$ li3 fix --verbose path/to/code
```

## Custom Rules & Rule Sets

Rule based commands (syntax and fix) will work only with a rule set configuration file.
If none is provided through the `--config=<FILE>` option, these commands fall back 
to use the default configuration files in `config/syntax.json` and `config/fix.json` 
respectively.  

Custom rules must be create as i.e. 
`<your library>/extensions/qa/rules/{syntax,fix}/YourCustomRule.php`.

## GIT Pre Commit Hook

This pre commit hook is based upon the example found in `.git/hooks/pre-commit.sample`. Copy
the sample script to `/path/to/project/.git/hooks/pre-commit` and make it executable. Then,
replace the code in the script with the code shown below and adjust the paths to the plugin and
the li3 command.

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
    cd ${APP} && ${LI3} syntax ${PROJECT}/${FILE}
    test $? != 0 && EXIT_STATUS=1
done

exit ${EXIT_STATUS}
```

Now when committing each file the syntax is checked. The commit is aborted if a check failed. If you don't want to have the hook run on commit pass the `--no-verify` option to git commit.

## Usage: The "coverage" command

With `li3 coverage` you can get a summary of how well your classes are covered with tests. This makes use of some `xdebug` functions, so make sure to have it installed.

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


## Copyright & License

Copyright 2011 Union of RAD. All rights reserved. This library
is distributed under the terms of the BSD 3-Clause License. The
full license text can be found in the LICENSE.txt file.
