<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Common;

use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\ValueObject\AccountNumber;

/**
 * One DHL Express account associated with a request — shipper,
 * payer, or duties-and-taxes account.
 *
 * Mirrors `supermodelIoLogisticsExpressAccount` in the OpenAPI spec.
 */
final readonly class Account
{
    public function __construct(
        public AccountTypeCode $typeCode,
        public AccountNumber $number,
    ) {
    }

    /**
     * @return array{typeCode: string, number: string}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'number' => $this->number->value,
        ];
    }
}
