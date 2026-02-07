<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public ?int $expenseId = null;
    public ?int $categoryId = null;
    public string $title = '';
    public ?string $description = null;
    public ?float $amount = null;
    public string $currency = 'CDF';
    public ?string $incurred_at = null;
    public $receipt = null;
    public ?string $notes = null;

    public ?float $initial_payment_amount = null;
    public ?string $initial_payment_method = null;
    public ?string $initial_paid_at = null;
    public ?string $initial_payment_notes = null;

    public ?int $paymentExpenseId = null;
    public ?float $payment_amount = null;
    public ?string $payment_method = null;
    public ?string $paid_at = null;
    public ?string $payment_notes = null;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'categoryId' => ['nullable', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['CDF', 'USD', 'EUR'])],
            'incurred_at' => ['nullable', 'date'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'initial_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'initial_payment_method' => ['nullable', 'string', 'max:50'],
            'initial_paid_at' => ['nullable', 'date'],
            'initial_payment_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->incurred_at = now()->format('Y-m-d');
        $this->initial_paid_at = now()->format('Y-m-d');
        $this->paid_at = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editExpense(int $expenseId): void
    {
        $this->authorizeAccess();
        $expense = Expense::query()->findOrFail($expenseId);

        $this->expenseId = $expense->id;
        $this->categoryId = $expense->expense_category_id;
        $this->title = $expense->title;
        $this->description = $expense->description;
        $this->amount = (float) $expense->amount;
        $this->currency = $expense->currency;
        $this->incurred_at = $expense->incurred_at?->format('Y-m-d');
        $this->notes = $expense->notes;
        $this->receipt = null;
    }

    public function resetForm(): void
    {
        $this->reset([
            'expenseId',
            'categoryId',
            'title',
            'description',
            'amount',
            'currency',
            'incurred_at',
            'receipt',
            'notes',
            'initial_payment_amount',
            'initial_payment_method',
            'initial_paid_at',
            'initial_payment_notes',
        ]);
        $this->incurred_at = now()->format('Y-m-d');
        $this->initial_paid_at = now()->format('Y-m-d');
    }

    public function saveExpense(): void
    {
        $this->authorizeAccess();
        $validated = $this->validate();

        if ($validated['initial_payment_amount'] !== null && $validated['initial_payment_amount'] > $validated['amount']) {
            $this->addError('initial_payment_amount', 'Le paiement initial ne peut pas depasser le montant.');
            return;
        }

        DB::transaction(function () use ($validated) {
            $receiptPath = null;
            if ($this->receipt) {
                $receiptPath = $this->receipt->store('receipts', 'public');
            }

            $expense = Expense::updateOrCreate(
                ['id' => $this->expenseId],
                [
                    'user_id' => auth()->id(),
                    'expense_category_id' => $validated['categoryId'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'incurred_at' => $validated['incurred_at'],
                    'notes' => $validated['notes'],
                    'receipt_path' => $receiptPath ?? ($this->expenseId ? Expense::query()->whereKey($this->expenseId)->value('receipt_path') : null),
                ]
            );

            if (! $this->expenseId && $validated['initial_payment_amount'] !== null && $validated['initial_payment_amount'] > 0) {
                ExpensePayment::create([
                    'expense_id' => $expense->id,
                    'amount' => $validated['initial_payment_amount'],
                    'payment_method' => $validated['initial_payment_method'],
                    'paid_at' => $validated['initial_paid_at'],
                    'notes' => $validated['initial_payment_notes'],
                ]);
            }
        });

        $this->resetForm();
    }

    public function savePayment(): void
    {
        $this->authorizeAccess();

        $data = $this->validate([
            'paymentExpenseId' => ['required', 'exists:expenses,id'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'paid_at' => ['nullable', 'date'],
            'payment_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        ExpensePayment::create([
            'expense_id' => $this->paymentExpenseId,
            'amount' => $data['payment_amount'],
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
            'notes' => $this->payment_notes,
        ]);

        $this->reset(['paymentExpenseId', 'payment_amount', 'payment_method', 'payment_notes']);
        $this->paid_at = now()->format('Y-m-d');
    }

    public function render()
    {
        $this->authorizeAccess();

        $categories = ExpenseCategory::query()->orderBy('name')->get();

        $expenses = Expense::query()
            ->with(['category', 'payments'])
            ->withSum('payments', 'amount')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('incurred_at')
            ->orderByDesc('id')
            ->paginate(10);

        $expenseOptions = Expense::query()
            ->orderByDesc('incurred_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'title', 'amount', 'currency']);

        return view('livewire.expenses.index', [
            'categories' => $categories,
            'expenses' => $expenses,
            'expenseOptions' => $expenseOptions,
        ])->layout('layouts.app');
    }

    public function getReceiptUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
