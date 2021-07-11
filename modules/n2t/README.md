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

Note that if you want to use "branded" identifier strings, you will need to configure a web server to forward to the N2T resolver as documented at [N2T API documentation](http://n2t.net/e/n2t_apidoc.html). If you do not use this feature, your ARKs will contain the hostname of the N2T resolver, `https://n2t.net` (or whatever you use in the "N2T API endpoint" setting).

## Usage

Two ways:

1. Users with the "Mint persistent identifiers" permission will see an option at the bottom of the entity edit form will see a checkbox with the help text "Create ARK". Saving the node with this box checked will mint an ARK for the node and persist it to the field configured in the module's admin settings.
1. Via Views Bulk Operations using the "Mint N2T Ark" action.

## Identifier metadata

If you select the option to "Add basic identifier metadata", the ARK elements 'who', 'what', 'when', and 'how' are bound with the ARK. Currently, only 'what' is populated, with the node's title. The other three elements are assigned the reserved missing value "to be assigned or announced later". This metadata can be retrieved from the ARK resolver service by simply adding a `?` to the end of the ARK URL, e.g. `https://ids-n2t2-stg.n2t.net/ark:/99999/fq31s3t?`.

If you do not select this option, no metadata is bound to the ARK.

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## Contributing

Bug reports, improvements, feature requests, and PRs are welcome. Before you open a pull request, please open an issue.

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
