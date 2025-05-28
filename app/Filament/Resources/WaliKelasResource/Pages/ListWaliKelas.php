<?php

namespace App\Filament\Resources\WaliKelasResource\Pages;

use App\Filament\Resources\WaliKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ListWaliKelas extends ListRecords
{
    protected static string $resource = WaliKelasResource::class;

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

                        // Berikan role wali_kelas
                        $roleWaliKelas = \Spatie\Permission\Models\Role::where('name', 'Wali Kelas')->first();
                        if ($roleWaliKelas) {
                            $user->assignRole($roleWaliKelas);
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
                        ->title('Wali Kelas ditambahkan')
                        ->body('Data wali kelas baru telah berhasil disimpan.')
                ),
        ];
    }
}
