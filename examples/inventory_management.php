<?php

/**
 * Simple CLI inventory management system.
 *
 * Run with: php examples/inventory_management.php
 */

class InventoryItem
{
    public function __construct(
        public int $id,
        public string $name,
        public int $quantity,
        public float $price
    ) {
    }
}

class InventoryManager
{
    /** @var array<int, InventoryItem> */
    private array $items = [];
    private int $nextId = 1;

    public function addItem(string $name, int $quantity, float $price): InventoryItem
    {
        $item = new InventoryItem($this->nextId++, $name, $quantity, $price);
        $this->items[$item->id] = $item;

        return $item;
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        $this->items[$id]->quantity = $quantity;
        return true;
    }

    public function deleteItem(int $id): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        unset($this->items[$id]);
        return true;
    }

    /** @return InventoryItem[] */
    public function listItems(): array
    {
        return array_values($this->items);
    }

    public function getTotalValue(): float
    {
        return array_reduce(
            $this->items,
            fn (float $total, InventoryItem $item): float => $total + ($item->quantity * $item->price),
            0.0
        );
    }
}

function prompt(string $message): string
{
    echo $message;
    return trim((string) fgets(STDIN));
}

function printMenu(): void
{
    echo PHP_EOL;
    echo "Inventory Management System" . PHP_EOL;
    echo "1. Add item" . PHP_EOL;
    echo "2. Update item quantity" . PHP_EOL;
    echo "3. Delete item" . PHP_EOL;
    echo "4. List items" . PHP_EOL;
    echo "5. Show total inventory value" . PHP_EOL;
    echo "6. Exit" . PHP_EOL;
}

function printItems(array $items): void
{
    if ($items === []) {
        echo "No inventory items found." . PHP_EOL;
        return;
    }

    printf("%-5s %-25s %-10s %-10s %-10s%s", 'ID', 'Name', 'Quantity', 'Price', 'Value', PHP_EOL);
    echo str_repeat('-', 65) . PHP_EOL;

    foreach ($items as $item) {
        printf(
            "%-5d %-25s %-10d $%-9.2f $%-9.2f%s",
            $item->id,
            $item->name,
            $item->quantity,
            $item->price,
            $item->quantity * $item->price,
            PHP_EOL
        );
    }
}

$inventory = new InventoryManager();

while (true) {
    printMenu();
    $choice = prompt('Choose an option: ');

    switch ($choice) {
        case '1':
            $name = prompt('Item name: ');
            $quantity = max(0, (int) prompt('Quantity: '));
            $price = max(0.0, (float) prompt('Price: '));
            $item = $inventory->addItem($name, $quantity, $price);
            echo "Added item #{$item->id}." . PHP_EOL;
            break;

        case '2':
            $id = (int) prompt('Item ID: ');
            $quantity = max(0, (int) prompt('New quantity: '));
            echo $inventory->updateQuantity($id, $quantity)
                ? "Quantity updated." . PHP_EOL
                : "Item not found." . PHP_EOL;
            break;

        case '3':
            $id = (int) prompt('Item ID: ');
            echo $inventory->deleteItem($id)
                ? "Item deleted." . PHP_EOL
                : "Item not found." . PHP_EOL;
            break;

        case '4':
            printItems($inventory->listItems());
            break;

        case '5':
            printf("Total inventory value: $%.2f%s", $inventory->getTotalValue(), PHP_EOL);
            break;

        case '6':
            echo "Goodbye!" . PHP_EOL;
            exit(0);

        default:
            echo "Invalid option. Please choose 1-6." . PHP_EOL;
    }
}
