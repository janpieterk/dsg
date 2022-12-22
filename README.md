# Dissonance Grading

Dissonance Grading is a program by [Jos Kunst](https://joskunst.net/) (1936-1996), intended as a 
pre-compositionial tool and used as such by himself.

It scans and/or modifies chords along the dimension of dissonance.

The original documentation by Jos Kunst can be found in the file [INTRODUCTION.txt](./INTRODUCTION.txt).

The original program is a command-line tool written in Pascal (1993). The 
original source code, meant for the Symantec Think Pascal compiler for classic 
Mac OS, can be found in the directory [cli/pascal/think_pascal](./cli/pascal/think_pascal).

With some minimal changes, mainly the removal of a pseudo-terminal window 
(every modern OS has a real terminal available now) and the change of the 
construction `copy(str, 1, 1)` to `str[1]` as a way to get the first character 
of a string, the code compiles with the [Free Pascal compiler](https://www.freepascal.org/). This version of the code can be found in the 
directory [cli/pascal/fpc](./cli/pascal/fpc).

The first version of my port to PHP was created in 2006. This was a port in 
which the procedural Pascal code was ported more or less straight to procedural 
PHP with no significant structural changes. It was also a CLI program, 
identical in its UI to the Pascal program.

Some time later, also in 2006, a web interface for the program was created. 
This was and is available at [https://www.joskunst.net/dsg/](https://www.joskunst.net/dsg/).

In 2012 the procedural PHP code was seriously refactored to an object-oriented 
structure. Also a few long-standing bugs, found when comparing the outputs of 
the Pascal code and the PHP code, surfaced and were fixed. (The output of the 
Pascal code is used as the gold standard for how the program should behave.)

The PHP CLI and web interfaces both use the classes [Chord.class.php](web/includes/Chord.class.php) and 
[DSG.class.php](web/includes/DSG.class.php) and the procedural code in [set_emulation.inc.php](web/includes/set_emulation.inc.php), all located in 
[web/includes](./web/includes).

The PHP CLI program should work wherever you put the dsg directory:
```shell
$ cd ./cli/php
$ php dsg_cli.php
```
The web interface should work if you upload the contents of the web directory to your PHP-enabled web server. Some editing of [web/dsg_config.inc.php](./web/dsg_config.inc.php) may be needed. 

## MIDI support
MIDI support is provided by third-party libraries. See  [web/includes/extlib/README.md](./web/includes/extlib/README.md) and [web/js/README.md](./web/js/README.md). MIDI does not seem to function in Safari. 

## Automated tests

An [expect-lite](https://expect-lite.sourceforge.net/) script to test the CLI programs is avaiable, see the files
[./cli/pascal.elt](cli/pascal.elt), [./cli/php.elt](cli/php.elt) and [./cli/dsg_cli_test.inc](cli/dsg_cli_test.inc).

### CLI

Install expect-lite, see [here](https://expect-lite.sourceforge.net/expect-lite_install.html) for instructions.

Make sure that the environment variable `EL_REMOTE_HOST` is set to `none`:

```shekk
$ export EL_REMOTE_HOST=none`
```
#### php-cli

```shell
$ cd cli
$ expect-lite -c php.elt
```

#### Pascal
Download the [Free Pascal compiler](https://www.freepascal.org/) and compile [cli/pascal/fpc/dsgrading.p](./cli/pascal/fpc/dsgrading.p) to an executable called `dsg`. On Mac OS X 12.6.2 on an Intel Mac mini I used the following command:
```shell
$ fpc dsgrading.p -odsg
```
Make sure that the resulting binary `dsg` is in your `PATH`. Then:

```shell
$ cd cli
$ expect-lite -c pascal.elt
```

Lots of terminal output, it should conclude with

`##Overall Result: PASS`.

## PHPUnit tests

Unit tests (written for [PHPUnit](https://phpunit.de/)) for the PHP DSG class are available in
[web/test](./web/test).

```shell
$ cd web/test
$ composer install
$ vendor/bin/phpunit DSGTest.php
```
Expected output:
```shell
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

.......................................                           39 / 39 (100%)

Time: 00:04.222, Memory: 4.00 MB

OK (39 tests, 39 assertions)
```

The PHP code runs correctly under PHP 7.4. Not tested under PHP 8.


Released under the terms of version 2 of the GNU General Public License.

copyright (c) 1993 - 2022 Jos Kunst, the Jos Kunst heirs.

Contact: [Jan Pieter Kunst](https://github.com/janpieterk)
