<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\PostHistory;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $post = $this->getRecord();
        $user = Auth::user();

        if (! $user->hasRole(['super_admin', 'admin']) && ! in_array($post->status, [0, 3])) {
            Notification::make()
                ->title('Yetkisiz İşlem')
                ->body('Onaya gönderilmiş, yayınlanmış veya planlanmış gönderileri düzenleyemezsiniz.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['status']) && $data['status'] === 2 && $this->getRecord()->status != 2) {
            $publishedAt = isset($data['published_at'])
                ? Carbon::parse($data['published_at'])
                : now();

            if ($publishedAt->isPast()) {
                $data['published_at'] = now();

                Notification::make()
                    ->title('Tarih Güncellendi')
                    ->body('Planlanan tarih geçmişte kaldığı için şu anki zaman olarak ayarlandı.')
                    ->info()
                    ->send();
            }
        }
        return $data;
    }

    protected function afterSave(): void
    {
        PostHistory::create([
            'post_id'       => $this->getRecord()->id,
            'user_id'       => Auth::id(),
            'action'        => 'Güncellendi',
            'description'   => 'Güncelleme işlemi yapıldı.'
        ]);
    }
}
