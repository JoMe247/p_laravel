<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileCustomer;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerFilesController extends Controller
{
    public function index($id)
    {

        // Obtener usuario autenticado (funciona también con remember me)
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        // En caso de no estar autenticado, redirige al login
        if (!$user) {
            return redirect()->route('login');
        }
        $customer = Customer::findOrFail($id);

        $files = FileCustomer::where('customer_id', $id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('files_customer', compact('customer', 'files'));
    }



    public function store(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:20480'
        ]);

        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        $type = Auth::guard('web')->check() ? 'user' : 'sub_user';

        $file = $request->file('file');

        $path = $file->store(
            "customers_files/customer_{$id}",
            'public'
        );

        FileCustomer::create([
            'customer_id' => $id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
            'uploaded_by_id' => $user->id,
            'uploaded_by_type' => $type,
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        $fileRecord = FileCustomer::findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:20480'
        ]);

        Storage::disk('public')->delete($fileRecord->file_path);

        $file = $request->file('file');
        $path = $file->store(
            "customers_files/customer_{$fileRecord->customer_id}",
            'public'
        );

        $fileRecord->update([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
            'updated_at' => now()
        ]);

        return back();
    }

    public function destroy($id)
    {
        $file = FileCustomer::findOrFail($id);

        // 🔒 Seguridad: verificar que exista
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return back()->with('success', 'File deleted successfully');
    }
}
