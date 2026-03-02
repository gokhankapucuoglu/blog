<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordRouteKeyName = 'username';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    // protected static string|UnitEnum|null $navigationGroup = 'Ayarlar';
    // protected static ?int $navigationSort = 1;
    protected static ?string $pluralModelLabel = 'Kullanıcılar';
    protected static ?string $modelLabel = 'Kullanıcı';

    protected static ?string $recordTitleAttribute = 'username';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        $authUser = Auth::user();

        // 1. Super Admin geri kalan herkesi görür
        if ($authUser?->hasRole('super_admin')) {
            return $query;
        }

        // 3. Düz Admin ise, listede Super Admin'leri de göremesin
        if ($authUser?->hasRole('admin')) {
            return $query
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'super_admin');
                });
        }

        return $query;
    }
}
