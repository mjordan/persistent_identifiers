# N2T (Names To Things) ARK Minter

## Introduction

Drupal module that mints ARKs from the Names To Things service.

## Requirements

* Drupal 8 or 9
* [Persistent Identifiers module](https://github.com/mjordan/persistent_identifiers)

You will also need credentials to mint and bind ARKs with the [Names To Things](http://n2t.net) service.

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y n2t`.

## Configuration

Visit `/admin/config/persistent_identifiers/settings` for options.

## Usage

Two ways:

1. Users with the "Mint persistent identifiers" permission will see an option at the bottom of the entity edit form will see a checkbox with the help text "Create ARK". Saving the node with this box checked will mint an ARK for the node and persist it to the field configured in the module's admin settings.
1. Via Views Bulk Operations.

## Identifier metadata


## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## Contributing

Bug reports, improvements, feature requests, and PRs are welcome. Before you open a pull request, please open an issue.

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
