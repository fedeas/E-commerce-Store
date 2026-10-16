<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use UnitEnum;

class Customers extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $slug = 'customers';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.customers';

    public string $search = '';

    public string $groupFilter = 'all';

    public ?int $selectedUserId = null;

    public ?int $formUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $customerGroup = 'retail';

    public string $phone = '';

    public string $mobile = '';

    public string $companyName = '';

    public string $vatNumber = '';

    public string $occupation = '';

    public string $companyAddress = '';

    public string $city = '';

    public string $postcode = '';

    public string $customerNotes = '';

    public function mount(): void
    {
        $this->selectedUserId = User::query()->orderByDesc('id')->value('id');
        $this->loadCustomerForm();
    }

    public function getViewData(): array
    {
        $users = $this->customerQuery()
            ->limit(120)
            ->get();

        if ($this->selectedUserId === null || ! $users->contains('id', $this->selectedUserId)) {
            $this->selectedUserId = optional($users->first())->id;
        }

        $selectedUser = $this->selectedUserId
            ? User::query()->find($this->selectedUserId)
            : null;

        return [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'stats' => [
                'total' => User::query()->count(),
                'retail' => User::query()->where(function (Builder $query): void {
                    $query->whereNull('customer_group')->orWhere('customer_group', 'retail');
                })->count(),
                'b2b' => User::query()->where('customer_group', 'b2b')->count(),
                'pending' => User::query()
                    ->whereNotNull('b2b_requested_at')
                    ->whereNull('b2b_approved_at')
                    ->whereNull('b2b_rejected_at')
                    ->where(function (Builder $query): void {
                        $query->whereNull('customer_group')->orWhere('customer_group', '<>', 'b2b');
                    })
                    ->count(),
            ],
        ];
    }

    public function selectUser(int $id): void
    {
        $this->selectedUserId = $id > 0 ? $id : null;
        $this->loadCustomerForm();
    }

    public function newCustomer(): void
    {
        $this->selectedUserId = null;
        $this->resetCustomerForm();
    }

    public function saveCustomer(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->formUserId)],
            'password' => [$this->formUserId ? 'nullable' : 'required', 'string', 'min:8'],
            'customerGroup' => ['required', 'in:retail,b2b'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'companyName' => ['nullable', 'string', 'max:255'],
            'vatNumber' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'companyAddress' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:24'],
            'customerNotes' => ['nullable', 'string'],
        ]);

        $data = [
            'name' => trim($this->name),
            'email' => trim($this->email),
            'customer_group' => $this->customerGroup,
            'b2b_approved_at' => $this->customerGroup === 'b2b' ? now() : null,
            'b2b_rejected_at' => null,
            'phone' => $this->nullableText($this->phone),
            'mobile' => $this->nullableText($this->mobile),
            'company_name' => $this->nullableText($this->companyName),
            'vat_number' => $this->nullableText($this->vatNumber),
            'occupation' => $this->nullableText($this->occupation),
            'company_address' => $this->nullableText($this->companyAddress),
            'city' => $this->nullableText($this->city),
            'postcode' => $this->nullableText($this->postcode),
            'customer_notes' => $this->nullableText($this->customerNotes),
        ];

        if (trim($this->password) !== '') {
            $data['password'] = $this->password;
        }

        if ($this->formUserId) {
            $user = User::query()->findOrFail($this->formUserId);
            $user->forceFill($data)->save();
        } else {
            $user = User::query()->create($data);
        }

        $this->password = '';
        $this->selectedUserId = $user->id;
        $this->loadCustomerForm();

        Notification::make()->title('Ο χρήστης αποθηκεύτηκε.')->success()->send();
    }

    public function deleteCustomer(int $id): void
    {
        if (auth()->id() === $id) {
            Notification::make()->title('Δεν μπορείς να διαγράψεις τον συνδεδεμένο χρήστη.')->warning()->send();
            return;
        }

        User::query()->whereKey($id)->delete();

        $this->selectedUserId = User::query()->orderByDesc('id')->value('id');
        $this->loadCustomerForm();

        Notification::make()->title('Ο χρήστης διαγράφηκε.')->success()->send();
    }

    public function setCustomerGroup(int $id, string $group): void
    {
        if (! in_array($group, ['retail', 'b2b'], true)) {
            return;
        }

        $user = User::query()->find($id);

        if (! $user) {
            return;
        }

        $user->forceFill([
            'customer_group' => $group,
            'b2b_approved_at' => $group === 'b2b' ? now() : null,
            'b2b_rejected_at' => null,
        ])->save();

        $this->selectedUserId = $id;
        $this->loadCustomerForm();
    }

    public function rejectB2bRequest(int $id): void
    {
        $user = User::query()->find($id);

        if (! $user) {
            return;
        }

        $user->forceFill([
            'customer_group' => 'retail',
            'b2b_approved_at' => null,
            'b2b_rejected_at' => now(),
        ])->save();

        $this->selectedUserId = $id;
        $this->loadCustomerForm();
    }

    protected function loadCustomerForm(): void
    {
        $user = $this->selectedUserId ? User::query()->find($this->selectedUserId) : null;

        if (! $user) {
            $this->resetCustomerForm();
            return;
        }

        $this->formUserId = (int) $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->password = '';
        $this->customerGroup = (string) ($user->customer_group ?: 'retail');
        $this->phone = (string) ($user->phone ?? '');
        $this->mobile = (string) ($user->mobile ?? '');
        $this->companyName = (string) ($user->company_name ?? '');
        $this->vatNumber = (string) ($user->vat_number ?? '');
        $this->occupation = (string) ($user->occupation ?? '');
        $this->companyAddress = (string) ($user->company_address ?? '');
        $this->city = (string) ($user->city ?? '');
        $this->postcode = (string) ($user->postcode ?? '');
        $this->customerNotes = (string) ($user->customer_notes ?? '');
    }

    protected function resetCustomerForm(): void
    {
        $this->formUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->customerGroup = 'retail';
        $this->phone = '';
        $this->mobile = '';
        $this->companyName = '';
        $this->vatNumber = '';
        $this->occupation = '';
        $this->companyAddress = '';
        $this->city = '';
        $this->postcode = '';
        $this->customerNotes = '';
    }

    protected function nullableText(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function customerQuery(): Builder
    {
        return User::query()
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($this->search)) . '%';

                $query->where(function (Builder $builder) use ($term): void {
                    $builder->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('vat_number', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('mobile', 'like', $term);
                });
            })
            ->when($this->groupFilter === 'retail', function (Builder $query): void {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('customer_group')->orWhere('customer_group', 'retail');
                });
            })
            ->when($this->groupFilter === 'b2b', fn (Builder $query) => $query->where('customer_group', 'b2b'))
            ->when($this->groupFilter === 'pending', function (Builder $query): void {
                $query->whereNotNull('b2b_requested_at')
                    ->whereNull('b2b_approved_at')
                    ->whereNull('b2b_rejected_at')
                    ->where(function (Builder $builder): void {
                        $builder->whereNull('customer_group')->orWhere('customer_group', '<>', 'b2b');
                    });
            })
            ->orderByRaw("CASE WHEN b2b_requested_at IS NOT NULL AND b2b_approved_at IS NULL AND b2b_rejected_at IS NULL AND COALESCE(customer_group, 'retail') <> 'b2b' THEN 0 ELSE 1 END")
            ->orderByDesc('id');
    }
}
