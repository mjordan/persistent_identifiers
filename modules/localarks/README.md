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

"Shoulders" are a way of identifying subunits within the organization represented by the NAAN. For example, a university may have the NAAN "3768", resulting in ARK URLs that look like `https://n2t.net/ark:/3768/khgtsso`. NAANs are associated with only one target base URL (in this example, `https://ids.exampleuni.ca`), and shoulders provide a mechanism insert a department-specific string at the start of ARK ID strings. An example of this ARK with the shoulder `/lib` (which identifies the Library in this fictitious university) added to the ID string would be `https://n2t.net/ark:/3768/libkhgtsso` (`/lib` is the shoulder prepended to the ID string `/khgtsso`).

The primary purpose of shoulders is to provide namespaces internal to an organization to ensure that two departments within the organization-level NAAN do not mint conflicting ID strings. This Drupal module provides an additional services that leverages shoulds: it is possible to configure shoulder-to-URL mappings such that ARKs being accepted by the module automatically redirect the request to the base URL associated with the shoulder. That's it however - a service that understands how to parse the resolve ARKs needs to be available at the destination URL. That service could be another Drupal running this module, or some other ARK-aware script or application.

Shoulder mappings to the local Drupal are ignored, to avoid infinite redirection. If no shoulder mappings are configured, all incoming ARKs are assumed to have the local Drupal as their redirection target.

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
