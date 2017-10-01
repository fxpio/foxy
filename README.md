Foxy
====

[![Latest Version](https://img.shields.io/packagist/v/foxy/foxy.svg)](https://packagist.org/packages/foxy/foxy)
[![Build Status](https://img.shields.io/travis/foxypkg/foxy/master.svg)](https://travis-ci.org/foxypkg/foxy)
[![Coverage Status](https://img.shields.io/coveralls/foxypkg/foxy/master.svg)](https://coveralls.io/r/foxypkg/foxy?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/foxypkg/foxy.svg)](https://scrutinizer-ci.com/g/foxypkg/foxy?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/01030987-5dc5-4753-92c8-70a9de80323a.svg)](https://insight.sensiolabs.com/projects/01030987-5dc5-4753-92c8-70a9de80323a)

Foxy automates installation, updates and removal of PHP libraries asset dependencies (JavaScript,
stylesheets, etc.) defined in the project's `package.json` file during the execution of Composer.
It handles restoring the project state in case [NPM](https://www.npmjs.com) or
[Yarn](https://yarnpkg.com) terminates with an error. All features and tools are available:
*.rc* files, [Webpack](https://webpack.js.org), [Gulp](https://gulpjs.com), [Grunt](https://gruntjs.com),
[Babel](https://babeljs.io), [TypeScript](https://www.typescriptlang.org), [Sass](http://sass-lang.com),
[Less](http://lesscss.org), etc.

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
- [Release Notes](https://github.com/foxypkg/foxy/releases)

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
See also the list of [contributors](https://github.com/foxypkg/foxy/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/foxypkg/foxy/issues).

Acknowledgments
---------------

Thanks to [Tobias Munk](https://github.com/schmunk42) to have suggesting this name
