<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
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
                                    ->alphaDash()
                                    ->validationMessages([
                                        'required' => ':attribute zorunludur.',
                                        'max' => ':attribute en fazla :max karakter olabilir.',
                                        'unique' => ':attribute zaten alınmış.',
                                        'alpha_dash' => ':attribute sadece harf, rakam, tire (-) ve alt çizgi (_) içerebilir.',
                                    ]),
                                TextInput::make('email')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->label('E-posta')
                                    ->required()
                                    ->maxLength(255)
                                    ->email()
                                    ->unique()
                                    ->validationMessages([
                                        'required' => ':attribute zorunludur.',
                                        'max' => ':attribute en fazla :max karakter olabilir.',
                                        'email' => 'Geçerli bir :attribute giriniz.',
                                        'unique' => ':attribute zaten kayıtlı.',
                                    ]),
                                TextInput::make('password')
                                    ->label('Şifre')
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->helperText('En az 8 karakter, 1 büyük harf, 1 küçük harf, 1 rakam ve 1 özel karakter içermelidir.')
                                    ->placeholder(fn(string $operation): ?string => $operation === 'create' ? null : 'Değiştirmek istemiyorsanız boş bırakın')
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->minLength(8)
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn($state) => filled($state))
                                    ->validationMessages([
                                        'min:8' => ':attribute en az :min karakter olmalıdır.',
                                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/' => ':attribute en az 1 büyük harf, 1 küçük harf, 1 rakam ve 1 özel karakter içermelidir.',
                                    ]),
                                TextInput::make('password_confirmation')
                                    ->label('Şifre Tekrar')
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->placeholder(fn(string $operation): ?string => $operation === 'create' ? null : 'Değiştirmek istemiyorsanız boş bırakın')
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->dehydrated(false)
                                    ->same('password')
                                    ->password()
                                    ->revealable()
                                    ->validationMessages([
                                        'same' => 'Şifre alanı ile eşleşmelidir.',
                                        'required' => ':attribute zorunludur.',
                                    ]),
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->label('Roller')
                                    ->prefixIcon('heroicon-m-shield-check')
                                    ->multiple()
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->getOptionLabelFromRecordUsing(fn($record) => match ($record->name) {
                                        'super_admin' => 'Süper Admin',
                                        'admin'       => 'Admin',
                                        'editor'      => 'Editör',
                                        'author'      => 'Yazar',
                                        'user'        => 'Kullanıcı',
                                        default       => $record->name,
                                    })
                                    ->validationMessages([
                                        'required' => ':attribute zorunludur.',
                                    ]),
                            ]),

                        Grid::make(1)->columnSpan(1)->schema([
                            Section::make('Kimlik Bilgileri')
                                ->icon('heroicon-m-identification')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Ad')
                                        ->required()
                                        ->maxLength(255)
                                        ->regex('/^[\pL\s]+$/u')
                                        ->validationMessages([
                                            'required' => ':attribute zorunludur.',
                                            'max' => ':attribute en fazla :max karakter olabilir.',
                                            'regex' => ':attribute sadece harf ve boşluk içerebilir.',
                                        ]),
                                    TextInput::make('surname')
                                        ->label('Soyad')
                                        ->required()
                                        ->maxLength(255)
                                        ->regex('/^[\pL\s]+$/u')
                                        ->validationMessages([
                                            'required' => ':attribute zorunludur.',
                                            'max' => ':attribute en fazla :max karakter olabilir.',
                                            'regex' => ':attribute sadece harf ve boşluk içerebilir.',
                                        ]),
                                ]),
                            Section::make('Durum')
                                ->schema([
                                    Toggle::make('is_active')
                                        ->live()
                                        ->label(fn(Get $get): string => $get('is_active') ? 'Aktif' : 'Pasif')
                                        ->default(true)
                                        ->onIcon('heroicon-m-check')
                                        ->offIcon('heroicon-m-lock-closed')
                                        ->onColor('success')
                                        ->offColor('gray')
                                        ->helperText(
                                            fn(Get $get): string => $get('is_active')
                                                ? 'Şu an kullanıcı sisteme giriş yapabilir.'
                                                : 'Kullanıcı engellenmiştir, giriş yapamaz.'
                                        )
                                ]),
                        ]),
                    ]),
            ]);
    }
}
