<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    
    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    
    protected $queryString = ['search', 'roleFilter', 'statusFilter'];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function render()
    {
        $users = User::query()
            ->when($this->search, fn($q) => 
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->when($this->roleFilter, fn($q) => 
                $q->where('role', $this->roleFilter)
            )
            ->when($this->statusFilter !== '', fn($q) => 
                $q->where('is_active', $this->statusFilter)
            )
            ->latest()
            ->paginate(20);
            
        return view('livewire.users.index', [
            'users' => $users,
        ]);
    }
    
    public function toggleStatus($userId)
    {
        if (auth()->id() === (int)$userId) {
            session()->flash('error', 'You cannot deactivate yourself!');
            return;
        }
        
        $user = User::find($userId);
        $user->update(['is_active' => !$user->is_active]);
        
        ActivityLog::log(
            $user->is_active ? 'activated' : 'deactivated',
            'User',
            $userId,
            ['is_active' => $user->is_active]
        );
        
        session()->flash('success', 'User status updated successfully!');
    }
    
    public function deleteUser($userId)
    {
        if (auth()->id() === (int)$userId) {
            session()->flash('error', 'You cannot delete yourself!');
            return;
        }
        
        $user = User::find($userId);
        $userName = $user->name;
        $user->delete();
        
        ActivityLog::log('deleted', 'User', $userId, ['name' => $userName]);
        
        session()->flash('success', "User '{$userName}' deleted successfully!");
    }
}
