Foxy
====

Foxy allows you to validate and manage the assets (javascript, stylesheet, etc.) of PHP libraries
during the installation of Composer dependencies with all permitted features
by [NPM](https://www.npmjs.com) or [Yarn](https://yarnpkg.com) with the `package.json` file
in your PHP project and PHP libraries.

#### Fast

Foxy retrieves the list of all Composer dependencies to inject the asset dependencies in the file `package.json`,
and leaves the execution of the analysis, validation and downloading of the libraries to NPM or Yarn. Therefore,
no VCS Repository of Composer is used for analyzing the asset dependencies, and you keep the performances
of each package manager.

#### Reliable

Foxy uses the lock file of Composer, as well as NPM or Yarn lock files.

#### Secure

Foxy restores the Composer lock file as well as any PHP dependencies if NPM or Yarn ends with an error.

Features
--------

- Works with Nodejs and NPM or Yarn
- Works with public or private repositories
- Works with all features of Composer, NPM and Yarn
- Retains the native performances of Composer, NPM and Yarn
- Restores previous versions of PHP dependencies and the lock file if NPM or Yarn terminates with an error
- Validates the NPM or Yarn version with a version range
- Configuration of the plugin per project, globally or with the environment variables:
  - Enable/disable the plugin
  - Choose the asset manager: NPM or Yarn (`npm` by default)
  - Lock the version of the asset manager with the Composer version range
  - Enable/disable the fallback for the asset package file of the project
  - Enable/disable the fallback for the Composer lock file and its dependencies
  - Enable/disable the running of asset manager to keep only the manipulation of the asset package file
  - Override the install command options for the asset manager
  - Override the update command options for the asset manager
- Works with the Composer commands:
  - `install`
  - `update`
  - `require`
  - `remove`

Why this plugin?
----------------

It is certain that each language has its own dependency management system, and that it is highly recommended to use
each package manager. NPM or Yarn works very well when the asset dependencies are managed only in the project,
but when you create PHP libraries using assets, there is no way to automatically add asset dependencies,
and most importantly, no verification of the versions can be done automatically. You must tell the developers
that the library uses this or that asset, and you must ask him to add manually the asset dependencies to its
asset manager of his project.

Foxy automates the addition, update and deletion of the asset dependencies in NPM or Yarn, while restoring
the project state, as well as PHP dependencies if NPM or Yarn terminates with an error.

Documentation
-------------

The bulk of the documentation is located in `Resources/doc/index.md`:

[Read the Documentation](Resources/doc/index.md)

[Read the Release Notes](https://github.com/foxypkg/foxy/releases)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This composer plugin is under the MIT license. See the complete license in:

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
