<?php
namespace App\DTO;


use InvalidArgumentException;

class OrderDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $customer,
        public readonly int $amount,
        public readonly string $status,
    ) {
        if ($this->amount < 0) {
            throw new InvalidArgumentException('Поле amount не может быть отрицательным. ID: ' . $this->id);
        }

        if (!in_array($this->status, ['pending', 'paid'], true)) {
            throw new InvalidArgumentException('Неизвестный статус: ' . $this->status . '. ID: ' . $this->id);
        }
    }

    public static function fromArray(array $data): self
    {
        $id = $data['id'] ?? throw new InvalidArgumentException('Отсутствует обязательное поле: id');
        $customer = $data['customer'] ?? throw new InvalidArgumentException("Отсутствует обязательное поле: customer. ID: $id");

        $amount = $data['amount'] ?? throw new InvalidArgumentException("Отсутствует обязательное поле: amount. ID: $id");
        if (!is_int($amount) && !(is_string($amount) && ctype_digit($amount))) {
            throw new InvalidArgumentException("Поле amount должно быть целым неотрицательным числом. ID: $id");
        }

        $status = $data['status'] ?? throw new InvalidArgumentException("Отсутствует обязательное поле: status. ID: $id");

        return new self(
            id: (int) $id,
            customer: (string) $customer,
            amount: (int) $amount,
            status: (string) $status,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'amount' => $this->amount,
            'status' => $this->status,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            'Заказ №%d, клиент: %s, сумма: %d, статус: %s',
            $this->id,
            $this->customer,
            $this->amount,
            $this->status,
        );
    }
}