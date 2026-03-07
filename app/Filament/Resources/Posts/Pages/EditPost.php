<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\PostHistory;
use Carbon\Carbon;
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

    public function getHeading(): string
    {
        return '';
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
