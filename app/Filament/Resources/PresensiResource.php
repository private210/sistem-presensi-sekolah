<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PresensiResource\Pages;

class PresensiResource extends Resource
{
    protected static ?string $model = Presensi::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Manajemen Presensi';
    protected static ?string $navigationLabel = 'Data Presensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal_presensi')
                    ->label('Tanggal Presensi')
                    ->required()
                    ->default(now())
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),
                Forms\Components\TextInput::make('pertemuan_ke')
                    ->label('Hari Ke')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Select::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->options(function () {
                        return Kelas::pluck('nama_kelas', 'id');
                    })
                    ->required()
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),
                Forms\Components\Select::make('siswa_id')
                    ->label('Siswa')
                    ->relationship('siswa', 'nama_lengkap')
                    ->options(function () {
                        return Siswa::pluck('nama_lengkap', 'id');
                    })
                    ->required()
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),
                Forms\Components\Select::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
                    ])
                    ->default('Hadir')
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pertemuan_ke')
                    ->label('Pertemuan Ke')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_presensi', 'desc')
            ->groups([
                Tables\Grouping\Group::make('Kelas')
                    ->column('kelas.nama_kelas')
                    ->collapsible(),
                Tables\Grouping\Group::make('Tanggal')
                    ->column('tanggal_presensi')
                    ->collapsible(),
            ])
            ->defaultGroup('Kelas')
            ->filters([
                Tables\Filters\Filter::make('tanggal_presensi')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_presensi', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_presensi', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
                    ]),
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPresensis::route('/'),
            // 'create' => Pages\CreatePresensi::route('/create'),
            // 'view' => Pages\ViewPresensi::route('/{record}'),
            // 'edit' => Pages\EditPresensi::route('/{record}/edit'),
        ];
    }

    // Membatasi akses resource berdasarkan role
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()->hasRole('Wali Kelas'), function ($query) {
                $waliKelas = auth()->user()->waliKelas;
                if ($waliKelas) {
                    return $query->where('wali_kelas_id', $waliKelas->id);
                }
                return $query->where('wali_kelas_id', 0); // No results if not a wali kelas
            })
            ->when(auth()->user()->hasRole('Wali Murid'), function ($query) {
                $waliMurid = auth()->user()->waliMurid;
                if ($waliMurid) {
                    return $query->where('siswa_id', $waliMurid->siswa_id);
                }
                return $query->where('siswa_id', 0); // No results if not linked to a student
            });
    }
}
