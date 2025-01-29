<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // register
    public function register(Request $request)
    {
        // Validate the request data
        // $validated = $request->validate([
        //     'name' =>'required|string|max:255',
        //     'email' =>'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|min:6|confirmed',
        // ]);

        $validated = Validator::make($request->all(),
        [
            'name' =>'required|string|max:255',
            'email' =>'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validated->fails())
        {
            return response()->json($validated->errors(), 403);
        }

        // Create a new user
        // $user = User::create([
        //     'name' => $validated['name'],
        //     'email' => $validated['email'],
        //     'password' => Hash::make($validated['password']),
        // ]);

        try {
            //code...
            $user = User::create([

                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),

            ]);

            // Generate a JWT token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            //return

            return response()->json([
            'access_token' => $token,
            'user' => $user,
            'message' => 'Enregistrement effectué avec succès',

            ], 200);
        } catch (\Exception $exception) {
            //exception $e;
            return response()->json(['error' => $exception->getMessage()], 403);
        }

    }

    // login

    public function login(Request $request)
    {
        // Validate the request data
        // $validated = $request->validate([
        //     'email' =>'required|string|email|max:255',
        //     'password' => 'required|string',
        // ]);

        $validated = Validator::make($request->all(),
        [
            'email' =>'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if($validated->fails())
        {
            return response()->json($validated->errors(), 403);
        }

        $credentials = ['email' => $request->email, 'password' => $request->password];

        try {
            //code...
            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'error' => 'Identifiants incorrects',
                ], 403);
            }

            // Attempt to authenticate the user
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            // return response()->json([
            //     'access_token' => $token,
            //     'user' => $user,

            // ], 200);

            return response()->json([
                'access_token' => $token,
                'user' => $user,
                'message' => 'Connexion réussie.',
            ], 200);


        } catch (\Exception $exception) {

            return response()->json([
                'error' => $exception->getMessage(),
            ], 403);
        }
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccesstoken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',

        ], 200);
    }
}
