<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\MfaMail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->sendMfa($request);

        return response()->json(['message' => 'Código MFA enviado para seu e-mail. Por favor, verifique para concluir o login.']);
    }

    public function sendMfa(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $mfa = rand(100000, 999999);

        $user->mfa = $mfa;
        $user->mfa_expires_at = Carbon::now()->addMinutes(10);
        
        $user->save();

        Mail::to($user->email)->send(new MfaMail($mfa));

        return response()->json(['message' => 'MFA enviado com sucesso!']);
    }


    public function verifyMfa(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $mfaExpiresAt = Carbon::parse($user->mfa_expires_at);
        
        if ($user->mfa !== $request->mfa || $mfaExpiresAt->isPast()) {
            return response()->json(['message' => 'Código MFA inválido ou expirado.'], 400);
        }

        $user->update([
            'mfa' => null,
            'mfa_expires_at' => null,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'Autenticação MFA bem-sucedida.', 'token' => $token]);
    }

}
