# Persistent Identifiers

> Note: This is the start of a module to address https://github.com/Islandora/documentation/issues/1042 and https://github.com/Islandora/documentation/issues/1256. It is not yet a production module.

## Introduction

Drupal 8 Module that provides a generalized framework for minting and persisting persistent identifiers (DOIs, ARKs, etc.) for Drupal Entities.

This module's primary use case was to provide this service for [Islandora](https://islandora.ca/) objects, but it can be used without Islandora.

Persistent identifiers can be minted by a variety of sources, such as CrossRef, DataCite, or EZID. Regardless of the specific source, many of the tasks involved in assigning persistent identifiers to Drupal nodes (or other entities) are the same - providing a "Mint Identifier" button in a node edit form, integration into automated workflows, or persisting identifiers to fields on an entity.

This module provides some common tasks while allowing small amounts of code (specifically Drupal services) to handle the particulars of minting and persisting. In addition, a Drupal site admin might want to mix and match minting and persisting services. Generally speaking, this module's goal is to allow site admins to select the minters and persisters they want, while allowing developers to write as little code as necessary to create new minter and persister services.

## Requirements

Drupal 8. This module is not specific to Islandora.

For production use, you will need access to an API for minting persistent identifiers (e.g., [DataCite](https://datacite.org/), [EZID](https://ezid.cdlib.org/), etc.).

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y persistent_identifiers`.

## Configuration

1. Visit `/admin/config/persistent_identifiers/settings` for options.
1. Assign the "Mint persistent identifiers" permission to desired roles.

## Usage

Currently, this module only demonstrates some basic ideas. It "mints" and "persists" identifiers in two ways:

1. When you save a node. Users with the "Mint persistent identifiers" permission will see an option at the bottom of the entity edit for similar to this:
  ![Mint checkbox](docs/images/mint_checkbox.png)
1. When you run the Drush command `drush persistent_identifiers:add_pid x` (where x is a node ID)
1. When you configure a Context using the "Add persistent identifier" reaction.

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## Contributing

Bug reports, improvements, feature requests, and PRs (especially for new minters and persisters) are welcome. Before you open a pull request, please open an issue.

### Writing minters

At a minimum, a minter module contains a services file (e.g., `sample_minter.service.yml`) that mints the identifier. The service must use the ID pattern `foo.minter.sample`, where `foo` is the module's namespace and `sample` is unique to the minter. If the minter requires admin settings, the module should also include an implementation of `hook_form_alter()` that adds minter-specific settings to the admin form at `/admin/config/persistent_identifiers/settings`.

The service class is implemented in the module's `src/Minter` directory. The persistent identifier is generated within and returned by the class's `mint()` method. See the source code in `modules/sample_minter` for more detail.

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
