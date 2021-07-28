# Local ARKs

## Introduction

Drupal module that mints ARKs locally and redirects them from the Names To Things service at https://n2t.net back to the local Drupal. This allows you to create and publish ARKs for nodes like `https://n2t.net/ark:/9999/jdlwicl` that automatically resolve to `https://yourdrupalhost.com/ark:/9999/jdlwicl`. If your Drupal instance is the target for this redirection from `https://n2t.net/`, this module parses the ARK and redirects the user to the appropriate node. In other words, a user visiting `https://n2t.net/ark:/9999/jdlwicl` will be redirected to the node in your Drupal with that ARK. Using this approach, ARKs are not minted by or persisted at `https://n2t.net`, they are minted by and persisted in the local Drupal. `https://n2t.net` is simply a redirection service.

For the initial redirection from `https://n2t.net/` to work, your Drupal's base URL must be registered as your NAAN's "name mapping authority." This registration is how N2T knows where to redirect ARK requests. You register your Drupal's base URL when you request a Name Assigning Authority Number (NAAN) from the [Names To Things](http://n2t.net) service using [this form](https://goo.gl/forms/bmckLSPpbzpZ5dix1).

## Requirements

* Drupal 8 or 9
* [Persistent Identifiers module](https://github.com/mjordan/persistent_identifiers)
* A Name Assigning Authority Number (NAAN) that has your Drupal's base URL as its name mapping authority value, requested using the form linked above.

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y localarks`.

## Configuration

Visit `/admin/config/persistent_identifiers/settings` for options.

### Redirecting hostname

Do not change the "Redirecting hostname" value unless you are running your own resolution server.

### Shoulders

This module has an option to register "shoulders". NAANs are associated with only one target base URL (say for example `https://ids.exampleuni.ca`). "Shoulders" are a way of identifying subunits within the organization represented by the NAAN in the form of short strings prepended to an ARK's ID string (the part that follows the NAAN and is specific to the resource the ARK redirects to). For example, a university may have the NAAN "9999", resulting in ARK URLs that look like `https://n2t.net/ark:/9999/khgtsso`. An example of this ARK with the shoulder `/s3` (which identifies the Library in this fictitious university) added to the ID string would be `https://n2t.net/ark:/9999/s3khgtsso` (`/s3` is the shoulder prepended to the ID string `/khgtsso`).

#### External shoulders

The primary purpose of shoulders is to provide namespaces internal to an organization to ensure that two departments within the organization-level NAAN do not mint conflicting ID strings. This Drupal module provides an additional service that leverages shoulders: it makes it possible to configure shoulder-to-base URL mappings such that ARKs being accepted by the module automatically redirect the ARK to the base URL associated with the shoulder. That's all it does however - a service that understands how to parse and resolve ARKs needs to be available at the destination URL. That service could be another Drupal running this module, or some other ARK-aware script or application. An important implication of this redirection to other hosts within your orginization is that ARKs containing your NAAN plus shoulders do not identify resources in the local Drupal, they identify resources managed within those other hosts. The series of redirects for these ARKs is 1) from `https://n2t.net`, 2) to the Drupal with this module installed, and finally 3) to the ARK service running on the host associated with a shoulder.

If no shoulder mappings are configured, all incoming ARKs are assumed to have the local Drupal as their redirection target. If you configure a mapping to the local Drupal's base URL, that mapping will be ignored to avoid infinite redirection.

#### Local shoulders

This field contains the shoulder string that your local Drupal must use when it mints ARK identifiers. Use of a shoulder is optional but recommended since it will future-proof your NAAN in the event that other units within your organization will create or manage ARKs in the future. Conventionally, a shoulder should be short and end in a digit, e.g., b1, b2, b3, etc. You do not need to register this shoulder with N2T or anyone else.

## Usage

1. Users with the "Mint persistent identifiers" permission will see an option at the bottom of the entity edit form will see a checkbox with the help text "Create ARK". Saving the node with this box checked will mint an ARK for the node and persist it to the field configured in the module's admin settings.
1. Via Views Bulk Operations using the "Mint ARKs locally" action.

## Identifier metadata and other ARK services

The ARKs minted by this module only offer redirection from N2T. They do not currently provide any additional services such as exposing resource metadata, ARK suffix passthrough, etc.

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## Contributing

Bug reports, improvements, feature requests, and PRs are welcome. Before you open a pull request, please open an issue.

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
