# CMS Drafts for Maho

Create a disabled draft from an existing CMS page or static block. The editor opens the new draft so you can make changes before publishing it.

## Install

CMS Drafts requires PHP 8.5.1 or later. It supports Maho 26.11.3 and later patches in the 26.11 series. The Maho 27.1 preview was tested on `main` at `d57ea65a13`. Validation against a stable Maho 27.1 release is pending.

From the store's Composer project, add the GitHub repository and install the tagged module release.

```bash
composer config repositories.cms-drafts vcs https://github.com/ugoparsi/module-cms-drafts
composer require ugoparsi/module-cms-drafts:^1.0
```

If you are installing in a Maho source checkout, set `COMPOSER_ROOT_VERSION` for the install command. Use `26.11.3` for a checkout of the Maho 26.11.3 tag or `27.1.x-dev` for the Maho 27.1 development branch.

The module source is available for each [tagged release](https://github.com/ugoparsi/module-cms-drafts/releases).

## Create a draft

Open a saved page under **CMS > Pages** or a saved block under **CMS > Static Blocks**. Select **Create draft** to open a disabled copy. Maho applies the same permission as saving a page or managing a block.

The draft gets its own title and identifier. It keeps the last saved content and store views. Page drafts also keep the saved layout, design schedule and metadata. On the Maho 27.1 development branch, page drafts also keep non-unique custom attributes.

Maho applies its normal save rules to the draft. Unique custom attributes use the defaults for a new page. If a required attribute needs a fresh value, Maho displays the validation error so you can create a new page with a valid value.

## Test

Install the module in a local Maho source checkout with the `home` sample data pack. From the store checkout, run the module tests with Maho's Pest configuration.

```bash
vendor/bin/pest vendor/ugoparsi/module-cms-drafts/tests/Integration/CmsDraftsTest.php
```

## License

PHP source and tests use [OSL-3.0](LICENSE.txt). Layouts, templates, module configuration and translations use [AFL-3.0](LICENSE_AFL.txt).
