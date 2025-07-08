<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;

/**
 * @group Lokasi Peta
 *
 */
class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth')->except(['index', 'show']);
        $this->middleware('is_admin')->only(['store', 'update', 'destroy']);
    }

    /**
     *
     * @response {
     * "data": [
     * {
     * "id": 1,
     * "nama_lokasi": "Kantor Pusat",
     * "latitude": "-7.7956",
     * "longitude": "110.3695"
     * }
     * ]
     * }
     */
    public function index()
    {
        $maps = Map::all();
        return response()->json(['data' => $maps]);
    }

    /**
     *
     * @response {
     * "id": 1,
     * "nama_lokasi": "Kantor Cabang",
     * "latitude": "-7.7889",
     * "longitude": "110.4261"
     * }
     *
     * @response 404 {
     * "message": "Lokasi tidak ditemukan."
     * }
     */
    public function show(string $id)
    {
        $map = Map::find($id);

        if (!$map) {
            return response()->json(['message' => 'Lokasi tidak ditemukan.'], 404);
        }

        return response()->json($map);
    }

    /**
     *
     * @authenticated
     * @bodyParam nama_lokasi required Nama lokasi. Contoh: Bandara Internasional Yogyakarta
     *
     * @response 201 {
     * "message": "Lokasi berhasil ditambahkan!",
     * "map": {
     * "id": 2,
     * "nama_lokasi": "Bandara Internasional Yogyakarta",
     * "latitude": "-7.90000000",
     * "longitude": "110.06000000"
     * }
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "nama_lokasi": [
     * "The nama lokasi field is required."
     * ]
     * }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $map = Map::create($request->all());

        return response()->json([
            'message' => 'Lokasi berhasil ditambahkan!',
            'map' => $map,
        ], 201);
    }

    /**
     *
     * @authenticated
     * @urlParam id required ID dari lokasi. Contoh: 1
     * @bodyParam nama_lokasi Nama lokasi. Contoh: Stasiun Tugu
     *
     * @response {
     * "message": "Lokasi berhasil diperbarui!",
     * "map": {
     * "id": 1,
     * "nama_lokasi": "Stasiun Tugu",
     * "latitude": "-7.78200000",
     * "longitude": "110.36300000"
     * }
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     * @response 404 {
     * "message": "Lokasi tidak ditemukan."
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     * "latitude": [
     * "The latitude field must be between -90 and 90."
     * ]
     * }
     * }
     */
    public function update(Request $request, string $id)
    {
        $map = Map::find($id);

        if (!$map) {
            return response()->json(['message' => 'Lokasi tidak ditemukan.'], 404);
        }

        $request->validate([
            'nama_lokasi' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
        ]);

        $map->update($request->all());

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui!',
            'map' => $map,
        ]);
    }

    /**
     *
     * @authenticated
     * @urlParam id required ID dari lokasi. Contoh: 1
     *
     * @response {
     * "message": "Lokasi berhasil dihapus!"
     * }
     *
     * @response 403 {
     * "message": "Anda tidak memiliki izin untuk melakukan tindakan ini."
     * }
     * @response 404 {
     * "message": "Lokasi tidak ditemukan."
     * }
     */
    public function destroy(string $id)
    {
        $map = Map::find($id);

        if (!$map) {
            return response()->json(['message' => 'Lokasi tidak ditemukan.'], 404);
        }

        $map->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus!'
        ]);
    }
}