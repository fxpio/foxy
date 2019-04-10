Getting started
===============

1. [Introduction](index.md#introduction)
2. [Required dependencies](index.md#required-dependencies)
3. [Installation](index.md#installation)
4. [Usage](usage.md)
5. [Configuration](config.md)
6. [Event](events.md)
7. [FAQs](faqs.md)

## Introduction

Foxy is a Composer plug-in that aggregates npm-packages from Composer packages.

This makes it possible (and automates the process of) installing and updating npm-packages that ship with your Composer packages, leveraging the native (`npm` or `yarn`) package manager to do the heavy lifting.

For this approach to work well, you should think of an npm-package in a Composer package not just as an "artifact", but as an actual npm-package *embedded* in your Composer package.

Importantly, you should name it and *version* it, independently of your Composer version number - like you would normally do with a stand-alone npm-package.

Note that, for npm-packages with no version number, Foxy will default to the Composer version, as a fallback only: versioning your npm-package explicitly is much safer in terms of correctly versioning breaking/non-breaking changes to any client-side APIs exposed by the embedded npm-package.

## Required dependencies

- [Nodejs](https://nodejs.org)
- [NPM](https://www.npmjs.com) or [Yarn](https://yarnpkg.com)
- [Git](https://git-scm.com)

## Installation

See the [Release Notes](https://github.com/fxpio/foxy/releases)
to know the Composer version required.

```shell
composer require "foxy/foxy:^1.0.0"
```

Composer will install the plugin to your project's `vendor/foxy` directory.

## Next step

You can read how to:

- [Use this plugin](usage.md)
- [Configure this plugin](config.md)
- [Expand Foxy with Composer events](events.md)
