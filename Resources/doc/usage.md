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

### With the Foxy dependency

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

> **Note:**
>
> If no PHP dependency requires Foxy inevitably (always in `require-dev` section), you must add Foxy
> in the required dependencies to the `composer.json` file of your project.

### With the Composer's extra option

However, if you want enable the Foxy for your library, but without required dependencies or dev dependencies,
you can use the extra option `extra.foxy` in your `composer.json` file:

**composer.json:**
```json
{
    "extra": {
        "foxy": true
    }
}
```

> **Note:**
>
> Like for the activation with the Foxy dependencies, you must add Foxy in the required dependencies
> to the `composer.json` file of your project.
