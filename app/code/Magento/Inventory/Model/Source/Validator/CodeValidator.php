<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Check that code is valid
 */
class CodeValidator implements SourceValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(SourceInterface $source): ValidationResult
    {
        $value = (string)$source->getCode();

        if ('' === trim($value)) {
            $errors[] = __('"%field" can not be empty.', ['field' => SourceInterface::CODE]);
        }
        elseif (preg_match('/\s/', $value)) {
            $errors[] = __('"%field" can not contain whitespaces.', ['field' => SourceInterface::CODE]);
        } else {
            $errors = [];
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
