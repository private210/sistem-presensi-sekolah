<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Izin;
use Filament\Tables;
use App\Models\Siswa;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\IzinResource\Pages;

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
                    ->required(),
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
                Forms\Components\FileUpload::make('bukti_pendukung')
                    ->label('Bukti Pendukung (Surat Dokter/Keterangan)')
                    ->directory('bukti-izin')
                    ->columnSpanFull()
                    ->visibility('public')
                    ->maxSize(2048) // 2 MB
                    // ->openable(true)
                    ->preserveFilenames(true)
                    // ->downloadable(true)
                    ->hint('Unggah file JPEG, JPG, PNG atau PDF maksimal 2 MB.')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']),
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
            ->groups([
                Tables\Grouping\Group::make('Kelas')
                    ->column('siswas.kelas.nama_kelas')
                    ->collapsible(),
                Tables\Grouping\Group::make('Tanggal')
                    ->column('tanggal_mulai')
                    ->date('d/m/Y')
                    ->collapsible(),
            ])
            ->defaultGroup('Kelas')
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
                ->label('Lihat Detail')
                ->modalHeading('Detail Surat Izin')
                ->infolist([
                    Section::make('Informasi Siswa')
                        ->schema([
                            TextEntry::make('siswas.nama_lengkap')
                                ->label('Nama Siswa'),
                            TextEntry::make('siswas.nis')
                                ->label('NIS'),
                            TextEntry::make('siswas.kelas.nama_kelas')
                                ->label('Kelas'),
                        ])
                        ->columns(3),

                    Section::make('Detail Izin')
                        ->schema([
                            TextEntry::make('tanggal_mulai')
                                ->label('Tanggal Mulai')
                                ->date('d F Y'),
                            TextEntry::make('tanggal_selesai')
                                ->label('Tanggal Selesai')
                                ->date('d F Y'),
                            TextEntry::make('jenis_izin')
                                ->label('Jenis Izin')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'Sakit' => 'warning',
                                    'Izin' => 'info',
                                    default => 'gray',
                                }),
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'Menunggu' => 'gray',
                                    'Disetujui' => 'success',
                                    'Ditolak' => 'danger',
                                    default => 'gray',
                                }),
                            TextEntry::make('keterangan')
                                ->label('Keterangan'),
                        ])->columns(2),
                    Section::make('Bukti Pendukung')
                        ->schema([
                            ImageEntry::make('bukti_pendukung')
                                ->label('Bukti Pendukung')
                                ->disk('public') // sesuaikan dengan disk yang digunakan
                                ->size(300)
                                ->columnSpanFull()
                                ->visible(fn($record) => $record->bukti_pendukung && self::isImage($record->bukti_pendukung)),
                            TextEntry::make('bukti_pendukung')
                                ->label('File Bukti Pendukung')
                                ->state(fn($record) => basename($record->bukti_pendukung))
                                ->url(fn($record) => $this->getFileUrl($record->bukti_pendukung))
                                ->openUrlInNewTab()
                                ->color('primary')
                                ->icon('heroicon-o-document-arrow-down')
                                ->visible(fn($record) => $record->bukti_pendukung && !self::isImage($record->bukti_pendukung)),
                        ])
                        ->columns(2),

                    Section::make('Informasi Proses')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Tanggal Pengajuan')
                                ->dateTime('d F Y H:i'),
                            TextEntry::make('approved_at')
                                ->label('Tanggal Diproses')
                                ->dateTime('d F Y H:i')
                                ->placeholder('-'),
                            TextEntry::make('approvedBy.name')
                                ->label('Diproses Oleh')
                                ->placeholder('-'),
                            TextEntry::make('catatan_approval')
                                ->label('Catatan Persetujuan')
                                ->placeholder('-')
                                ->columnSpanFull()
                                ->visible(fn($record) => $record->catatan_approval !== null),
                        ])
                        ->columns(3),
                ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(
                        fn(Izin $record) =>
                        auth()->user()->hasRole(['Wali Kelas', 'Admin', 'super_admin']) &&
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
                        auth()->user()->hasRole(['Wali Kelas', 'Admin', 'super_admin']) &&
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
            ->headerActions([
                Tables\Actions\Action::make('refreshData')
                    ->label('Refresh Data')
                    ->color('secondary')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($livewire) {
                        // Use the $livewire parameter to access the component
                        $livewire->resetTable();

                        Notification::make()
                            ->title('Data berhasil di-refresh')
                            ->success()
                            ->send();
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
    protected static function isImage(string $filename): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $imageExtensions);
    }
}
