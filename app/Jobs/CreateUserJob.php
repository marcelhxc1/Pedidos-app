<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Log;

class CreateUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    /**
     * Create a new job instance.
     *
     * @param  string  $name
     * @param  string  $email
     * @param  string  $password
     * @param  string  $password_confirmation
     * @return void
     */
    public function __construct($name, $email, $password, $password_confirmation)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->password_confirmation = $password_confirmation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::error($this->password);
        Log::error($this->password_confirmation);
        if ($this->password != $this->password_confirmation) {
            throw new \Exception('As senhas não estão iguais, por favor corrija e tente novamente.');
        }

        $validator = Validator::make([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validação falhou: ' . $validator->errors()->first());
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Mail::to($user->email)->send(new WelcomeUserMail($user));
    }
}
