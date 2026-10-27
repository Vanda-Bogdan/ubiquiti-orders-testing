<?php
// PHP 8.3

use App\DTO\OrderDTO;
use Illuminate\Support\Collection;

require_once __DIR__ . '/vendor/autoload.php';
$filePath = 'data.json';

try {
    //1. Получение списка заказов из JSON.
    $parsedData = getData($filePath);

    //2. Выбор заказов со статусом paid.
    $paidData = getPaidData($parsedData);

    //3. Подсчёт общей суммы оплаченных заказов.
    $totalAmount = getPaidOrdersTotalAmount($parsedData);

    //4. Подсчёт количества оплаченных заказов.
    $paidCount = getPaidOrdersCount($parsedData);

    //5. Определение самого дорогого оплаченного заказа.
    $maxPriceOrder = getPaidMaxOrder($parsedData);

    //Вариант А. Добавьте фильтрацию заказов по минимальной сумме.
    $taskA = taskA($parsedData, 1000);

    //Вариант Б. Добавьте группировку заказов по статусам с подсчётом количества заказов в каждой группе.
    $taskB = taskB($parsedData);

    $result = [
        'parsed_data' => $parsedData->map(fn(OrderDTO $o) => $o->toArray())->all(),
        'paid_data' => $paidData->map(fn(OrderDTO $o) => $o->toArray())->all(),
        'total_amount' => $totalAmount,
        'paid_count' => $paidCount,
        'max_price_order' => $maxPriceOrder?->toArray(),
        'task_A' => $taskA->map(fn(OrderDTO $o) => $o->toArray())->all(),
        'task_B' => $taskB->all(),
    ];

    //Вариант В. Сохраните результаты обработки в отдельный JSON-файл.
    taskC($result);
} catch (RuntimeException | UnexpectedValueException | InvalidArgumentException $e) {
    fwrite(STDERR, '[ошибка] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}




function getData(string $filePath): Collection
{
    if (file_exists($filePath)) {
        $jsonString = file_get_contents($filePath);
        $data = json_decode($jsonString, true);

        if (json_last_error() === JSON_ERROR_NONE) {

            $orders = collect($data)->map(fn($item) => OrderDTO::fromArray($item));

            return $orders;

        } else {
            throw new UnexpectedValueException('Ошибка декодирования JSON: ' . json_last_error_msg());
        }
    } else {
        throw new RuntimeException('Файл не найден!');
    }
}

/** @var Collection<OrderDTO> $data */
function getPaidData(Collection $data): Collection
{
    return $data->where('status', 'paid');
}

/** @var Collection<OrderDTO> $data */
function getPaidOrdersTotalAmount(Collection $data): int
{
    return (int) getPaidData($data)->sum('amount');
}

/** @var Collection<OrderDTO> $data */
function getPaidOrdersCount(Collection $data): int
{
    return getPaidData($data)->count();
}

/** @var Collection<OrderDTO> $data */
function getPaidMaxOrder(Collection $data): ?OrderDTO
{
    return getPaidData($data)->sortByDesc('amount')->first();
}

/** @var Collection<OrderDTO> $data */
function taskA(Collection $data, int $minAmount): Collection
{
    return $data->where('amount', '>=', $minAmount)->values();
}

/** @var Collection<OrderDTO> $data */
function taskB(Collection $data): Collection
{
    return $data->groupBy('status')->map(fn($group) => $group->count());
}

function taskC(array $result): void
{
    $json = json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
    );
    file_put_contents('result.json', $json);
}
