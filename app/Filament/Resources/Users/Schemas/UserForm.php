<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->columnSpanFull()->schema([
                    Section::make('Hesap Bilgileri')
                        ->icon('heroicon-m-user')
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            TextInput::make('username')
                                ->prefixIcon('heroicon-m-at-symbol')
                                ->label('Kullanıcı Adı')
                                ->helperText('Sadece harf, rakam, tire (-) ve alt çizgi (_) kullanabilirsiniz.')
                                ->required()
                                ->maxLength(20)
                                ->unique()
                                ->alphaDash(),
                            TextInput::make('email')
                                ->prefixIcon('heroicon-m-envelope')
                                ->label('E-posta')
                                ->required()
                                ->maxLength(255)
                                ->email()
                                ->unique(),
                            TextInput::make('password')
                                ->label('Şifre')
                                ->prefixIcon('heroicon-m-lock-closed')
                                ->helperText('En az 8 karakter, 1 büyük harf, 1 küçük harf, 1 rakam ve 1 özel karakter içermelidir.')
                                ->placeholder(fn(string $operation): ?string => $operation === 'create' ? null : 'Değiştirmek istemiyorsanız boş bırakın')
                                ->required(fn(string $context): bool => $context === 'create')
                                ->minLength(8)
                                ->regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/')
                                ->password()
                                ->revealable()
                                ->confirmed()
                                ->live(onBlur: true)
                                ->dehydrated(fn($state) => filled($state)),
                            TextInput::make('password_confirmation')
                                ->label('Şifre Tekrar')
                                ->prefixIcon('heroicon-m-lock-closed')
                                ->placeholder(fn(string $operation): ?string => $operation === 'create' ? null : 'Değiştirmek istemiyorsanız boş bırakın')
                                ->required(fn(string $operation, Get $get): bool => $operation === 'create' || filled($get('password')))->dehydrated(false)
                                ->same('password')
                                ->password()
                                ->revealable(),
                            Select::make('roles')
                                ->relationship(
                                    name: 'roles',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn($query) => Auth::user()?->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin'),
                                )->label('Roller')
                                ->prefixIcon('heroicon-m-shield-check')
                                ->multiple()
                                ->required()
                                ->preload()
                                ->searchable()
                                ->columnSpanFull()
                                ->getOptionLabelFromRecordUsing(fn($record) => __('roles.' . $record->name)),
                        ]),
                    Grid::make(1)->columnSpan(1)->schema([
                        Section::make('Kimlik Bilgileri')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ad')
                                    ->required()
                                    ->maxLength(255)
                                    ->regex('/^[\pL\s]+$/u'),
                                TextInput::make('surname')
                                    ->label('Soyad')
                                    ->required()
                                    ->maxLength(255)
                                    ->regex('/^[\pL\s]+$/u'),
                            ]),
                        Section::make('Durum')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Toggle::make('status')
                                    ->live()
                                    ->label(fn(Get $get): string => $get('status') ? 'Aktif' : 'Pasif')
                                    ->default(true)
                                    ->onIcon('heroicon-m-lock-open')
                                    ->offIcon('heroicon-m-lock-closed')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->helperText(
                                        fn(Get $get): string => $get('status')
                                            ? 'Şu an kullanıcı sisteme giriş yapabilir.'
                                            : 'Kullanıcı engellenmiştir, giriş yapamaz.'
                                    )
                                    ->afterStateUpdated(function (Set $set, $state, ?Model $record) {
                                        if ($record && $record->id === Auth::id() && $state === false) {

                                            $set('status', true);

                                            Notification::make()
                                                ->title('Engellendi')
                                                ->body('Kendi hesabınızı pasif yapamazsınız.')
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ]),
                    ]),
                ]),
            ]);
    }
}
