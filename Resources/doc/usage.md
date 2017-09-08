Usage
=====

To use Foxy, whether for a PHP library or a PHP project, you must add the Foxy dependency in
the `require` section of the Composer file.

**composer.json:**
```json
{
    "require": {
        "foxy/foxy": "^1.0.0"
    }
}
```

And create the `package.json` file to add your asset dependencies.

**package.json:**
```json
{
   "dependencies": {
       "@foo/bar": "latest"
   }
}
```

Composer will install Foxy automatically before installing the PHP dependencies, and Foxy will immediately
deal with the asset dependencies.

## Use the plugin in PHP library

In the case if you use Foxy in a PHP library, you can render Foxy optional by adding its dependency in
the `require-dev` section of the Composer file.

**composer.json:**
```json
{
    "require-dev": {
        "foxy/foxy": "^1.0.0"
    }
}
```

If no PHP dependency requires Foxy inevitably (always in `require-dev` section), you must add it
to the `composer.json` file of your project.
