# CMS Drafts for Maho

Create a disabled draft from an existing CMS page or static block. The editor opens the new draft so you can make changes before publishing it.

## Install

Use Maho 27.1 or later in the 27.x series and PHP 8.5.1 or later. Add the module to the store's Composer project.

```bash
composer require editorial/cms-drafts
```

To install from a local checkout, place the module next to the store and add it as a Composer path repository.

```bash
composer config repositories.editorial-drafts path ../cms-drafts
composer require editorial/cms-drafts:dev-main
```

## Create a draft

Open a saved page under **CMS > Pages** or a saved block under **CMS > Static Blocks**. Select **Create draft** to open a disabled copy. Maho applies the same permission as saving a page or managing a block.

The draft gets its own title and identifier. It keeps the last saved content and store views. Page drafts also keep the saved layout, design schedule, metadata and non-unique custom attributes.

Maho applies its normal save rules to the draft. Unique custom attributes use the defaults for a new page. If a required attribute needs a fresh value, Maho displays the validation error so you can create a new page with a valid value.

## Test

Install the module in a local Maho source checkout with the `home` sample data pack. From the store checkout, run the module tests with Maho's Pest configuration.

```bash
vendor/bin/pest vendor/editorial/cms-drafts/tests/Integration/CmsDraftsTest.php
```
