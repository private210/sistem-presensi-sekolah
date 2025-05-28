<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SiswaRankingWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Ranking Kehadiran Siswa Bulan Ini';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Query untuk mendapatkan data siswa dengan statistik kehadiran
        $query = Siswa::query()
            ->where('is_active', true)
            ->withCount([
                'presensi as total_hadir' => function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('status', 'Hadir')
                        ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth]);
                },
                'presensi as total_presensi' => function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth]);
                },
                'presensi as total_alpha' => function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('status', 'Tanpa Keterangan')
                        ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth]);
                }
            ])
            ->with(['kelas']);

        // Filter berdasarkan role
        if ($user->hasRole('Wali Kelas')) {
            $waliKelas = $user->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('ranking')
                    ->label('#')
                    ->state(
                        static function (Tables\Columns\TextColumn $column): int {
                            return $column->getTable()->getRecords()->search($column->getRecord()) + 1;
                        }
                    )
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: $user->hasRole('Wali Kelas')),

                Tables\Columns\TextColumn::make('total_hadir')
                    ->label('Hadir')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_presensi')
                    ->label('Total')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('persentase_kehadiran')
                    ->label('Persentase')
                    ->state(function (Siswa $record): string {
                        if ($record->total_presensi > 0) {
                            $percentage = round(($record->total_hadir / $record->total_presensi) * 100, 1);
                            return $percentage . '%';
                        }
                        return '0%';
                    })
                    ->badge()
                    ->color(function (Siswa $record): string {
                        if ($record->total_presensi > 0) {
                            $percentage = ($record->total_hadir / $record->total_presensi) * 100;
                            if ($percentage >= 90) return 'success';
                            if ($percentage >= 80) return 'warning';
                            return 'danger';
                        }
                        return 'gray';
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderByRaw('(total_hadir / GREATEST(total_presensi, 1)) ' . $direction);
                    }),

                Tables\Columns\TextColumn::make('total_alpha')
                    ->label('Alpha')
                    ->alignCenter()
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('status_kehadiran')
                    ->label('Status')
                    ->icon(function (Siswa $record): string {
                        if ($record->total_presensi == 0) return 'heroicon-o-minus-circle';

                        $percentage = ($record->total_hadir / $record->total_presensi) * 100;
                        if ($percentage >= 90) return 'heroicon-o-check-circle';
                        if ($percentage >= 80) return 'heroicon-o-exclamation-circle';
                        return 'heroicon-o-x-circle';
                    })
                    ->color(function (Siswa $record): string {
                        if ($record->total_presensi == 0) return 'gray';

                        $percentage = ($record->total_hadir / $record->total_presensi) * 100;
                        if ($percentage >= 90) return 'success';
                        if ($percentage >= 80) return 'warning';
                        return 'danger';
                    }),
            ])
            ->defaultSort('persentase_kehadiran', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->visible(fn(): bool => !$user->hasRole('Wali Kelas')),

                Tables\Filters\Filter::make('kehadiran_rendah')
                    ->label('Kehadiran < 80%')
                    ->query(function ($query): void {
                        $query->havingRaw('(total_hadir / GREATEST(total_presensi, 1)) < 0.8');
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('ada_alpha')
                    ->label('Ada Alpha')
                    ->query(function ($query): void {
                        $query->having('total_alpha', '>', 0);
                    })
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(
                        fn(Siswa $record): string =>
                        route('filament.admin.pages.rekap-kepala-sekolah') .
                            '?filters[siswa_id]=' . $record->id
                    ),
            ])
            ->emptyStateHeading('Tidak Ada Data Kehadiran')
            ->emptyStateDescription('Belum ada data presensi untuk periode ini.')
            ->emptyStateIcon('heroicon-o-chart-bar')
            ->paginated([10, 25, 50]);
    }
}
