<?php

/**
 * SPDX-FileCopyrightText: 2026 Editorial CMS Drafts contributors
 * SPDX-License-Identifier: OSL-3.0
 * @package Editorial_CmsDrafts
 */

declare(strict_types=1);

class Editorial_CmsDrafts_Block_Adminhtml_Draft extends Mage_Adminhtml_Block_Template
{
    public function getDraftUrl(): ?string
    {
        $type = $this->getData('source_type');
        $resource = match ($type) {
            'page' => Editorial_CmsDrafts_Adminhtml_Editorial_DraftsController::ADMIN_RESOURCE,
            'block' => Editorial_CmsDrafts_Adminhtml_Editorial_DraftsController::BLOCK_RESOURCE,
            default => null,
        };
        if ($resource === null || !Mage::getSingleton('admin/session')->isAllowed($resource)) {
            return null;
        }

        $source = Mage::registry('cms_' . $type);
        if (!$source instanceof Mage_Cms_Model_Page && !$source instanceof Mage_Cms_Model_Block) {
            return null;
        }

        return $source->getId() ? $this->getUrl('adminhtml/editorial_drafts/' . $type, [$type . '_id' => $source->getId()]) : null;
    }
}
