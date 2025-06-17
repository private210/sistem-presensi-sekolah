<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IzinResource\Pages;
use App\Models\Izin;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manajemen Presensi';
    protected static ?string $navigationLabel = 'Surat Izin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('siswa_id')
                    ->label('Siswa')
                    ->relationship('siswas', 'nama_lengkap')
                    ->options(function () {
                        if (auth()->user()->hasRole('Wali Murid')) {
                            $waliMurid = auth()->user()->waliMurid;
                            return Siswa::where('id', $waliMurid->siswa_id)
                                ->pluck('nama_lengkap', 'id');
                        }

                        return Siswa::pluck('nama_lengkap', 'id');
                    })
                    // ->searchable()
                    ->required()
                    ->disabled(function () {
                        return auth()->user()->hasRole('Wali Murid');
                    })
                    ->default(function () {
                        if (auth()->user()->hasRole('Wali Murid')) {
                            $waliMurid = auth()->user()->waliMurid;
                            return $waliMurid?->siswa_id;
                        }

                        return null;
                    }),
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->required(),
                Forms\Components\Select::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->options([
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('bukti_pendukung')
                    ->label('Bukti Pendukung (Surat Dokter/Keterangan)')
                    ->directory('bukti-izin')
                    ->visibility('public')
                    ->maxSize(1024) // 1 MB
                    ->openable(true)
                    ->downloadable(true)
                    ->hint('Unggah file gambar atau PDF')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf']),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Disetujui' => 'Disetujui',
                        'Ditolak' => 'Ditolak',
                    ])
                    ->default('Menunggu')
                    ->disabled(function () {
                        return !auth()->user()->hasRole(['Wali Kelas', 'super_admin']);
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswas.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswas.kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
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
                    ->label('Dibuat Pada')
                    ->dateTime()
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
                Tables\Filters\SelectFilter::make('jenis_izin')
                    ->label('Jenis Izin')
                    ->options([
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(
                        fn(Izin $record) =>
                        auth()->user()->hasRole(['Wali Kelas', 'Kepala Sekolah', 'super_admin']) &&
                            $record->status === 'Menunggu'
                    )
                    ->action(function (Izin $record) {
                        $record->update([
                            'status' => 'Disetujui',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(
                        fn(Izin $record) =>
                        auth()->user()->hasRole(['Wali Kelas', 'Kepala Sekolah', 'super_admin']) &&
                            $record->status === 'Menunggu'
                    )
                    ->action(function (Izin $record) {
                        $record->update([
                            'status' => 'Ditolak',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListIzins::route('/'),
            // 'create' => Pages\CreateIzin::route('/create'),
            // 'view' => Pages\ViewIzin::route('/{record}'),
            // 'edit' => Pages\EditIzin::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()->hasRole('Wali Kelas'), function ($query) {
                $waliKelas = auth()->user()->waliKelas;
                return $query->whereHas('siswas', function ($q) use ($waliKelas) {
                    $q->where('kelas_id', $waliKelas->kelas_id);
                });
            });
    }
}
