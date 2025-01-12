<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateUserJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected $userData;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $userData)
    {
        $this->userId = $userId;
        $this->userData = $userData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::error("Usuário não encontrado com ID: {$this->userId}");
            return;
        }

        if (isset($this->userData['name'])) {
            $user->name = $this->userData['name'];
        }

        if (isset($this->userData['email'])) {
            $user->email = $this->userData['email'];
        }

        if (isset($this->userData['password'])) {
            $user->password = bcrypt($this->userData['password']);
        }

        $user->save();
        Log::info("Usuário atualizado com sucesso: {$user->id}");
    }
}
