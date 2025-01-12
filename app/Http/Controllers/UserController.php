<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\CreateUserJob;
use App\Jobs\UpdateUserJob;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        CreateUserJob::dispatch(
            $request->name, 
            $request->email, 
            $request->password, 
            $request->password_confirmation
        );

        return response()->json(['message' => 'Usuário sendo criado...'], 201);
    }

    public function index()
    {
        $users = User::all();

        $users->makeHidden(['password', 'mfa', 'mfa_expires_at']);
        
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with('orders')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
    
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        UpdateUserJob::dispatch($id, $request->all());

        return response()->json(['message' => 'Atualização em andamento.'], 202);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuário deletado com sucesso!']);
    }
}
