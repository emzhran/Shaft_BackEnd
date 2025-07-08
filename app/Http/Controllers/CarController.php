<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use App\Http\Requests\CarRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api');
    }

    public function index()
    {
        $cars = Car::all();

        return response()->json([
            'message' => 'Daftar mobil berhasil dimuat.',
            'status_code' => 200,
            'data' => $cars
        ], 200);
    }

    public function show(string $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail mobil berhasil dimuat.',
            'status_code' => 200,
            'data' => $car
        ], 200);
    }

    public function store(CarRequest $request)
    {
        $validatedData = $request->validated();
        $gambarMobilUrl = null;

        if ($request->filled('gambar_mobil')) {
            $base64Image = $request->input('gambar_mobil');

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
                case 'image/svg+xml': $extension = 'svg'; break;
            }

            $fileName = Str::uuid() . '.' . $extension;
            $path = 'cars/' . $fileName;

            Storage::disk('public')->put($path, $decodedImage);

            $gambarMobilUrl = asset('storage/' . $path);
        }

        $car = Car::create([
            'merk_mobil' => $validatedData['merk_mobil'],
            'nama_mobil' => $validatedData['nama_mobil'],
            'harga_mobil' => $validatedData['harga_mobil'],
            'jumlah_mobil' => $validatedData['jumlah_mobil'],
            'jumlah_kursi' => $validatedData['jumlah_kursi'],
            'transmisi' => $validatedData['transmisi'],
            'gambar_mobil' => $gambarMobilUrl,
        ]);

        return response()->json([
            'message' => 'Mobil berhasil ditambahkan',
            'status_code' => 201,
            'car' => $car,
        ], 201);
    }

    public function update(Request $request, $id)
{
    $car = Car::findOrFail($id);

    $car->merk_mobil = $request->input('merk_mobil');
    $car->nama_mobil = $request->input('nama_mobil');
    $car->transmisi = $request->input('transmisi');
    $car->jumlah_kursi = $request->input('jumlah_kursi');
    $car->jumlah_mobil = $request->input('jumlah_mobil');
    $car->harga_mobil = $request->input('harga_mobil');


    if ($request->has('gambarMobil') && !empty($request->gambarMobil)) {
        try {
            $imageData = base64_decode($request->gambarMobil);

            if ($imageData === false) {
                return response()->json(['message' => 'Gambar tidak valid'], 400);
            }

            $fileName = Str::uuid() . '.jpg';

            Storage::disk('public')->put("cars/$fileName", $imageData);

            if ($car->gambar_mobil) {
                $oldFile = str_replace("/storage/", "", $car->gambar_mobil);
                Storage::disk('public')->delete($oldFile);
            }

            $car->gambar_mobil = asset("storage/cars/$fileName");


        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan gambar'], 500);
        }
    }

    $car->save();

    return response()->json([
        'message' => 'Mobil berhasil diperbarui',
        'data' => $car
        ]);
    }

    public function destroy(string $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Mobil tidak ditemukan.'], 404);
        }

        if ($car->gambar_mobil) {
            $oldPath = str_replace(asset('storage/'), '', $car->gambar_mobil);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $car->delete();

        return response()->json([
            'message' => 'Mobil berhasil dihapus!',
            'status_code' => 200,
        ]);
    }
}
