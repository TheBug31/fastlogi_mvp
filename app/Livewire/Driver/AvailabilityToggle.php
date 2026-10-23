<?php

namespace App\Livewire\Driver;

use App\Enums\DriverAvailability;
use App\Services\DriverManagementService;
use Livewire\Component;

/**
 * Manual availability switch shown in the driver navbar (RF-005).
 *
 * `offline` is always allowed; going back online recomputes `available`/`busy`
 * from the current workload through {@see DriverManagementService::setAvailability()}.
 */
class AvailabilityToggle extends Component
{
    public function setAvailability(string $availability, DriverManagementService $drivers): void
    {
        $driver = auth()->user()->deliveryDriver;

        if (! $driver) {
            $this->addError('availability', 'No tienes un perfil de repartidor.');

            return;
        }

        $target = DriverAvailability::tryFrom($availability);

        if (! $target || $target === DriverAvailability::Busy) {
            // `busy` is derived from the workload, never set by hand.
            $this->addError('availability', 'Estado de disponibilidad no válido.');

            return;
        }

        $drivers->setAvailability($driver, $target);

        session()->flash('success', $target === DriverAvailability::Offline
            ? 'Quedaste desconectado. No recibirás nuevas ofertas.'
            : 'Quedaste disponible para recibir ofertas.');
    }

    public function render()
    {
        $driver = auth()->user()->deliveryDriver;

        $availability = $driver?->availability ?? DriverAvailability::Offline;
        $current = $availability->value;

        $options = [
            [
                'value' => DriverAvailability::Available->value,
                'label' => 'Disponible',
                'dot' => 'bg-green-500',
                'hint' => 'Recibes ofertas de pedidos.',
                'current' => $current === DriverAvailability::Available->value,
            ],
            [
                'value' => DriverAvailability::Offline->value,
                'label' => 'Desconectado',
                'dot' => 'bg-slate-400',
                'hint' => 'No recibes ofertas.',
                'current' => $current === DriverAvailability::Offline->value,
            ],
        ];

        return view('livewire.driver.availability-toggle', [
            'availability' => $availability,
            'options' => $options,
            'isOffline' => $availability === DriverAvailability::Offline,
        ]);
    }
}
