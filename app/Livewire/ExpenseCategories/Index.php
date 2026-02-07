<?php

namespace App\Livewire\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $name = '';
    public ?int $categoryId = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')->ignore($this->categoryId),
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editCategory(int $categoryId): void
    {
        $this->authorizeAccess();
        $category = ExpenseCategory::query()->findOrFail($categoryId);

        $this->categoryId = $category->id;
        $this->name = $category->name;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'categoryId']);
    }

    public function saveCategory(): void
    {
        $this->authorizeAccess();
        $validated = $this->validate();

        ExpenseCategory::updateOrCreate(
            ['id' => $this->categoryId],
            $validated
        );

        $this->resetForm();
    }

    public function deleteCategory(int $categoryId): void
    {
        $this->authorizeAccess();
        ExpenseCategory::query()->findOrFail($categoryId)->delete();
        $this->resetForm();
    }

    public function render()
    {
        $this->authorizeAccess();
        $categories = ExpenseCategory::query()
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.expense-categories.index', [
            'categories' => $categories,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
