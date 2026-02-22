<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Genel Bilgiler')
                        ->columnSpanFull()
                        ->schema([
                            Select::make('category_id')
                                ->label('Kategori')
                                ->relationship(
                                    name: 'category',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn($query) => $query->with('parent')
                                )
                                ->getOptionLabelFromRecordUsing(fn(Category $record) => $record->getHierarchyText())
                                ->searchable()
                                ->preload()
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('title')
                                ->label('Başlık')
                                ->columnSpanFull()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                ->required()
                                ->maxLength(255),
                            Hidden::make('slug')
                                ->dehydrated(),
                            Textarea::make('description')
                                ->label('Açıklama')
                                ->rows(3)
                                ->columnSpanFull()
                                ->extraInputAttributes([
                                    'style' => 'resize: none;',
                                ])
                                ->live(debounce: 300)
                                ->hint(fn(?string $state) => mb_strlen($state ?? '') . '/255')
                                ->hintColor(fn(?string $state) => mb_strlen($state ?? '') > 240 ? 'danger' : 'gray')
                                ->required()
                                ->maxLength(255),
                        ]),
                    Step::make('Kapak ve İçerik')
                        ->columnSpanFull()
                        ->schema([
                            FileUpload::make('image')
                                ->label('Kapak Görseli')
                                ->columnSpanFull()
                                ->disk('public')
                                ->imageEditor()
                                ->directory(fn() => 'images/posts/' . now()->format('Y') . '/' . now()->format('Y-m-d'))
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Get $get) {
                                    $slug = $get('slug') ?: Str::random(10);
                                    $uniqueSuffix = now()->timestamp . '-' . Str::lower(Str::random(4));

                                    return (string) str($slug)
                                        ->append('-' . $uniqueSuffix)
                                        ->append('.' . $file->getClientOriginalExtension());
                                })
                                ->required()
                                ->image()
                                ->maxSize(3120)
                                ->automaticallyCropImagesToAspectRatio('16:9')
                                ->automaticallyResizeImagesMode('cover'),
                            RichEditor::make('content')
                                ->label('İçerik')
                                ->columnSpanFull()
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory(fn() => 'images/posts/' . now()->format('Y') . '/' . now()->format('Y-m-d'))
                                ->resizableImages()
                                ->required()
                                ->validationAttribute('İçerik')
                                ->fileAttachmentsMaxSize(3120),
                        ]),
                    Step::make('SEO ve Yayınlama')
                        ->columns(2)
                        ->schema([
                            Group::make()
                                ->columnSpan(1)
                                ->schema([
                                    TagsInput::make('tags')
                                        ->label('Etiketler')
                                        ->prefixIcon('heroicon-m-tag')
                                        ->placeholder('Örn: yazılım, meb, teknoloji, eğitim')
                                        ->helperText('Kelimeyi yazıp "Enter" tuşuna basarak kutucuk (hashtag) şeklinde ekleyebilirsiniz.')
                                        ->separator(',')
                                        ->required(),
                                    TextInput::make('meta_title')
                                        ->label('Meta Başlık (Google)')
                                        ->placeholder('Örn: Laravel 11 ile Blog Sitesi Yapımı | Kapsamlı Rehber')
                                        ->helperText('Arama sonuçlarında görünecek ana başlıktır. Boş bırakılırsa normal başlık kullanılır. (İdeal uzunluk: 50-60 karakter)')
                                        ->maxLength(255),
                                    Textarea::make('meta_description')
                                        ->label('Meta Açıklama (Google)')
                                        ->placeholder('Örn: Bu rehberde Laravel 11 ve Filament v3 kullanarak sıfırdan adım adım nasıl profesyonel bir blog sitesi yapabileceğinizi öğreneceksiniz. Hemen tıklayın!')
                                        ->helperText('Google aramalarında başlığın altındaki gri tanıtım yazısıdır. (İdeal uzunluk: 150-160 karakter)')
                                        ->rows(4)
                                        ->extraInputAttributes(['style' => 'resize: none;'])
                                        ->maxLength(255),
                                ]),
                            Group::make()
                                ->columnSpan(1)
                                ->schema([
                                    DateTimePicker::make('published_at')
                                        ->label('Yayın Zamanı')
                                        ->helperText('Hemen yayın için boş bırakın.')
                                        ->live()
                                        ->prefixIcon('heroicon-s-calendar')
                                        ->native(false)
                                        ->displayFormat('d/m/Y H:i')
                                        ->seconds(false)
                                        ->minDate(now())
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $user = Auth::user();

                                            if ($state && ! $user?->hasRole(['super_admin', 'admin'])) {
                                                $set('status', 1);
                                            }
                                        }),
                                    Toggle::make('is_featured')
                                        ->live()
                                        ->label(fn(Get $get): string => $get('is_featured') ? 'Öne Çıkan Gönderi' : 'Standart Gönderi')
                                        ->default(false)
                                        ->onIcon('heroicon-m-star')
                                        ->offIcon('heroicon-m-minus')
                                        ->onColor('warning')
                                        ->helperText(
                                            fn(Get $get): string => $get('is_featured')
                                                ? 'Bu makale anasayfada veya özel vitrin alanlarında vurgulanacak.'
                                                : 'Makale standart akışta (kronolojik olarak) listelenecek.'
                                        )
                                        ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'admin'])),
                                    Select::make('status')
                                        ->label(fn() => Auth::user()?->hasRole(['super_admin', 'admin']) ? 'Yayın Durumu / Onay' : 'Gönderi Durumu')
                                        ->options(function () {
                                            if (Auth::user()?->hasRole(['super_admin', 'admin'])) {
                                                return [
                                                    0 => 'Taslak',
                                                    1 => 'Onay Bekliyor',
                                                    2 => 'Yayınla / Onayla',
                                                    3 => 'Reddet / Düzeltme İste',
                                                ];
                                            }
                                            return [
                                                0 => 'Taslak (Henüz Bitmedi)',
                                                1 => 'Onaya Gönder (Admin İncelemesi)',
                                            ];
                                        })
                                        ->default(0)
                                        ->required()
                                        ->native(false)
                                        ->disabled(
                                            fn(?Post $record) =>
                                            ! Auth::user()?->hasRole(['super_admin', 'admin']) && $record?->status !== 0 && $record !== null
                                        )
                                        ->helperText(
                                            fn(Get $get) =>
                                            $get('status') === 1 ? 'Yazınız admin onayına sunulmuştur.' : 'Durumu seçiniz.'
                                        ),
                                ])

                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString(),
            ]);
    }
}
