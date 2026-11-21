# CMS Drafts for Maho

Create a disabled draft from an existing CMS page or static block. The editor opens the new draft so you can make changes before publishing it.

## Install

This preview requires PHP 8.5.1 or later and a Maho 27.1 development checkout. It was tested with Maho `main` at `d57ea65a13`. Validation against a stable Maho 27.1 release is pending.

From the Maho source checkout, add the GitHub repository and install the module's `main` branch.

```bash
composer config repositories.cms-drafts vcs https://github.com/ugoparsi/module-cms-drafts
COMPOSER_ROOT_VERSION=27.1.x-dev composer require ugoparsi/module-cms-drafts:dev-main
```

If you already have a checkout of the module's `main` branch next to Maho as `module-cms-drafts`, use a path repository. Run these commands from the Maho checkout.

```bash
composer config repositories.cms-drafts path ../module-cms-drafts
COMPOSER_ROOT_VERSION=27.1.x-dev composer require ugoparsi/module-cms-drafts:dev-main
```

## Create a draft

Open a saved page under **CMS > Pages** or a saved block under **CMS > Static Blocks**. Select **Create draft** to open a disabled copy. Maho applies the same permission as saving a page or managing a block.

The draft gets its own title and identifier. It keeps the last saved content and store views. Page drafts also keep the saved layout, design schedule, metadata and non-unique custom attributes.

Maho applies its normal save rules to the draft. Unique custom attributes use the defaults for a new page. If a required attribute needs a fresh value, Maho displays the validation error so you can create a new page with a valid value.

## Test

Install the module in a local Maho source checkout with the `home` sample data pack. From the store checkout, run the module tests with Maho's Pest configuration.

```bash
vendor/bin/pest vendor/ugoparsi/module-cms-drafts/tests/Integration/CmsDraftsTest.php
```

## License

PHP source and tests use [OSL-3.0](LICENSE.txt). Layouts, templates, module configuration and translations use [AFL-3.0](LICENSE_AFL.txt).
