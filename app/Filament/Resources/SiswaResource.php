<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SiswaResource\Pages;
use App\Filament\Resources\SiswaResource\Actions\ImportSiswaAction;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';
    protected static ?string $navigationLabel = 'Daftar Siswa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nis')
                    ->label('NIS')
                    ->required()
                    ->placeholder('Nomor Induk Siswa')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->required()
                    ->placeholder('Masukkan nama lengkap siswa')
                    ->maxLength(255),
                Forms\Components\Select::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        // Jika user adalah wali kelas, hanya tampilkan kelas yang dia pegang
                        if (auth()->user()->hasRole('Wali Kelas')) {
                            $waliKelas = auth()->user()->waliKelas;
                            if ($waliKelas) {
                                return Kelas::where('id', $waliKelas->kelas_id)
                                    ->where('is_active', true)
                                    ->pluck('nama_kelas', 'id');
                            }
                            return collect();
                        }

                        // Admin dan Kepala Sekolah bisa lihat semua
                        return Kelas::where('is_active', true)
                            ->pluck('nama_kelas', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->required(),
                Forms\Components\Textarea::make('alamat')
                    ->label('Alamat')
                    ->maxLength(500)
                    ->placeholder('Masukkan alamat lengkap siswa'),
                // Forms\Components\FileUpload::make('foto')
                //     ->label('Foto')
                //     ->image()
                //     ->directory('siswa-photos')
                //     ->visibility('public')
                //     ->imageResizeMode('cover')
                //     ->imageCropAspectRatio('1:1')
                //     ->imageResizeTargetWidth('300')
                //     ->imageResizeTargetHeight('300'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn(string $state): string => $state === 'L' ? 'Laki-laki' : 'Perempuan'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Tables\Grouping\Group::make('Kelas')
                    ->column('kelas.nama_kelas')
                    ->collapsible(),
            ])
            ->defaultGroup('Kelas')
            ->filters([
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        // Jika user adalah wali kelas, hanya tampilkan kelas yang dia pegang
                        if (auth()->user()->hasRole('Wali Kelas')) {
                            $waliKelas = auth()->user()->waliKelas;
                            if ($waliKelas) {
                                return Kelas::where('id', $waliKelas->kelas_id)
                                    ->pluck('nama_kelas', 'id');
                            }
                            return collect();
                        }

                        // Admin dan Kepala Sekolah bisa lihat semua
                        return Kelas::pluck('nama_kelas', 'id');
                    }),
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->headerActions([
                ImportSiswaAction::make(),
                ImportSiswaAction::downloadTemplate(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSiswas::route('/'),
            // 'create' => Pages\CreateSiswa::route('/create'),
            // 'view' => Pages\ViewSiswa::route('/{record}'),
            // 'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }

    // Membatasi akses resource berdasarkan role
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()->hasRole('Wali Kelas'), function ($query) {
                $waliKelas = auth()->user()->waliKelas;
                if ($waliKelas) {
                    return $query->where('kelas_id', $waliKelas->kelas_id);
                }
                return $query->where('kelas_id', 0); // No results if not assigned to a class
            })
            ->when(auth()->user()->hasRole('Wali Murid'), function ($query) {
                $waliMurid = auth()->user()->waliMurid;
                if ($waliMurid) {
                    return $query->where('id', $waliMurid->siswa_id);
                }
                return $query->where('id', 0); // No results if not linked to a student
            });
    }
}
