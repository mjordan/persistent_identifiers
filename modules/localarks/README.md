# Local ARKs

## Introduction

Drupal module that mints ARKs locally and redirects them from the Names To Things service at https://n2t.net back to the local Drupal. This allows you to create and publish ARKs for nodes like `https://n2t.net/ark:/9999/jdlwicl` that automatically resolve to `https://yourdrupalhost.com/ark:/9999/jdlwicl`. When your Drupal instance is the target for this redirection from `https://n2t.net/`, this module parses the ARK and redirects the user to the appropriate node. In other words, a user visiting `https://n2t.net/ark:/9999/jdlwicl` will be redirected to the node in your Drupal with that ARK.

For the initial redirection from `https://n2t.net/` to work, your Drupal's base URL must be registered as your NAAN's "name mapping authority." You register your Drupal's base URL when you request a Name Assigning Authority Number (NAAN) from the [Names To Things](http://n2t.net) service using [this form](https://goo.gl/forms/bmckLSPpbzpZ5dix1).

## Requirements

* Drupal 8 or 9
* [Persistent Identifiers module](https://github.com/mjordan/persistent_identifiers)
* A Name Assigning Authority Number (NAAN) that has your Drupal's base URL as its name mapping authority value

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y localarks`.

## Configuration

Visit `/admin/config/persistent_identifiers/settings` for options.

Do not change the "Redirecting hostname" value unless you are running your own resolution server.

## Usage

1. Users with the "Mint persistent identifiers" permission will see an option at the bottom of the entity edit form will see a checkbox with the help text "Create ARK". Saving the node with this box checked will mint an ARK for the node and persist it to the field configured in the module's admin settings.
1. Via Views Bulk Operations using the "Mint Ark locally" action.

## Identifier metadata and other ARK services

The ARKs minted by this module only offer redirection from N2T. They do not currently provide any additional services such as exposing resource metadata, ARK suffix passthrough, etc.

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## Contributing

Bug reports, improvements, feature requests, and PRs are welcome. Before you open a pull request, please open an issue.

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
