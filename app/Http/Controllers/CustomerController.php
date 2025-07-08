<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Manajemen Customer
 *
 */
class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
        $this->middleware('is_admin')->only(['index', 'updateStatusAkun']);
    }

    /**
     *
     * @authenticated
     * @urlParam userId required ID user dari customer. Contoh: 2
     * @bodyParam status_akun required Contoh: Terverifikasi
     *
     * @response {
     * "message": "Status akun berhasil diperbarui!",
     * "user": {
     * "id": 2,
     * "nama": "Customer Test",
     * "email": "customer@example.com",
     * "role_id": 2,
     * "status_akun": "Terverifikasi"
     * }
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     * @response 404 {
     * "message": "User atau customer tidak ditemukan."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "status_akun": [
     * "The selected status akun is invalid."
     * ]
     * }
     * }
     */
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

    /**
     * @authenticated
     * @bodyParam identitas required Tipe identitas ('KTP' atau 'SIM'). Example: KTP
     * @bodyParam upload_identitas file required Foto identitas (JPEG, PNG, JPG, GIF). Max 5MB.
     *
     * @response {
     * "message": "Identitas berhasil diupload!",
     * "customer": {
     * "id": 1,
     * "user_id": 2,
     * "nama": "Nama Customer",
     * "alamat": "Alamat Customer",
     * "identitas": "KTP",
     * "upload_identitas": "data:image/jpeg;base64,..."
     * }
     * }
     *
     * @response 403 {
     * "message": "Hanya customer yang dapat mengupload identitas untuk diri sendiri."
     * }
     * @response 404 {
     * "message": "Data customer tidak ditemukan untuk pengguna ini."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "upload_identitas": [
     * "The upload identitas field is required."
     * ]
     * }
     * }
     */
    public function uploadIdentitas(Request $request)
    {
        $user = Auth::user();
        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat mengupload identitas untuk diri sendiri.'], 403);
        }

        $request->validate([
            'identitas' => 'required|in:KTP,SIM',
            'upload_identitas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $customer = $user->customer;

        if (!$customer) {
            return response()->json(['message' => 'Data customer tidak ditemukan untuk pengguna ini.'], 404);
        }

        $uploadedFile = $request->file('upload_identitas');
        $identitasBlob = file_get_contents($uploadedFile->getRealPath());

        $customer->identitas = $request->identitas;
        $customer->upload_identitas = $identitasBlob;
        $customer->save();

        $customer->upload_identitas = 'data:image/jpeg;base64,' . base64_encode($identitasBlob);

        return response()->json([
            'message' => 'Identitas berhasil diupload!',
            'customer' => $customer,
        ]);
    }

    /**
     *
     * @authenticated
     *
     * @response {
     * "data": [
     * {
     * "id": 1,
     * "user_id": 2,
     * "nama": "Customer Satu",
     * "alamat": "Jl. Customer 1",
     * "identitas": "KTP",
     * "upload_identitas": "data:image/jpeg;base64,..."
     * },
     * {
     * "id": 2,
     * "user_id": 3,
     * "nama": "Customer Dua",
     * "alamat": "Jl. Customer 2",
     * "identitas": null,
     * "upload_identitas": null
     * }
     * ]
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     */
    public function index()
    {
        // Middleware 'is_admin' sudah menangani otorisasi
        $customers = Customer::with('user.role')->get(); // Load user dan role-nya
        $customers->each(function ($customer) {
            if ($customer->upload_identitas) {
                $customer->upload_identitas = 'data:image/jpeg;base64,' . base64_encode($customer->upload_identitas);
            }
        });
        return response()->json(['data' => $customers]);
    }

    /**
     *
     * @authenticated
     * @urlParam id required ID dari customer. Contoh: 1
     *
     * @response {
     * "id": 1,
     * "user_id": 2,
     * "nama": "Customer Satu",
     * "alamat": "Jl. Customer 1",
     * "identitas": "KTP",
     * "upload_identitas": "data:image/jpeg;base64,...",
     * "user": {
     * "id": 2,
     * "nama": "Customer Satu",
     * "email": "customer1@example.com",
     * "status_akun": "Terverifikasi"
     * }
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     * @response 404 {
     * "message": "Customer tidak ditemukan."
     * }
     */
    public function show(string $id)
    {
        $this->middleware('is_admin');

        $customer = Customer::with('user')->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer tidak ditemukan.'], 404);
        }

        if ($customer->upload_identitas) {
            $customer->upload_identitas = 'data:image/jpeg;base64,' . base64_encode($customer->upload_identitas);
        }

        return response()->json($customer);
    }
}