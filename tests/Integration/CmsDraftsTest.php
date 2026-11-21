<?php

/**
 * SPDX-FileCopyrightText: 2026 Editorial CMS Drafts contributors
 * SPDX-License-Identifier: OSL-3.0
 */

declare(strict_types=1);

uses(Tests\MahoBackendTestCase::class);

it('copies the saved content and stores to an independent disabled draft', function (string $type, string $sampleTitle): void {
    $model = 'cms/' . $type;
    $source = Mage::getModel($model)->load($sampleTitle, 'title');
    expect($source->getId())->not->toBeNull();

    $sourceData = $source->getData();
    $draftService = Mage::getService('editorial_cmsdrafts/draft');
    $createDraft = $type === 'page' ? $draftService->createPageDraft(...) : $draftService->createBlockDraft(...);
    $first = null;
    $second = null;
    try {
        $first = $createDraft((int) $source->getId());
        $second = $createDraft((int) $source->getId());
        $saved = Mage::getModel($model)->load($first->getId());

        expect($saved->getId())->not->toBe($source->getId())
            ->and($saved->getIsActive())->toBeFalse()
            ->and($saved->getTitle())->not->toBe($source->getTitle())
            ->and($saved->getIdentifier())->not->toBe($source->getIdentifier())
            ->and($saved->getIdentifier())->not->toBe($second->getIdentifier())
            ->and($saved->getTitle())->not->toBe($second->getTitle())
            ->and($saved->getContent())->toBe($source->getContent())
            ->and($saved->getStoreId())->toBe($source->getStoreId());

        if ($type === 'page') {
            expect($saved->getRootTemplate())->toBe($source->getRootTemplate())
                ->and($saved->getMetaDescription())->toBe($source->getMetaDescription());
        }

        $saved->setContent('<p>Edited draft.</p>')->save();
        expect(Mage::getModel($model)->load($source->getId())->getData())->toBe($sourceData);
    } finally {
        foreach ([$first, $second] as $draft) {
            if ($draft?->getId()) {
                $draft->delete();
            }
        }
    }
})->with([
    'page' => ['page', 'Hearthstone'],
    'block' => ['block', 'Category landing: Furniture'],
]);

it('applies native page layout validation before saving a draft', function (): void {
    $source = Mage::getModel('cms/page');
    $source->setTitle('Invalid layout source')
        ->setIdentifier('layout-validation-' . bin2hex(random_bytes(6)))
        ->setIsActive(false)
        ->setRootTemplate('one_column')
        ->setLayoutUpdateXml('<reference name="content">')
        ->setData('stores', [0])
        ->save();
    $draft = null;

    try {
        expect(function () use ($source, &$draft): void {
            $draft = Mage::getService('editorial_cmsdrafts/draft')->createPageDraft((int) $source->getId());
        })->toThrow(Mage_Core_Exception_Input::class);
    } finally {
        $draft?->delete();
        $source->delete();
    }
});

it('uses the existing CMS permission for each kind of draft', function (?string $allowed, bool $canDraftPage, bool $canDraftBlock): void {
    $actor = Mage::getModel('admin/user')->setUserId(42);
    $role = $actor->getAclRole();
    $acl = new Mage_Admin_Model_Acl();
    $acl->addRole($role);
    $acl->addResource('admin/cms/page');
    $acl->addResource('admin/cms/page/save');
    $acl->addResource('admin/cms/block');
    if ($allowed !== null) {
        $acl->allow($role, $allowed);
    }

    $session = Mage::getSingleton('admin/session')->setUser($actor)->setAcl($acl);
    $request = Mage::app()->getRequest();
    $controller = new Editorial_CmsDrafts_Adminhtml_Editorial_DraftsController($request, Mage::app()->getResponse());
    $isAllowed = new ReflectionMethod($controller, '_isAllowed');
    Mage::register('cms_page', Mage::getModel('cms/page')->setId(10));
    Mage::register('cms_block', Mage::getModel('cms/block')->setId(12));

    try {
        foreach (['page' => $canDraftPage, 'block' => $canDraftBlock] as $type => $expected) {
            $request->setActionName($type);
            $button = new Editorial_CmsDrafts_Block_Adminhtml_Draft(['source_type' => $type]);
            expect($isAllowed->invoke($controller))->toBe($expected)
                ->and($button->getDraftUrl() !== null)->toBe($expected);
        }
    } finally {
        Mage::unregister('cms_page');
        Mage::unregister('cms_block');
        $session->setUser(null)->setAcl(null);
    }
})->with([
    'view pages only' => ['admin/cms/page', false, false],
    'save pages' => ['admin/cms/page/save', true, false],
    'manage blocks' => ['admin/cms/block', false, true],
    'no CMS access' => [null, false, false],
]);
