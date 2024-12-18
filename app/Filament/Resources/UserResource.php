<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Enums\UserStatus;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static bool $shouldRegisterNavigation = true;

    public static function canViewAny(): bool
    {
        // $user = auth()->user();
        // echo "<pre>";
        // print_r($user->getRoleNames()->first());
        // die;
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(191)
                            ->rules([
                                'unique:users,email,' . optional($form->getRecord())->id
                            ])
                            ->label('Email'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->label('Role')
                            ->searchable()
                            ->required()
                            ->native(false)
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name') // Displays user roles
                    ->label('Role(s)')
                    ->badge() // Optionally display roles as badges
                    ->sortable()
                    ->searchable(),
                /* SelectColumn::make('role')
                    ->label('Role')
                    ->options(Role::all()->pluck('name', 'name')->toArray()) // Fetch roles by name
                    ->default(fn ($record) => $record->getRoleNames()->first()) // Set the default to the first role
                    ->rules(['required'])
                    ->afterStateUpdated(function ($state, $record) {
                        // Check if the role exists in the roles table
                        $role = Role::findByName($state);
                
                        if ($role) {
                            // Use syncRoles correctly to update roles through the pivot table
                            $record->syncRoles([$role->name]); // Sync roles using an array
                            
                            // Notify the user that the role has been updated
                            Notification::make()
                                ->title('Role Updated')
                                ->body("The role has been changed to {$role->name}.")
                                ->success()
                                ->send();
                        } else {
                            // Handle invalid role (optional)
                            Notification::make()
                                ->title('Role Error')
                                ->body("The selected role is invalid.")
                                ->danger()
                                ->send();
                        }
                    }), */
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        UserStatus::Active => 'Active',
                        UserStatus::Inactive => 'Inactive',
                        UserStatus::Suspended => 'Suspended',
                    ])
                    ->default('active')
                    ->rules(['required'])
                    ->afterStateUpdated(function ($state, $record) {
                        $record->update(['status' => $state]);

                        Notification::make()
                        ->title('Status Updated')
                        ->body("The status has been changed to {$state}.")
                        ->success()
                        ->send();
                    }),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Signup At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('User Details')
                    ->schema([
                        TextEntry::make('name')
                            ->label('User Name'),
                        TextEntry::make('email')
                            ->label('User Email'),
                        TextEntry::make('created_at')
                            ->label('Signup At')
                            ->date('Y M d H:i:s'),
                        TextEntry::make('last_login_at')
                            ->label('Last Login')
                            ->date('Y M d H:i:s'),
                    ])->columns(2),
            ]);
    }

    // After saving the message, set the user_id
    public static function actions(): array
    {
        return [
            Tables\Actions\EditAction::make(), // Edit action for the message
            Tables\Actions\DeleteAction::make(), // Delete action for the message
        ];
    }

    public static function bulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(), // Bulk delete action
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('id', '!=', auth()->id());
            // ->whereDoesntHave('roles', function ($query) {
            //     $query->where('name', 'admin');
            // });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
