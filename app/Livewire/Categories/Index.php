<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $name = '';
    public ?string $description = null;
    public ?int $categoryId = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->categoryId),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editCategory(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'categoryId']);
    }

    public function saveCategory(): void
    {
        $validated = $this->validate();

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            $validated
        );

        $this->resetForm();
    }

    public function deleteCategory(int $categoryId): void
    {
        Category::query()->findOrFail($categoryId)->delete();

        $this->resetForm();
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.categories.index', [
            'categories' => $categories,
        ])->layout('layouts.app');
    }
}
