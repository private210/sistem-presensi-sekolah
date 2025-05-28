<?php

namespace App\Filament\Widgets;

use App\Models\Izin;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class IzinPendingWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Izin Menunggu Persetujuan';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                Izin::query()
                    ->where('status', 'Menunggu')
                    ->with(['siswas', 'siswas.kelas'])
                    ->when($user->hasRole('Wali Kelas'), function ($query) use ($user) {
                        $waliKelas = $user->waliKelas;
                        if ($waliKelas) {
                            return $query->whereHas('siswas', function ($q) use ($waliKelas) {
                                $q->where('kelas_id', $waliKelas->kelas_id);
                            });
                        }
                        return $query->where('id', 0); // No results if not assigned to a class
                    })
                    ->latest('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('siswas.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswas.kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Sakit' => 'warning',
                        'Izin' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(
                        fn(Izin $record): bool =>
                        auth()->user()->hasAnyRole(['Wali Kelas',"Admin", 'super_admin']) &&
                            $record->status === 'Menunggu'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Izin')
                    ->modalDescription(
                        fn(Izin $record): string =>
                        "Apakah Anda yakin ingin menyetujui izin {$record->jenis_izin} untuk {$record->siswas->nama_lengkap}?"
                    )
                    ->action(function (Izin $record) {
                        $record->update([
                            'status' => 'Disetujui',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        // Send notification (optional)
                        \Filament\Notifications\Notification::make()
                            ->title('Izin telah disetujui')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(
                        fn(Izin $record): bool =>
                        auth()->user()->hasAnyRole(['Wali Kelas', "Admin", 'super_admin']) &&
                            $record->status === 'Menunggu'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Izin')
                    ->modalDescription(
                        fn(Izin $record): string =>
                        "Apakah Anda yakin ingin menolak izin {$record->jenis_izin} untuk {$record->siswas->nama_lengkap}?"
                    )
                    ->action(function (Izin $record) {
                        $record->update([
                            'status' => 'Ditolak',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        // Send notification (optional)
                        \Filament\Notifications\Notification::make()
                            ->title('Izin telah ditolak')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()
                    ->modalHeading(
                        fn(Izin $record): string =>
                        "Detail Izin - {$record->siswas->nama_lengkap}"
                    ),
            ])
            ->emptyStateHeading('Tidak Ada Izin Menunggu')
            ->emptyStateDescription('Semua izin sudah diproses atau tidak ada pengajuan izin baru.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
