<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $deviceType = $request->query('device_type');

        $devices = Device::with(['room','spec'])
            ->when($q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('device_name', 'like', "%{$q}%")
                        ->orWhere('device_type', 'like', "%{$q}%")
                        ->orWhere('brand', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%")
                        ->orWhere('status', 'like', "%{$q}%")
                        ->orWhere('condition', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%");
                });
            })
            ->when($deviceType, function ($query, $deviceType) {
                $query->where('device_type', $deviceType);
            })
            ->orderBy('device_name')
            ->paginate(20)
            ->withQueryString();

        $deviceTypes = [
            'CPU',
            'Monitor',
            'PC AIO',
            'Laptop',
            'Router',
            'Hub',
            'Printer',
        ];

        return view('devices.device', [
            'devices' => $devices,
            'q' => $q,
            'deviceTypes' => $deviceTypes,
        ]);
    }

    public function create()
    {
        $rooms = Room::orderBy('room_id')->get(['room_id', 'name']);
        $deviceTypes = [
            'CPU',
            'Monitor',
            'PC AIO',
            'Laptop',
            'Router',
            'Hub',
            'Printer',
        ];
        return view('devices.adddevice', compact('rooms', 'deviceTypes'));
    }

    public function store(Request $request)
    {
        $deviceTypes = ['CPU', 'Monitor', 'PC AIO', 'Laptop', 'Router', 'Hub', 'Printer'];

        $data = $request->validate([
            'device_name' => ['required', 'string', 'max:255'],
            'room_id' => ['nullable', 'string', 'max:20', 'exists:rooms,room_id'],
            'device_type' => ['required', 'string', 'in:' . implode(',', $deviceTypes)],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'condition' => ['nullable', 'string', 'in:Good,Damage,Maintenance'],
            'status' => ['nullable', 'string', 'in:Active,Inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        Device::create($data);

        return redirect()->route('device.index')->with('success', 'Perangkat berhasil ditambahkan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('device.index')->with('success', 'Perangkat berhasil dihapus.');
    }
}
    
