<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
    {

        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // En caso de no estar autenticado, redirige al login
        if (!$user) {
            return redirect()->route('login');
        }
        $companies = DB::table('company')->orderBy('id', 'desc')->get();

        foreach ($companies as $c) {
            try {
                // Intentar desencriptar si es cifrado de Laravel
                $c->password = decrypt($c->password);
            } catch (\Exception $e) {
                // NO está encriptado → mostrar tal como está
                $c->password = $c->password;
            }
        }


        return view('company', compact('companies'));
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'company_name' => 'required|string|max:255',
                'user_name'    => 'required|string|max:255',
                'phone_number' => ['required', 'regex:/^\d{10}$/'],
                'password'     => 'required|string|max:255',
                'type'         => 'required|string',
                'url'          => 'required|string|max:255',
                'picture'      => 'nullable|image|max:5000',
            ]);

            $pictureName = null;

            if ($request->hasFile('picture')) {
                $pictureName = time() . $request->picture->getClientOriginalName();
                $request->picture->move(public_path('uploads/company'), $pictureName);
            }

            DB::table('company')->insert([
                'company_name' => $request->company_name,
                'user_name'    => $request->user_name,
                'phone_number' => preg_replace('/\D/', '', $request->phone_number),
                'password' => encrypt($request->password),
                'description'  => $request->description,
                'picture'      => $pictureName,
                'url'          => $request->url,
                'type'         => $request->type
            ]);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        $company = DB::table('company')->where('id', $id)->first();

        if ($company) {
            try {
                $company->password = decrypt($company->password);
            } catch (\Exception $e) {
                $company->password = $company->password;
            }
        }

        return response()->json([
            'ok'   => true,
            'data' => $company,
        ]);
    }


    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'company_name' => 'required|string|max:255',
                'user_name'    => 'required|string|max:255',
                'phone_number' => ['required', 'regex:/^\d{10}$/'],
                'password'     => 'required|string|max:255',
                'type'         => 'required|string',
                'url'          => 'required|string|max:255',
                'picture'      => 'nullable|image|max:5000',
            ]);

            // Obtener registro actual
            $current = DB::table('company')->where('id', $id)->first();

            $data = [
                'company_name' => $request->company_name,
                'user_name'    => $request->user_name,
                'phone_number' => preg_replace('/\D/', '', $request->phone_number),
                'password'     => encrypt($request->password),
                'description'  => $request->description,
                'url'          => $request->url,
                'type'         => $request->type,
            ];

            // Si hay nueva imagen
            if ($request->hasFile('picture')) {

                // Borrar imagen anterior si existe
                if ($current && $current->picture && file_exists(public_path('uploads/company/' . $current->picture))) {
                    unlink(public_path('uploads/company/' . $current->picture));
                }

                // Guardar nueva imagen
                $img = time() . '_' . $request->picture->getClientOriginalName();
                $request->picture->move(public_path('uploads/company'), $img);
                $data['picture'] = $img;
            }

            // Actualizar datos
            DB::table('company')->where('id', $id)->update($data);

            // Obtener registro actualizado
            $updated = DB::table('company')->where('id', $id)->first();

            // Desencriptar para mandarlo al JS
            try {
                $updated->password = decrypt($updated->password);
            } catch (\Exception $e) {
            }

            return response()->json([
                'ok'   => true,
                'data' => $updated
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'  => false,
                'msg' => $e->getMessage()
            ]);
        }
    }


    public function delete($id)
    {
        try {
            DB::table('company')->where('id', $id)->delete();

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }
}
