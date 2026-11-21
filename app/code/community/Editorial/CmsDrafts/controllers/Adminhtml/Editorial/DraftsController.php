<?php

/**
 * SPDX-FileCopyrightText: 2026 Editorial CMS Drafts contributors
 * SPDX-License-Identifier: OSL-3.0
 * @package Editorial_CmsDrafts
 */

declare(strict_types=1);

use Maho\Config\Route;

class Editorial_CmsDrafts_Adminhtml_Editorial_DraftsController extends Mage_Adminhtml_Controller_Action
{
    public const ADMIN_RESOURCE = 'cms/page/save';
    public const BLOCK_RESOURCE = Mage_Adminhtml_Cms_BlockController::ADMIN_RESOURCE;

    #[Route('/admin/editorial_drafts/page', methods: ['POST'])]
    public function pageAction(): void
    {
        $this->createDraft('page');
    }

    #[Route('/admin/editorial_drafts/block', methods: ['POST'])]
    public function blockAction(): void
    {
        $this->createDraft('block');
    }

    #[\Override]
    protected function _isAllowed(): bool
    {
        return match ($this->getRequest()->getActionName()) {
            'page' => parent::_isAllowed(),
            'block' => Mage::getSingleton('admin/session')->isAllowed(self::BLOCK_RESOURCE),
            default => false,
        };
    }

    private function createDraft(string $type): void
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setHttpResponseCode(405)->setHeader('Allow', 'POST', true);
            return;
        }

        $idParameter = $type . '_id';
        $editRoute = 'adminhtml/cms_' . $type . '/edit';
        $sourceId = filter_var($request->getParam($idParameter), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $session = $this->_getSession();
        $helper = Mage::helper('editorial_cmsdrafts');
        if ($sourceId === false) {
            $session->addError($helper->__('Choose an existing CMS item to copy.'));
            $this->_redirect('adminhtml/cms_' . $type . '/index');
            return;
        }

        try {
            $drafts = Mage::getService('editorial_cmsdrafts/draft');
            $draft = $type === 'page' ? $drafts->createPageDraft($sourceId) : $drafts->createBlockDraft($sourceId);
            $session->addSuccess($helper->__('The draft has been created.'));
            $this->_redirect($editRoute, [$idParameter => $draft->getId()]);
            return;
        } catch (Mage_Core_Exception_Input $error) {
            foreach ($error->getErrors() as $fieldError) {
                $session->addError($fieldError['message']);
            }
        } catch (Mage_Core_Exception_NoSuchEntity $error) {
            $session->addError($error->getMessage());
            $this->_redirect('adminhtml/cms_' . $type . '/index');
            return;
        } catch (Mage_Core_Exception $error) {
            $session->addError($error->getMessage());
        } catch (Exception $error) {
            $session->addException($error, $helper->__('Could not create a draft.'));
        }

        $this->_redirect($editRoute, [$idParameter => $sourceId]);
    }
}
