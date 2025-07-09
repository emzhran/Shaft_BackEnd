<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
        $this->middleware('is_admin')->only(['index', 'updateStatusAkun']);
    }

    public function uploadIdentitas(Request $request)
    {
        $user = Auth::user();

        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat mengupload identitas untuk diri sendiri.'], 403);
        }

        $request->validate([
            'alamat' => 'required|string|max:255',
            'identitas' => 'required|in:KTP,SIM',
            'nomor_identitas' => 'required|string|max:50',
            'upload_identitas' => 'required|string',
        ]);

        $customer = $user->customer;

        if (!$customer) {
            return response()->json(['message' => 'Data customer tidak ditemukan untuk pengguna ini.'], 404);
        }

        $base64Image = $request->input('upload_identitas');

        try {
            if (Str::startsWith($base64Image, 'data:image')) {
                $base64Image = Str::after($base64Image, ',');
            }

            $decodedImage = base64_decode($base64Image);

            if ($decodedImage === false || empty($decodedImage)) {
                return response()->json(['message' => 'Gagal mendekode gambar Base64.'], 400);
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($decodedImage);
            $extension = 'jpg';

            switch ($mimeType) {
                case 'image/jpeg': $extension = 'jpg'; break;
                case 'image/png': $extension = 'png'; break;
                case 'image/gif': $extension = 'gif'; break;
                default: $extension = 'jpg'; break;
            }

            $fileName = Str::uuid() . '.' . $extension;
            $path = 'identitas/' . $fileName;

            Storage::disk('public')->put($path, $decodedImage);

            $uploadIdentitasUrl = asset('storage/' . $path);

            $customer->alamat = $request->alamat;
            $customer->identitas = $request->identitas;
            $customer->nomor_identitas = $request->nomor_identitas;
            $customer->upload_identitas = $uploadIdentitasUrl;
            $customer->save();

            return response()->json([
                'message' => 'Identitas berhasil diupload!',
                'customer' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan identitas', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatusAkun(Request $request, string $userId)
    {
        $user = User::find($userId);

        if (!$user || $user->role->nama !== 'customer') {
            return response()->json(['message' => 'User atau customer tidak ditemukan.'], 404);
        }

        $request->validate([
            'status_akun' => 'required|in:Terverifikasi,Belum Terverifikasi',
        ]);

        $user->status_akun = $request->status_akun;
        $user->save();

        return response()->json([
            'message' => 'Status akun berhasil diperbarui!',
            'user' => $user->load('role'),
        ]);
    }

    public function getMyProfile()
    {
        $user = Auth::user();

        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat melihat profil sendiri.'], 403);
        }

        $customer = $user->customer;

        if (!$customer) {
            return response()->json(['message' => 'Profil belum lengkap.', 'data' => null], 404);
        }
        return response()->json([
            'message' => 'Profil berhasil ditemukan.',
            'status_code' => 200,
            'data' => $customer,
        ], 200);
    }

    public function index()
    {
        $customers = Customer::with('user.role')->get();
        return response()->json(['data' => $customers]);
    }
}
