<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\ActivityLog;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class CreateEdit extends Component
{
    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role = 'agent';
    public $phone;
    public $department;
    public $is_active = true;
    
    public function mount($userId = null)
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->phone = $user->phone;
            $this->department = $user->department;
            $this->is_active = $user->is_active;
        }
    }
    
    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->userId ?? 'NULL'),
            'role' => 'required|in:super_admin,admin,manager,agent,viewer',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
        ];
        
        if (!$this->userId) {
            // Creating new user - password required
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            // Editing - password optional
            $rules['password'] = 'nullable|min:8|confirmed';
        }
        
        $validated = $this->validate($rules);
        
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'is_active' => $this->is_active,
        ];
        
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }
        
        if ($this->userId) {
            $user = User::find($this->userId);
            $changes = array_diff_assoc($data, $user->only(array_keys($data)));
            $user->update($data);
            $action = 'updated';
            
            ActivityLog::log($action, 'User', $user->id, $changes);
        } else {
            $user = User::create($data);
            $action = 'created';
            
            ActivityLog::log($action, 'User', $user->id, ['name' => $user->name, 'role' => $user->role]);
        }
        
        session()->flash('success', 'User saved successfully!');
        return redirect()->route('users.index');
    }
    
    public function render()
    {
        return view('livewire.users.create-edit');
    }
}
