<?php

namespace Tests\Feature;

use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_expense_with_initial_payment(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = ExpenseCategory::factory()->create();

        Livewire::actingAs($user)
            ->test(ExpensesIndex::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Frais transport')
            ->set('description', 'Taxi livraison')
            ->set('amount', 120.50)
            ->set('currency', 'USD')
            ->set('incurred_at', now()->format('Y-m-d'))
            ->set('initial_payment_amount', 50)
            ->set('initial_payment_method', 'cash')
            ->set('initial_paid_at', now()->format('Y-m-d'))
            ->call('saveExpense');

        $this->assertDatabaseHas('expenses', [
            'title' => 'Frais transport',
            'amount' => 120.50,
            'currency' => 'USD',
            'expense_category_id' => $category->id,
        ]);

        $expense = Expense::query()->firstOrFail();
        $this->assertDatabaseHas('expense_payments', [
            'expense_id' => $expense->id,
            'amount' => 50,
            'payment_method' => 'cash',
        ]);

        $this->assertSame(1, ExpensePayment::query()->count());
    }
}
