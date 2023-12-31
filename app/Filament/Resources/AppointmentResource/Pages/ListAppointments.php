<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use Filament\Actions;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AppointmentResource;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $statusCounts = Appointment::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy(function ($item) {
                return $item->status->value;
            });

        $tabs = [
            'Alle afspraken' => Tab::make()->badge($statusCounts->sum('count')),
        ];

        foreach (AppointmentStatus::cases() as $status) {
            $count = $statusCounts[$status->value]->count ?? 0;

            $tabs[$status->getLabel()] = Tab::make()
                ->label($status->getLabel())
                ->badge($count)
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(function (Builder $query) use ($status) {
                    $query->where('status', $status->value);
                });
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Alle afspraken';
    }
}
