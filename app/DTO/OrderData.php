<?php
namespace App\DTO;

readonly class OrderData
{
    /** @param OrderItemData[] $items */
    public function __construct(
        public int $customerId,
        public array $items,
    ) {}
}