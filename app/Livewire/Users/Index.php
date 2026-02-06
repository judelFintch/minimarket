<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public ?string $password = null;
    public string $role = 'vendeur';
    public string $search = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(['admin', 'vendeur', 'vendeur_simple'])],
        ];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editUser(int $userId): void
    {
        $this->authorizeAccess();
        $user = User::query()->findOrFail($userId);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = null;
        $this->role = $user->role ?? 'vendeur';
    }

    public function resetForm(): void
    {
        $this->reset(['userId', 'name', 'email', 'password', 'role']);
        $this->role = 'vendeur';
    }

    public function saveUser(): void
    {
        $this->authorizeAccess();
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        User::updateOrCreate(['id' => $this->userId], $data);

        $this->resetForm();
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);
    }

    public function render()
    {
        $this->authorizeAccess();

        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
