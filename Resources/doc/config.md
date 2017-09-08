Configuration
=============

## Manipulate the configuration

### Define the config for one project

All options can be added in the `composer.json` file of the project in the `config.foxy.*` section.

**Example:**

```json
{
    "name": "root/package",
    "config": {
        "foxy": {
            "enabled": false
        }
    }
}
```

### Define the config for all projects

You can define the options in the `composer.json` file of each project, but you can also set an option
for all projects.

To do this, you simply need to add your options in the Composer global configuration,
in the file of your choice:

- `<COMPOSER_HOME>/composer.json` file
- `<COMPOSER_HOME>/config.json` file

> **Note:**
> The `composer global config` command cannot be used, bacause Composer does not accept custom options.
> But you can use the command `composer global config -e` to edit the global `composer.json` file with
> your text editor.

### Define the config in a environment variable

You can define each option (`config.foxy.*`) directly in the PHP environment variables. For
this, all variables will start with `FOXY__` and uppercased, and each `-` will replaced by `_`.

The accepted value types are:

- string
- boolean
- integer
- JSON array or object

**Example:**
```json
{
    "config": {
        "foxy": {
            "enabled": false
        }
    }
}
```

Can be overridden by `FOXY__ENABLED="false"` environment variable.

### Config priority order

The config values are retrieved in priority in:

1. the environment variables starting with `FOXY__`
2. the project `composer.json` file
3. the global `<COMPOSER_HOME>/config.json` file
4. the global `<COMPOSER_HOME>/composer.json` file
