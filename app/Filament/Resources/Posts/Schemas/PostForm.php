<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\Post;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                Grid::make(3)->columnSpanFull()->schema([
                    Group::make()->columnSpan(2)->schema([
                        Section::make('İçerik Detayları')->schema([
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
                                ->required(),
                            TextInput::make('title')
                                ->label('Başlık')
                                ->live(onBlur: true)
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label('Kısa Açıklama')
                                ->rows(3)
                                ->extraInputAttributes(['style' => 'resize: none;'])
                                ->live(debounce: 300)
                                ->hint(fn(?string $state) => mb_strlen($state ?? '') . '/255')
                                ->hintColor(fn(?string $state) => mb_strlen($state ?? '') > 240 ? 'danger' : 'gray')
                                ->required()
                                ->maxLength(255),
                            RichEditor::make('content')
                                ->label('İçerik')
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory(function (Get $get) {
                                    $slug = $get('title') ? Str::slug($get('title')) : 'taslak';
                                    $folderName = Str::limit($slug, 50, '') ?: 'taslak-' . uniqid();
                                    return 'images/posts/' . now()->format('Y/m/d') . '/' . $folderName;
                                })
                                ->resizableImages()
                                ->fileAttachmentsMaxSize(3120)
                                ->required(),

                        ]),
                    ]),

                    Group::make()->columnSpan(1)->schema([
                        Section::make('Kapak Görseli')->schema([
                            FileUpload::make('image')
                                ->hiddenLabel()
                                ->disk('public')
                                ->imageEditor()
                                ->directory(function (Get $get) {
                                    $slug = $get('title') ? Str::slug($get('title')) : 'taslak';
                                    $folderName = Str::limit($slug, 50, '') ?: 'taslak-' . uniqid();
                                    return 'images/posts/' . now()->format('Y/m/d') . '/' . $folderName;
                                })
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                    return 'kapak-' . uniqid() . '.' . $file->getClientOriginalExtension();
                                })
                                ->image()
                                ->maxSize(3120)
                                ->automaticallyCropImagesToAspectRatio('16:9')
                                ->automaticallyResizeImagesMode('cover')
                                ->required(),
                        ]),

                        Section::make('Yayın ve Planlama')->schema([
                            Toggle::make('is_featured')
                                ->label('Öne Çıkan Gönderi')
                                ->default(false)
                                ->onIcon('heroicon-m-star')
                                ->offIcon('heroicon-m-minus')
                                ->onColor('warning')
                                ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'admin'])),
                            Checkbox::make('is_scheduled')
                                ->label('İleri Tarihli Planla')
                                ->live()
                                ->dehydrated(false)
                                ->afterStateHydrated(fn(Checkbox $component, ?Post $record) => $component->state($record?->published_at?->isFuture() ?? false))
                                ->afterStateUpdated(fn(Set $set, $state) => ! $state ? $set('published_at', null) : null),
                            DateTimePicker::make('published_at')
                                ->label('Planlanacak Tarih')
                                ->prefixIcon('heroicon-s-calendar')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i')
                                ->seconds(false)
                                ->minDate(now())
                                ->visible(fn(Get $get) => $get('is_scheduled'))
                                ->required(fn(Get $get) => $get('is_scheduled')),
                        ]),

                        Section::make('SEO Ayarları')->schema([
                            TagsInput::make('tags')
                                ->label('Etiketler')
                                ->prefixIcon('heroicon-m-tag')
                                ->placeholder('Kelime yazıp "Enter" tusuna basın.')
                                ->separator(',')
                                ->required(),
                            TextInput::make('meta_title')
                                ->label('Meta Başlık (Google)')
                                ->maxLength(60),
                            Textarea::make('meta_description')
                                ->label('Meta Açıklama (Google)')
                                ->rows(4)
                                ->extraInputAttributes(['style' => 'resize: none;'])
                                ->maxLength(160),
                        ]),
                    ]),
                ]),
            ]);
    }
}
