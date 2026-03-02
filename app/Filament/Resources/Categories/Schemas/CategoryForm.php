<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->columnSpanFull()->schema([
                    Section::make('Kategori Bilgileri')
                        ->icon('heroicon-m-squares-plus')
                        ->columnSpan(2)
                        ->schema([
                            Radio::make('menu_type')
                                ->label('Tür')
                                ->options([
                                    'main' => 'Ana Kategori',
                                    'sub'  => 'Alt Kategori',
                                ])
                                ->inline()
                                ->default('main')
                                ->columnSpanFull()
                                ->live()
                                ->afterStateHydrated(function (Radio $component, ?Category $record): void {
                                    if ($record) {
                                        $component->state($record->parent_id ? 'sub' : 'main');
                                    }
                                })
                                ->afterStateUpdated(function (Set $set, string $state) {
                                    if ($state === 'sub' && ! Category::query()->whereNull('parent_id')->exists()) {
                                        Notification::make()
                                            ->title('İşlem Engellendi')
                                            ->body('Sistemde kayıtlı hiç "Ana Kategori" yok. Önce Ana Kategori oluşturmalısınız.')
                                            ->warning()
                                            ->send();

                                        $set('menu_type', 'main');
                                    }
                                }),
                            Select::make('parent_id')
                                ->label('Üst Kategori')
                                ->columnSpanFull()
                                ->relationship(
                                    name: 'parent',
                                    titleAttribute: 'name',
                                    ignoreRecord: true,
                                    modifyQueryUsing: function ($query, $record) {
                                        if ($record) {
                                            $childrenIds = $record->getAllChildrenIds();

                                            $query->whereNotIn('id', $childrenIds);
                                        }
                                    }
                                )
                                ->searchable()
                                ->preload()
                                ->placeholder(function (Get $get, ?Category $record) {
                                    $query = Category::query()->whereNull('parent_id');

                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }

                                    return $query->exists()
                                        ? 'Bir üst kategori seçiniz...'
                                        : 'Sistemde seçilebilecek Üst Kategori yok!';
                                })
                                ->disabled(function (Get $get, ?Category $record) {
                                    if ($get('menu_type') === 'main') return true;

                                    $query = Category::query()->whereNull('parent_id');
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    return ! $query->exists();
                                })
                                ->visible(fn(Get $get): bool => $get('menu_type') === 'sub' && Category::query()->exists())
                                ->required(fn(Get $get): bool => $get('menu_type') === 'sub' && Category::query()->exists()),
                            TextInput::make('name')
                                ->label('Ad')
                                ->columnSpanFull()
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label('Açıklama')
                                ->rows(4)
                                ->columnSpanFull()
                                ->maxLength(500),
                        ]),
                    Section::make('Sıralama ve Görünüm')
                        ->icon('heroicon-m-adjustments-horizontal')
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('order')
                                ->hiddenLabel()
                                ->prefixIcon('heroicon-m-list-bullet')
                                ->default(0)
                                ->required()
                                ->numeric()
                                ->minValue(0),
                            Toggle::make('status')
                                ->live()
                                ->label(fn(Get $get): string => $get('status') ? 'Görünür' : 'Gizli')
                                ->default(true)
                                ->onIcon('heroicon-m-eye')
                                ->offIcon('heroicon-m-eye-slash')
                                ->onColor('success')
                                ->offColor('gray'),
                        ]),
                ]),
            ]);
    }
}
