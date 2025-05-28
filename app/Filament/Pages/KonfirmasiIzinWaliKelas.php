<?php

namespace App\Filament\Pages;

use App\Models\Izin;
use Filament\Tables;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class KonfirmasiIzinWaliKelas extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Konfirmasi Izin Siswa';
    protected static ?string $navigationGroup = 'Manajemen Presensi';
    protected static string $view = 'filament.pages.konfirmasi-izin-wali-kelas';
    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('siswas.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswas.nis')
                    ->label('NIS')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Sakit' => 'warning',
                        'Izin' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Menunggu' => 'gray',
                        'Disetujui' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Disetujui' => 'Disetujui',
                        'Ditolak' => 'Ditolak',
                    ]),
                    // ->default('Menunggu'),
                Tables\Filters\SelectFilter::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->options([
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Detail Surat Izin')
                    ->modalContent(function (Izin $record) {
                        return view('filament.pages.components.detail-izin', compact('record'));
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Izin $record) => $record->status === 'Menunggu')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Persetujuan')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui surat izin ini?')
                    ->action(function (Izin $record) {
                        $record->update([
                            'status' => 'Disetujui',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Surat izin berhasil disetujui')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Izin $record) => $record->status === 'Menunggu')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('catatan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Masukkan alasan penolakan surat izin...'),
                    ])
                    ->action(function (Izin $record, array $data) {
                        $record->update([
                            'status' => 'Ditolak',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'catatan_penolakan' => $data['catatan_penolakan'],
                        ]);

                        Notification::make()
                            ->title('Surat izin ditolak')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_selected')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Persetujuan Massal')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui semua surat izin yang dipilih?')
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'Menunggu') {
                                $record->update([
                                    'status' => 'Disetujui',
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->title("$count surat izin berhasil disetujui")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s') // Auto refresh setiap 30 detik
            ->emptyStateHeading('Tidak ada surat izin')
            ->emptyStateDescription('Belum ada surat izin yang diajukan siswa di kelas Anda.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    protected function getTableQuery(): Builder
    {
        $waliKelas = auth()->user()->waliKelas;

        return Izin::query()
            ->with(['siswas', 'siswas.kelas'])
            ->whereHas('siswas', function ($query) use ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            })
            ->orderBy('created_at', 'desc');
    }

    public function getTitle(): string
    {
        $waliKelas = auth()->user()->waliKelas;
        $kelasName = $waliKelas->kelas->nama_kelas ?? 'Kelas';

        return "Konfirmasi Izin Siswa - {$kelasName}";
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->resetTable()),
        ];
    }
}
