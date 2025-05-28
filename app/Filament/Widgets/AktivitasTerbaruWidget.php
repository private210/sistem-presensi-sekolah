<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Izin;
use Filament\Tables;
use App\Models\Presensi;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AktivitasTerbaruWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Aktivitas Terbaru';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        if ($user->hasRole('Wali Murid')) {
            // Untuk Wali Murid, tampilkan aktivitas anaknya
            return $this->getWaliMuridTable($table);
        } elseif ($user->hasRole('Wali Kelas')) {
            // Untuk Wali Kelas, tampilkan aktivitas kelasnya
            return $this->getWaliKelasTable($table);
        } else {
            // Untuk Kepala Sekolah dan Admin, tampilkan aktivitas umum
            return $this->getKepalaSekolahTable($table);
        }
    }

    private function getWaliMuridTable(Table $table): Table
    {
        $waliMurid = auth()->user()->waliMurid;

        return $table
            ->query(
                Presensi::query()
                    ->where('siswa_id', $waliMurid?->siswa_id)
                    ->with(['siswa', 'kelas'])
                    ->latest('tanggal_presensi')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Izin' => 'info',
                        'Sakit' => 'warning',
                        'Tanpa Keterangan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pertemuan_ke')
                    ->label('Pertemuan')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->placeholder('Tidak ada keterangan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }

    private function getWaliKelasTable(Table $table): Table
    {
        $waliKelas = auth()->user()->waliKelas;

        return $table
            ->query(
                Presensi::query()
                    ->where('kelas_id', $waliKelas?->kelas_id)
                    ->with(['siswa', 'kelas'])
                    ->latest('created_at')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Izin' => 'info',
                        'Sakit' => 'warning',
                        'Tanpa Keterangan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->placeholder('Tidak ada keterangan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
                    ]),
            ])
            ->paginated(false);
    }

    private function getKepalaSekolahTable(Table $table): Table
    {
        return $table
            ->query(
                Presensi::query()
                    ->with(['siswa', 'kelas'])
                    ->latest('created_at')
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->limit(15),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Izin' => 'info',
                        'Sakit' => 'warning',
                        'Tanpa Keterangan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pertemuan_ke')
                    ->label('Pertemuan')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn(Builder $query): Builder => $query->whereDate('tanggal_presensi', Carbon::today()))
                    ->toggle()
                    ->default(true),
            ])
            ->groups([
                Group::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->collapsible(),
                Group::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->collapsible(),
            ])
            ->defaultGroup('kelas.nama_kelas')
            ->paginated(false);
    }
}
