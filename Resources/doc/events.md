Events
======

Foxy triggers events with the event dispatcher system of Composer allowing you to extend Foxy's
capabilities. To do this, you must create a Composer plugin requiring Foxy in addition to the
requirements required for the creation of a Composer plugin. You can read the documentation of
Composer: [Setting up and using plugin](https://getcomposer.org/doc/articles/plugins.md).

## Event names

All event names are listed in constants of the `Foxy\FoxyEvents` class containing the name of
the event class for each event.

### pre-solve

The `foxy.pre-solve` event occurs before the `solve` action of asset packages and after the
Composer's command events `post-install-cmd` and `post-update-cmd`.

### get-assets

The `foxy.get-assets` event occurs before the `solve` action of asset packages and during the
retrieves the map of the asset packages. It is in this event that you can add new asset packages
in the map.

### post-solve

The `foxy.post-solve` event occurs after the `solve` action of asset packages and before the
execution of the Composer's fallback.
