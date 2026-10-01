<?php

namespace App\Filament\Resources\HelpTickets\Pages;

use App\Filament\Resources\HelpTickets\HelpTicketResource;
use App\Mail\HelpTicketReplyMail;
use App\Services\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditHelpTicket extends EditRecord
{
    protected static string $resource = HelpTicketResource::class;

    protected bool $hasReply = false;

    protected bool $isNewReply = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->hasReply = ! empty($data['admin_reply']);
        $this->isNewReply = $this->hasReply && $data['admin_reply'] !== $this->record->admin_reply;

        if ($this->hasReply) {
            $data['status'] = 'replied';
        } else {
            $data['status'] = 'pending';
        }

        return $data;
    }

protected function afterSave(): void
{
    if ($this->hasReply) {

        ActivityLogService::log(
            'Ecommerce Panel',  
            'Help Ticket Replied',
            'Replied to Help Ticket #' .
            $this->record->id .
            ' for ' .
            ($this->record->name ?? $this->record->email ?? 'Customer')
        );
    }

    if ($this->isNewReply) {

        // Refresh the record so the latest admin_reply is available
        $this->record->refresh();

        $memberEmail = $this->record->member?->email;

        if ($memberEmail) {
            try {

                Mail::to($memberEmail)->send(
                    new HelpTicketReplyMail($this->record)
                );

            } catch (Throwable $e) {

                report($e);

                Notification::make()
                    ->title('Reply Saved')
                    ->body('The reply was saved, but the email could not be sent to the member.')
                    ->warning()
                    ->send();
            }
        }
    }
}

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function () {

                    ActivityLogService::log(
                        'Ecommerce Panel',
                        'Help Ticket Deleted',
                        'Deleted Help Ticket #' .
                        $this->record->id
                    );

                }),
        ];
    }
}