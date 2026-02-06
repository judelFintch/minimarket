<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $supplierId = null;
    public string $name = '';
    public ?string $contact_name = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $address = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editSupplier(int $supplierId): void
    {
        $this->authorizeAccess();
        $supplier = Supplier::findOrFail($supplierId);

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->contact_name = $supplier->contact_name;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
    }

    public function resetForm(): void
    {
        $this->reset([
            'supplierId',
            'name',
            'contact_name',
            'phone',
            'email',
            'address',
        ]);
    }

    public function saveSupplier(): void
    {
        $this->authorizeAccess();
        $validated = $this->validate();

        Supplier::updateOrCreate(
            ['id' => $this->supplierId],
            $validated
        );

        $this->resetForm();
    }

    public function deleteSupplier(int $supplierId): void
    {
        $this->authorizeAccess();
        Supplier::query()->findOrFail($supplierId)->delete();

        $this->resetForm();
    }

    public function render()
    {
        $this->authorizeAccess();
        $suppliers = Supplier::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('contact_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.suppliers.index', [
            'suppliers' => $suppliers,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
