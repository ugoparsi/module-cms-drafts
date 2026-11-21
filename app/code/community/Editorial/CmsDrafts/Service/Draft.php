<?php

/**
 * SPDX-FileCopyrightText: 2026 Editorial CMS Drafts contributors
 * SPDX-License-Identifier: OSL-3.0
 * @package Editorial_CmsDrafts
 */

declare(strict_types=1);

class Editorial_CmsDrafts_Service_Draft
{
    private const PAGE_FIELDS = [
        'content', 'content_heading', 'root_template', 'layout_update_xml', 'sort_order',
        'custom_theme', 'custom_theme_from', 'custom_theme_to', 'custom_root_template',
        'custom_layout_update_xml', 'meta_robots', 'meta_description', 'meta_keywords',
    ];

    public function createPageDraft(int $sourceId): Mage_Cms_Model_Page
    {
        if (class_exists(Mage_Cms_Service_Page::class)) {
            $pages = $this->getCmsService('cms/page', Mage_Cms_Service_Page::class);
            $source = $pages->getById($sourceId);
            $fields = self::PAGE_FIELDS;
            foreach ($source->getResource()->getCustomAttributes() as $code => $attribute) {
                if (!$attribute->getIsUnique()) {
                    $fields[] = $code;
                }
            }
            return $pages->save($this->preparePageDraft($pages->newPage(), $source, $fields));
        }

        $source = Mage::getModel('cms/page');
        $this->loadSource($source, $sourceId);
        $draft = $this->preparePageDraft(Mage::getModel('cms/page'), $source, self::PAGE_FIELDS);
        $this->validateLayoutUpdates($draft);
        $draft->save();

        return $draft;
    }

    public function createBlockDraft(int $sourceId): Mage_Cms_Model_Block
    {
        if (class_exists(Mage_Cms_Service_Block::class)) {
            $blocks = $this->getCmsService('cms/block', Mage_Cms_Service_Block::class);
            $source = $blocks->getById($sourceId);
            $draft = $this->prepareBlockDraft($blocks->newBlock(), $source);

            return $blocks->save($draft);
        }

        $source = Mage::getModel('cms/block');
        $this->loadSource($source, $sourceId);
        $draft = $this->prepareBlockDraft(Mage::getModel('cms/block'), $source);
        $draft->save();

        return $draft;
    }

    /**
     * @template T of object
     * @param class-string<T> $serviceType
     * @return T
     */
    private function getCmsService(string $alias, string $serviceType): object
    {
        $service = Mage::getService($alias);
        if (!$service instanceof $serviceType) {
            throw new RuntimeException('The ' . $alias . ' service must extend ' . $serviceType . '.');
        }
        return $service;
    }

    /** @param list<string> $fields */
    private function preparePageDraft(Mage_Cms_Model_Page $draft, Mage_Cms_Model_Page $source, array $fields): Mage_Cms_Model_Page
    {
        return $draft->addData(array_intersect_key($source->getData(), array_fill_keys($fields, true)))
            ->addData($this->newIdentity($source))
            ->setData('stores', (array) $source->getStoreId())
            ->setIsActive(false);
    }

    private function prepareBlockDraft(Mage_Cms_Model_Block $draft, Mage_Cms_Model_Block $source): Mage_Cms_Model_Block
    {
        return $draft->addData($this->newIdentity($source))
            ->setContent($source->getContent())
            ->setData('stores', (array) $source->getStoreId())
            ->setIsActive(false);
    }

    private function validateLayoutUpdates(Mage_Cms_Model_Page $draft): void
    {
        $errors = new Mage_Core_Exception_Input();
        foreach (['layout_update_xml', 'custom_layout_update_xml'] as $field) {
            $xml = (string) $draft->getData($field);
            if ($xml === '') {
                continue;
            }
            $validator = Mage::getModel('adminhtml/layoutUpdate_validator');
            if (!$validator->isValid($xml)) {
                foreach ($validator->getMessages() as $message) {
                    $errors->addError($field, (string) $message);
                }
            }
        }
        $errors->throwIfErrors();
    }

    private function loadSource(Mage_Cms_Model_Page|Mage_Cms_Model_Block $source, int $sourceId): void
    {
        $source->load($sourceId);
        if (!$source->getId()) {
            throw new Mage_Core_Exception_NoSuchEntity(Mage::helper('editorial_cmsdrafts')->__('The CMS item no longer exists.'));
        }
    }

    /** @return array{title: string, identifier: string} */
    private function newIdentity(Mage_Cms_Model_Page|Mage_Cms_Model_Block $source): array
    {
        $helper = Mage::helper('editorial_cmsdrafts');
        $sourceTitle = $source->getTitle();
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $reference = bin2hex(random_bytes(8));
            $identifier = 'draft-' . $reference;
            $title = mb_substr(trim($helper->__('Draft %s', $reference) . ' ' . $sourceTitle), 0, 255, 'UTF-8');
            $used = $source->getResourceCollection()->addFieldToFilter(
                ['identifier', 'title'],
                [['eq' => $identifier], ['eq' => $title]],
            )->getSize();
            if ($used === 0) {
                return ['title' => $title, 'identifier' => $identifier];
            }
        }

        throw new Mage_Core_Exception_Conflict($helper->__('Could not choose a unique name for the draft.'));
    }
}
