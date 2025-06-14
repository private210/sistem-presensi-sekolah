<?php

namespace App\Filament\Resources\KepalaSekolahResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\KepalaSekolahResource;

class ListKepalaSekolahs extends ListRecords
{
    protected static string $resource = KepalaSekolahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                // Gunakan slideOver() untuk modal yang slide dari kanan
                ->mutateFormDataUsing(function (array $data): array {
                    // Proses pembuatan user dan penugasan role di sini
                    return DB::transaction(function () use ($data) {
                        // Buat user baru
                        $userData = [
                            'name' => $data['user']['name'] ?? '',
                            'email' => $data['user']['email'] ?? '',
                            'password' => $data['password'] ?? bcrypt('password'),
                        ];

                        $user = User::create($userData);

                        // Berikan role  kepala sekolah
                        $roleKepalaSekolah = \Spatie\Permission\Models\Role::where('name', 'Kepala Sekolah')->first();
                        if ($roleKepalaSekolah) {
                            $user->assignRole($roleKepalaSekolah);
                        }

                        // Tambahkan user_id ke data
                        $data['user_id'] = $user->id;

                        // Hapus data yang tidak perlu
                        unset($data['user']);
                        unset($data['password']);

                        return $data;
                    });
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Kepala Sekolah berhasil ditambahkan')
                        ->body('Data kepala sekolah baru telah berhasil disimpan.')
                ),
        ];
    }
}
