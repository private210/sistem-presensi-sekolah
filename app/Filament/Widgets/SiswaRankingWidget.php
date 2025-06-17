<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
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
                    $query->where('status', 'Alpa')
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
                    ->label('Rangking')
                    ->state(
                        static function (Tables\Columns\TextColumn $column): int {
                            return $column->getTable()->getRecords()->search($column->getRecord()) + 1;
                        }
                    )
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: $user->hasRole('Wali Kelas')),

                Tables\Columns\TextColumn::make('total_hadir')
                    ->label('Hadir')
                    ->alignCenter()
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_presensi')
                    ->label('Total')
                    ->alignCenter()
                    ->badge()
                    ->searchable()
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
                    ->searchable()
                    ->alignCenter()
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
                    ->searchable()
                    ->color(fn(int $state): string => $state > 0 ? 'danger' : 'success'),

                // Tables\Columns\IconColumn::make('presensi.status')
                //     ->label('Status')
                //     ->icon(function (Siswa $record): string {
                //         if ($record->total_presensi == 0) return 'heroicon-o-minus-circle';

                //         $percentage = ($record->total_hadir / $record->total_presensi) * 100;
                //         if ($percentage >= 90) return 'heroicon-o-check-circle';
                //         if ($percentage >= 80) return 'heroicon-o-exclamation-circle';
                //         return 'heroicon-o-x-circle';
                //     })
                //     ->color(function (Siswa $record): string {
                //         if ($record->total_presensi == 0) return 'gray';

                //         $percentage = ($record->total_hadir / $record->total_presensi) * 100;
                //         if ($percentage >= 90) return 'success';
                //         if ($percentage >= 80) return 'warning';
                //         return 'danger';
                //     }),
            ])
            ->defaultSort('persentase_kehadiran', 'desc')
            // ->filters([
            //     Tables\Filters\SelectFilter::make('kelas_id')
            //         ->label('Kelas')
            //         ->relationship('kelas', 'nama_kelas')
            //         ->visible(fn(): bool => !$user->hasRole('Wali Kelas')),

            //     Tables\Filters\Filter::make('kehadiran_rendah')
            //         ->label('Kehadiran < 80%')
            //         ->query(function ($query): void {
            //             $query->havingRaw('persentase_kehadiran < 0.8');
            //         })
            //         ->toggle(),

            //     Tables\Filters\Filter::make('ada_alpha')
            //         ->label('Ada Alpha')
            //         ->query(function ($query): void {
            //             $query->having('total_alpha', '>', 0);
            //         })
            //         ->toggle(),
            // ])
            ->actions([
            Tables\Actions\ViewAction::make('detail')
                ->label('Detail')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading(fn(Siswa $record): string => 'Detail Kehadiran - ' . $record->nama_lengkap)
                ->modalWidth('3xl')
                ->infolist(fn(Siswa $record): Infolist => $this->getDetailInfolist($record))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
            ])
            ->emptyStateHeading('Tidak Ada Data Kehadiran')
            ->emptyStateDescription('Belum ada data presensi untuk periode ini.')
            ->emptyStateIcon('heroicon-o-chart-bar')
            ->paginated([10, 25, 50]);
    }
    protected function getDetailInfolist(Siswa $record): Infolist
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Get recent attendance records
        $recentAttendance = Presensi::where('siswa_id', $record->id)
            ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->orderBy('tanggal_presensi', 'desc')
            ->limit(10)
            ->get();

        return Infolist::make()
            ->record($record)
            ->schema([
                Section::make('Informasi Siswa')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nama_lengkap')
                                    ->label('Nama Lengkap'),
                                TextEntry::make('nis')
                                    ->label('NIS'),
                                TextEntry::make('kelas.nama_kelas')
                                    ->label('Kelas'),
                                TextEntry::make('jenis_kelamin')
                                    ->label('Jenis Kelamin'),
                            ])
                    ])
                    ->collapsible(),

                Section::make('Statistik Kehadiran Bulan Ini')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_hadir')
                                    ->label('Total Hadir')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('total_izin')
                                    ->label('Total Izin')
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('total_sakit')
                                    ->label('Total Sakit')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('total_alpha')
                                    ->label('Total Alpha')
                                    ->badge()
                                    ->color('danger'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_presensi')
                                    ->label('Total Hari Efektif')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('persentase_kehadiran')
                                    ->label('Persentase Kehadiran')
                                    ->state(function () use ($record): string {
                                        if ($record->total_presensi > 0) {
                                            $percentage = round(($record->total_hadir / $record->total_presensi) * 100, 1);
                                            return $percentage . '%';
                                        }
                                        return '0%';
                                    })
                                    ->badge()
                                    ->color(function () use ($record): string {
                                        if ($record->total_presensi > 0) {
                                            $percentage = ($record->total_hadir / $record->total_presensi) * 100;
                                            if ($percentage >= 90) return 'success';
                                            if ($percentage >= 80) return 'warning';
                                            return 'danger';
                                        }
                                        return 'gray';
                                    }),
                            ])
                    ])

                // Section::make('Riwayat Presensi Terbaru')
                //     ->schema([
                //         TextEntry::make('recent_attendance')
                //             ->label('')
                //             ->state(function () use ($recentAttendance): string {
                //                 if ($recentAttendance->isEmpty()) {
                //                     return 'Belum ada data presensi bulan ini.';
                //                 }

                //                 $html = '<div class="space-y-2">';
                //                 foreach ($recentAttendance as $attendance) {
                //                     $date = Carbon::parse($attendance->tanggal_presensi)->format('d M Y');
                //                     $status = $attendance->status;
                //                     $color = match($status) {
                //                         'Hadir' => 'text-green-600',
                //                         'Izin' => 'text-yellow-600',
                //                         'Sakit' => 'text-blue-600',
                //                         'Tanpa Keterangan' => 'text-red-600',
                //                         default => 'text-gray-600'
                //                     };

                //                     $html .= "<div class='flex justify-between items-center p-2 rounded bg-gray-50'>";
                //                     $html .= "<span class='font-medium'>{$date}</span>";
                //                     $html .= "<span class='px-2 py-1 rounded text-sm {$color} font-semibold'>{$status}</span>";
                //                     $html .= "</div>";
                //                 }
                //                 $html .= '</div>';

                //                 return $html;
                //             })
                //             ->html()
                //     ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
