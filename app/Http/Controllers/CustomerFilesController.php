<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileCustomer;
use App\Models\Customer;
use App\Models\User;
use App\Models\SubUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CustomerFilesController extends Controller
{
    public function index($id)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

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

        $path = $file->store("customers_files/customer_{$id}", 'public');

        FileCustomer::create([
            'customer_id' => $id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'uploaded_by_id' => $user->id,
            'uploaded_by_type' => $type,
        ]);

        return back()->with('success', 'File uploaded successfully');
    }

    public function update(Request $request, $id)
    {
        $fileRecord = FileCustomer::findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:20480'
        ]);

        if ($fileRecord->file_path && Storage::disk('public')->exists($fileRecord->file_path)) {
            Storage::disk('public')->delete($fileRecord->file_path);
        }

        $file = $request->file('file');

        $path = $file->store(
            "customers_files/customer_{$fileRecord->customer_id}",
            'public'
        );

        $fileRecord->update([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'updated_at' => now()
        ]);

        return back()->with('success', 'File updated successfully');
    }

    public function destroy($id)
    {
        $file = FileCustomer::findOrFail($id);

        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return back()->with('success', 'File deleted successfully');
    }

    public function view($id)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $file = FileCustomer::findOrFail($id);

        if (!$file->file_path || !Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        $absolutePath = Storage::disk('public')->path($file->file_path);
        $mimeType = Storage::disk('public')->mimeType($file->file_path);

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"'
        ]);
    }

    public function download($id)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $file = FileCustomer::findOrFail($id);

        if (!$file->file_path || !Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        $absolutePath = Storage::disk('public')->path($file->file_path);

        return response()->download($absolutePath, $file->file_name);
    }
}