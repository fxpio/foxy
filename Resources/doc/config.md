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

## Use the config options

### Enable/disable the plugin

You can enable or disable the plugin with the option `config.foxy.enabled` [`boolean`, default: `true`].

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

### Choose the asset manager

You can choose the asset manager with the option `config.foxy.manager` [`string`, default: `npm`].

**Available values:**

- `npm`
- `yarn`

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager": "yarn"
        }
    }
}
```

### Lock the version of the asset manager with the Composer version range

You can validate the version of the asset manager with the option
`config.foxy.manager-version` [`string`, default: `null`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager-version": "^5.3.0"
        }
    }
}
```

### Define the custom path of binary of the asset manager

You can define the custom path of the binary of the asset manager with the option
`config.foxy.manager-bin` [`string`, default: `null`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager-bin": "/custom/path/of/asset/manager/binary"
        }
    }
}
```

### Override the install command options for the asset manager

You can add custom options for the asset manager binary for the install command with the
option `config.foxy.manager-install-options` [`string`, default: `null`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager": "npm",
            "manager-install-options": "--dry-run"
        }
    }
}
```

> **Note:**
>
> For this example, the option allow you to keep only the manipulation of the asset package file,
> and validate the dependencies without the installation of the dependencies

### Override the update command options for the asset manager

You can add custom options for the asset manager binary for the update command with the
option `config.foxy.manager-update-options` [`string`, default: `null`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager": "yarn",
            "manager-update-options": "--flat"
        }
    }
}
```

### Define the execution timeout of the asset manager

You can define the execution timeout of the asset manager with the
option `config.foxy.manager-timeout` [`int`, default: `null`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager-timeout": 420
        }
    }
}
```

### Enable/disable the fallback for the asset package file of the project

You can enable or disable the fallback of the asset package file with the option
`config.foxy.fallback-asset` [`boolean`, default: `true`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "fallback-asset": false
        }
    }
}
```

### Enable/disable the fallback for the Composer lock file and its dependencies

You can enable or disable the fallback of the Composer lock file and its dependencies with the option
`config.foxy.fallback-composer` [`boolean`, default: `true`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "fallback-composer": false
        }
    }
}
```

### Enable/disable the running of asset manager

You can enable or disable the running of the asset manager with the option
`config.foxy.run-asset-manager` [`boolean`, default: `true`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "run-asset-manager": false
        }
    }
}
```

> **Note:**
>
> This option allow you to keep only the manipulation of the asset package file,
> without the execution of the asset manager

### Define the custom path of the mock package of PHP library

You can define the custom path of the mock package of PHP library with the option
`config.foxy.composer-asset-dir` [`string`, default: `null`].

**Example:**
```json
{
    "config": {
        "foxy": {
            "manager-bin": "./my/mock/asset/path/of/project"
        }
    }
}
```
