<?php

namespace App\Filament\Pages;

use Filament\Forms;
use App\Models\Izin;
use Filament\Tables;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class PengajuanIzinWaliMurid extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Izin Anak';
    protected static ?string $navigationGroup = 'Wali Murid';
    protected static string $view = 'filament.pages.pengajuan-izin-wali-murid';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $waliMurid = auth()->user()->waliMurid;
        $siswa = $waliMurid->siswa;

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Siswa')
                    ->schema([
                        Forms\Components\TextInput::make('nama_siswa')
                            ->label('Nama Siswa')
                            ->default($siswa->nama_lengkap)
                            ->disabled(),
                        Forms\Components\TextInput::make('kelas')
                            ->label('Kelas')
                            ->default($siswa->kelas->nama_kelas)
                            ->disabled(),
                        Forms\Components\TextInput::make('nis')
                            ->label('NIS')
                            ->default($siswa->nis)
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail Permohonan Izin')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->minDate(now())
                            ->default(now()),
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->minDate(now())
                            ->afterOrEqual('tanggal_mulai'),
                        Forms\Components\Select::make('jenis_izin')
                            ->label('Jenis Izin')
                            ->options([
                                'Sakit' => 'Sakit',
                                'Izin' => 'Izin',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'Sakit') {
                                    $set('keterangan', 'Anak saya tidak dapat mengikuti pembelajaran karena sakit.');
                                } else {
                                    $set('keterangan', '');
                                }
                            }),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->required()
                            ->placeholder('Jelaskan alasan izin dengan detail...')
                            ->rows(4),
                        // ->columnSpanFull(),
                        Forms\Components\FileUpload::make('bukti_pendukung')
                            ->label('Bukti Pendukung')
                            ->directory('bukti-izin')
                            ->visibility('public')
                            ->storeFileNamesIn('attachment_file_names')
                            // ->storeFileNamesIn('surat_izin')
                            // ->preserveFilenames()
                            ->hint('Upload surat dokter (untuk sakit) atau dokumen pendukung lainnya')
                            ->helperText('File yang didukung: JPG, JPEG, PNG, PDF (Maksimal 2MB)')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'])
                            ->maxSize(2048)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $waliMurid = auth()->user()->waliMurid;

        Izin::create([
            'siswa_id' => $waliMurid->siswa_id,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'jenis_izin' => $data['jenis_izin'],
            'keterangan' => $data['keterangan'],
            'bukti_pendukung' => $data['bukti_pendukung'] ?? null,
            'status' => 'Menunggu',
            'created_by' => auth()->id(),
        ]);

        $this->form->fill();

        Notification::make()
            ->title('Surat izin berhasil diajukan')
            ->body('Permohonan izin telah dikirim ke wali kelas untuk diproses.')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
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
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Tanggal Diproses')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
            ->actions([
                Tables\Actions\ViewAction::make()
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
                                    ->label('Keterangan')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Bukti Pendukung')
                            ->schema([
                                ImageEntry::make('bukti_pendukung')
                                    ->label('Bukti Pendukung')
                                    ->disk('public') // sesuaikan dengan disk yang digunakan
                                    ->size(300)
                                    ->columnSpanFull()
                                    ->visible(fn($record) => $record->bukti_pendukung && $this->isImage($record->bukti_pendukung)),

                                TextEntry::make('bukti_pendukung')
                                    ->label('File Bukti Pendukung')
                                    ->state(fn($record) => basename($record->bukti_pendukung))
                                    ->url(fn($record) => $this->getFileUrl($record->bukti_pendukung))
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->visible(fn($record) => $record->bukti_pendukung && !$this->isImage($record->bukti_pendukung)),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn(Izin $record) => $record->status === 'Menunggu')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->minDate(now()),
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->minDate(now())
                            ->afterOrEqual('tanggal_mulai'),
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
                            ->rows(4),
                        Forms\Components\FileUpload::make('bukti_pendukung')
                            ->label('Bukti Pendukung')
                            ->directory('bukti-izin')
                            ->visibility('public')
                            ->required()
                            ->imagePreviewHeight(250)
                            ->openable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(2048),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(Izin $record) => $record->status === 'Menunggu'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada pengajuan izin')
            ->emptyStateDescription('Anda belum pernah mengajukan surat izin untuk anak Anda.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    protected function getTableQuery(): Builder
    {
        $waliMurid = auth()->user()->waliMurid;

        return Izin::query()
            ->where('siswa_id', $waliMurid->siswa_id)
            ->orderBy('created_at', 'desc');
    }

    public function getTitle(): string
    {
        $waliMurid = auth()->user()->waliMurid;
        $siswaName = $waliMurid->siswa->nama_lengkap ?? 'Anak';

        return "Pengajuan Izin - {$siswaName}";
    }
    protected function isImage(string $filename): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $imageExtensions);
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('create')
    //             ->label('Ajukan Izin Baru')
    //             ->icon('heroicon-o-plus')
    //             ->color('primary')
    //             ->action('create'),
    //     ];
    // }
}
