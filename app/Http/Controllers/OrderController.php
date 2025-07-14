<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CarOrderRequest;
use Illuminate\Validation\Rule;

/**
 * @group Pemesanan Mobil
 *
 */
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');

        $this->middleware('is_admin')->only(['index', 'updateOrderStatus']);
    }

    /**
     *
     * @authenticated
     *
     * @response 201 {
     * "message": "Pesanan berhasil dibuat!",
     * "order": {
     * "id": 1,
     * "user_id": 2,
     * "car_id": 1,
     * "tanggal_pemesanan": "2025-07-01",
     * "metode_pembayaran": "Transfer Bank",
     * "status_pemesanan": "Pending"
     * }
     * }
     *
     * @response 403 {
     * "message": "Hanya customer yang dapat membuat pesanan."
     * }
     * @response 404 {
     * "message": "Mobil tidak ditemukan."
     * }
     * @response 400 {
     * "message": "Stok mobil tidak mencukupi."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "tanggal_pemesanan": [
     * "The tanggal pemesanan field must be a valid date."
     * ]
     * }
     * }
     */
    public function store(CarOrderRequest $request) 
    {
        $user = Auth::user();
        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat membuat pesanan.'], 403);
        }

        $car = Car::find($request->car_id);

        if (!$car) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }

        if ($car->jumlah_mobil < 1) {
            return response()->json(['message' => 'Stok mobil tidak mencukupi.'], 400);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'car_id' => $request->car_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'metode_pembayaran' => $request->metode_pembayaran,
            'status_pemesanan' => 'Pending',
            'rating' => null
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat!',
            'order' => $order->load('user', 'car'),
        ], 201);
    }

    /**
     *
     * @authenticated
     *
     * @response {
     * "data": [
     * {
     * "id": 1,
     * "user": { "id": 2, "nama": "Customer A" },
     * "car": { "id": 1, "nama_mobil": "Avanza" },
     * "tanggal_pemesanan": "2025-07-01",
     * "metode_pembayaran": "Transfer Bank",
     * "status_pemesanan": "Pending"
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
        $orders = Order::with(['user.customer', 'car'])->get();

        $result = $orders->map(function ($order) {
            $user = $order->user;
            $customer = $user->customer;

            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'car_id' => $order->car_id,
                'tanggal_mulai' => $order->tanggal_mulai,
                'tanggal_selesai' => $order->tanggal_selesai,
                'metode_pembayaran' => $order->metode_pembayaran,
                'status_pemesanan' => $order->status_pemesanan,
                'rating' => $order->rating,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'status_akun' => $user->status_akun,
                    'alamat' => optional($customer)->alamat,
                    'identitas' => optional($customer)->identitas,
                    'nomor_identitas' => optional($customer)->nomor_identitas,
                ],
                'car' => $order->car,
            ];
        });

        return response()->json(['data' => $result]);
    }

    public function myOrders()
    {
        $user = Auth::user();
        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat melihat riwayat pesanan mereka.'], 403);
        }

        $orders = Order::where('user_id', $user->id)->with('car')->get();
        return response()->json(['data' => $orders]);
    }

    /**
     *
     * @authenticated
     *
     * @response 200 {
     * "message": "Detail pesanan berhasil ditemukan!",
     * "data": {
     * "id": 1,
     * "user_id": 2,
     * "car": { "id": 1, "nama_mobil": "Avanza" },
     * "tanggal_pemesanan": "2025-07-01",
     * "metode_pembayaran": "Transfer Bank",
     * "status_pemesanan": "Pending"
     * }
     * }
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melihat pesanan ini."
     * }
     * @response 404 {
     * "message": "Pesanan tidak ditemukan."
     * }
     */
    public function show(string $id)
    {
        $user = Auth::user();

        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat melihat detail pesanan.'], 403);
        }

        $order = Order::where('id', $id)
                      ->where('user_id', $user->id)
                      ->with('car', 'user')
                      ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan atau Anda tidak memiliki izin untuk melihatnya.'], 404);
        }

        return response()->json([
            'message' => 'Detail pesanan berhasil ditemukan!',
            'data' => $order
        ], 200);
    }

    /**
     *
     * @authenticated
     *
     * @response 200 {
     * "message": "Pesanan berhasil diperbarui!",
     * "order": {
     * "id": 1,
     * "tanggal_pemesanan": "2025-07-10",
     * "metode_pembayaran": "Kartu Kredit",
     * "status_pemesanan": "Pending"
     * }
     * }
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk memperbarui pesanan ini."
     * }
     * @response 404 {
     * "message": "Pesanan tidak ditemukan."
     * }
     * @response 400 {
     * "message": "Pesanan tidak dapat diubah karena statusnya sudah Dikonfirmasi/Selesai."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "tanggal_pemesanan": [
     * "The tanggal pemesanan field must be a valid date."
     * ]
     * }
     * }
     */
    public function update(CarOrderRequest $request, string $id) 
    {
        $user = Auth::user();

        if ($user->role->nama !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang dapat memperbarui pesanan.'], 403);
        }

        $order = Order::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan atau Anda tidak memiliki izin untuk memperbaruinya.'], 404);
        }

        if ($order->status_pemesanan === 'Pending') {
            if ($request->has('tanggal_mulai')) {
                $order->tanggal_mulai = $request->tanggal_mulai;
            }
            if ($request->has('tanggal_selesai')) {
                $order->tanggal_selesai = $request->tanggal_selesai;
            }
            if ($request->has('metode_pembayaran')) {
                $order->metode_pembayaran = $request->metode_pembayaran;
            }
        }

        if ($request->has('rating')) {
            if ($order->status_pemesanan === 'Selesai') {
                $validated = $request->validate([
                    'rating' => 'integer|min:1|max:5'
                ]);
                $order->rating = $request->rating;
            } else {
                return response()->json(['message' => 'Rating hanya dapat diberikan jika status pesanan adalah Selesai.'], 400);
            }
        }

        if ($request->has('status_pemesanan')) {
            $newStatus = $request->status_pemesanan;
            $oldStatus = $order->status_pemesanan;

            if ($newStatus === 'Dibatalkan') {
                if ($oldStatus === 'Pending' || $oldStatus === 'Dikonfirmasi') {
                    $order->status_pemesanan = $newStatus;
                    $car = Car::find($order->car_id);
                    if ($car) {
                        $car->jumlah_mobil++;
                        $car->save();
                    }
                } else {
                    return response()->json(['message' => 'Pesanan tidak dapat dibatalkan pada status saat ini (' . $oldStatus . ').'], 400);
                }
            } else {
                return response()->json(['message' => 'Customer hanya dapat mengubah status pesanan menjadi "Dibatalkan".'], 400);
            }
        }

        $order->save();

        return response()->json([
            'message' => 'Pesanan berhasil diperbarui!',
            'order' => $order->load('user', 'car'), 
        ], 200);
    }


    /**
     *
     * @authenticated
     *
     * @response {
     * "message": "Status pesanan berhasil diperbarui!",
     * "order": {
     * "id": 1,
     * "status_pemesanan": "Dikonfirmasi"
     * }
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     * @response 404 {
     * "message": "Pesanan tidak ditemukan."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "status_pemesanan": [
     * "The selected status pemesanan is invalid."
     * ]
     * }
     */
    public function updateOrderStatus(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $request->validate([
            'status_pemesanan' => 'required|in:Pending,Dikonfirmasi,Dibatalkan,Selesai',
        ]);

        $oldStatus = $order->status_pemesanan;
        $newStatus = $request->status_pemesanan;
        $car = Car::find($order->car_id);

        if (!$car) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }

        if ($oldStatus !== 'Dikonfirmasi' && $newStatus === 'Dikonfirmasi') {
            if ($car->jumlah_mobil > 0) {
                $car->jumlah_mobil -= 1;
                $car->save();
            } else {
                return response()->json(['message' => 'Stok mobil habis. Tidak bisa konfirmasi pesanan.'], 400);
            }
        }

        if ($oldStatus === 'Dikonfirmasi' && in_array($newStatus, ['Dibatalkan', 'Selesai'])) {
            $car->jumlah_mobil += 1;
            $car->save();
        }

        $order->status_pemesanan = $newStatus;
        $order->save();

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui!',
            'order' => $order,
        ]);
    }

}
