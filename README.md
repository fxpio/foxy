Foxy
====

[![Latest Version](https://img.shields.io/packagist/v/foxy/foxy.svg)](https://packagist.org/packages/foxy/foxy)
[![Build Status](https://img.shields.io/travis/fxpio/foxy/master.svg)](https://travis-ci.org/fxpio/foxy)
[![Coverage Status](https://img.shields.io/coveralls/fxpio/foxy/master.svg)](https://coveralls.io/r/fxpio/foxy?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/fxpio/foxy.svg)](https://scrutinizer-ci.com/g/fxpio/foxy?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/01030987-5dc5-4753-92c8-70a9de80323a.svg)](https://insight.sensiolabs.com/projects/01030987-5dc5-4753-92c8-70a9de80323a)

Foxy is a Composer plugin to automate the validation, installation, updating and removing of PHP libraries
asset dependencies (javaScript, stylesheets, etc.) defined in the NPM `package.json` file of the project and
PHP libraries during the execution of Composer. It handles restoring the project state in case
[NPM](https://www.npmjs.com) or [Yarn](https://yarnpkg.com) terminates with an error. All features and tools
are available: [Npmrc](https://docs.npmjs.com/files/npmrc), [Yarnrc](https://yarnpkg.com/en/docs/yarnrc),
[Webpack](https://webpack.js.org), [Gulp](https://gulpjs.com), [Grunt](https://gruntjs.com),
[Babel](https://babeljs.io), [TypeScript](https://www.typescriptlang.org), [Scss/Sass](http://sass-lang.com),
[Less](http://lesscss.org), etc.

It is certain that each language has its own dependency management system, and that it is highly recommended to use
each package manager. NPM or Yarn works very well when the asset dependencies are managed only in the PHP project,
but when you create PHP libraries that using assets, there is no way to automatically add asset dependencies,
and most importantly, no validation of versions can be done automatically. You must tell the developers
the list of asset dependencies that using by your PHP library, and you must ask him to add manually the asset
dependencies to its asset manager of his project.

However, another solution exist - what many projects propose - you must add the assets in the folder of the
PHP library (like `/assets`, `/Resources/public`). Of course, with this method, the code is duplicated, it
pollutes the source code of the PHP library, no version management/validation is possible, and it is even
less possible, to use all tools such as Babel, Scss, Less, etc ...

Foxy focuses solely on automation of the validation, addition, updating and deleting of the dependencies in
the definition file of the asset package, while restoring the project state, as well as PHP dependencies if
NPM or Yarn terminates with an error.

#### It is Fast

Foxy retrieves the list of all Composer dependencies to inject the asset dependencies in the file `package.json`,
and leaves the execution of the analysis, validation and downloading of the libraries to NPM or Yarn. Therefore,
no VCS Repository of Composer is used for analyzing the asset dependencies, and you keep the performance
of native package manager used.

#### It is Reliable

Foxy creates mock packages of the PHP libraries containing only the asset dependencies definition file
in a local directory, and associates these packages in the asset dependencies definition file of the
project. Given that Foxy does not manipulate any asset dependencies, and let alone the version constraints,
this allows NPM or Yarn to solve the asset dependencies without any intermediary. Moreover, the entire
validation with the lock file and installation process is left to NPM or Yarn.

#### It is Secure

Foxy restores the Composer lock file with all its PHP dependencies, as well as the asset dependencies
definition file, in the previous state if NPM or Yarn ends with an error.

Features
--------

- Compatible with [Symfony Webpack Encore](http://symfony.com/doc/current/frontend.html)
  and [Laravel Mix](https://laravel.com/docs/master/mix)
- Works with Node.js and NPM or Yarn
- Works with the asset dependencies defined in the `package.json` file for projects and PHP libraries
- Works with the installation in the dependencies of the project or libraries (not in global mode)
- Works with public or private repositories
- Works with all features of Composer, NPM and Yarn
- Retains the native performance of Composer, NPM and Yarn
- Restores previous versions of PHP dependencies and the lock file if NPM or Yarn terminates with an error
- Validates the NPM or Yarn version with a version range
- Configuration of the plugin per project, globally or with the environment variables:
  - Enable/disable the plugin
  - Choose the asset manager: NPM or Yarn (`npm` is used by default)
  - Lock the version of the asset manager with the Composer version range
  - Define the custom path of binary of the asset manager
  - Enable/disable the fallback for the asset package file of the project
  - Enable/disable the fallback for the Composer lock file and its dependencies
  - Enable/disable the running of asset manager to keep only the manipulation of the asset package file
  - Override the install command options for the asset manager
  - Override the update command options for the asset manager
  - Define the custom path of the mock package of PHP library
  - Enable/disable manually the asset packages for the PHP libraries
- Works with the Composer commands:
  - `install`
  - `update`
  - `require`
  - `remove`

Documentation
-------------

- [Guide](Resources/doc/index.md)
- [FAQs](Resources/doc/faqs.md)
- [Release Notes](https://github.com/fxpio/foxy/releases)

Installation
------------

Installation instructions are located in [the guide](Resources/doc/index.md).

License
-------

Foxy is released under the MIT license. See the complete license in:

[LICENSE](LICENSE)

About
-----

Foxy is a [François Pluchino](https://github.com/francoispluchino) initiative.
See also the list of [contributors](https://github.com/fxpio/foxy/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/fxpio/foxy/issues).

Acknowledgments
---------------

Thanks to [Tobias Munk](https://github.com/schmunk42) to have suggesting this name
