FAQs
====

What version required of Composer?
----------------------------------

See the documentation: [Installation](index.md#installation).

How does the plugin work?
-------------------------

Foxy work in this order:

1. Validation of the asset manager installation, then checking of the compatible asset manager version (optional)
2. Saving  the status of project
3. Installing/updating of the PHP dependencies by Composer
4. Retrieving the entire list of installed packages
5. Retains only PHP dependencies with the `foxy/foxy` dependency in the `require` or `require-dev` section of the `composer.json` file
6. Checking the lock file of asset manager
7. Comparing the difference between the installed asset dependencies and the new asset dependencies, to determine whether the dependency must be installed, updated, or removed
8. Creating, updating, or deleting of the mock asset libraries in local, containing only the `package.json` file of the PHP library, with a formatted name as: `@composer-asset/<php-package-vendor>--<php-package-name>`
9. Adding, updating, or deleting the mock asset library in the `package.json` file of the project
10. Running the install or update command of asset manager
11. Restoring the `package.json` file with the previous dependencies if the asset manager terminates with an error
12. Restoring the `composer.lock` file and all PHP dependencies if the asset manager terminates with an error

How to increase the PHP memory limit?
-------------------------------------

See the official documentation of Composer: [Memory limits errors](https://getcomposer.org/doc/articles/troubleshooting.md#memory-limit-errors).
