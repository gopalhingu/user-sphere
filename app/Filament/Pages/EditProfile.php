<?php

// namespace App\Filament\Pages;

// use Filament\Forms;
// use App\Models\User;
// use Filament\Pages\Page;
// use Forms\Components\Button;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;

// class EditProfile extends Page
// {
//     protected static string $view = 'filament.pages.edit-profile';

//     public $name;
//     public $email;
//     public $password;
//     public $password_confirmation;

//     // Define the form
//     public function mount()
//     {
//         $user = Auth::user();
//         $this->name = $user->name;
//         $this->email = $user->email;
//     }

//     public function updateProfile()
//     {
//         $validatedData = $this->validate([
//             'name' => ['required', 'string', 'max:255'],
//             'email' => ['required', 'email', 'max:255'],
//             'password' => ['nullable', 'confirmed', 'min:8'],
//         ]);

//         $user = Auth::user();
//         $user->name = $validatedData['name'];
//         $user->email = $validatedData['email'];

//         if ($validatedData['password']) {
//             $user->password = Hash::make($validatedData['password']);
//         }

//         $user->save();

//         session()->flash('message', 'Profile updated successfully!');
//     }

//     // Define the form schema
//     protected function getFormSchema(): array
//     {
//         return [
//             Forms\Components\TextInput::make('name')
//                 ->label('Name')
//                 ->required()
//                 ->default($this->name),
            
//             Forms\Components\TextInput::make('email')
//                 ->label('Email')
//                 ->email()
//                 ->required()
//                 ->default($this->email),

//             Forms\Components\TextInput::make('password')
//                 ->label('Password')
//                 ->password()
//                 ->nullable()
//                 ->minLength(8)
//                 ->confirmed()
//                 ->helperText('Leave blank if you don’t want to change your password.'),

//             Forms\Components\TextInput::make('password_confirmation')
//                 ->label('Confirm Password')
//                 ->password()
//                 ->nullable()
//                 ->same('password'),
//         ];
//     }

//     // Define the buttons for saving the form
//     protected function getActions(): array
//     {
//         return [
//             Button::make('Save')
//                 ->action('updateProfile')
//                 ->color('primary')
//                 ->icon('heroicon-o-save'),
//         ];
//     }
// }

