<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\Package;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // pengunjung tanpa akun boleh memesan
    }

    public function rules(): array
    {
        return [
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['required', 'date', 'after_or_equal:start_date'],

            'items'          => ['nullable', 'array'],
            'items.*'        => ['nullable', 'integer', 'min:0', 'max:99'],
            'packages'       => ['nullable', 'array'],
            'packages.*'     => ['nullable', 'integer', 'min:0', 'max:10'],

            'customer_name'  => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    /** Validasi tambahan yang melibatkan beberapa field sekaligus. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items    = $this->selected('items');
            $packages = $this->selected('packages');

            if (! $items && ! $packages) {
                $validator->errors()->add('items', 'Pilih minimal satu alat atau paket.');
            }

            // ID yang dikirim harus benar-benar ada dan aktif (cegah manipulasi form).
            if ($items && Item::active()->whereIn('id', array_keys($items))->count() !== count($items)) {
                $validator->errors()->add('items', 'Terdapat alat yang tidak valid atau tidak tersedia.');
            }
            if ($packages && Package::active()->whereIn('id', array_keys($packages))->count() !== count($packages)) {
                $validator->errors()->add('packages', 'Terdapat paket yang tidak valid atau tidak tersedia.');
            }

            // Batas lama sewa.
            if (! $validator->errors()->hasAny(['start_date', 'end_date'])) {
                $days = Carbon::parse($this->input('start_date'))->diffInDays(Carbon::parse($this->input('end_date'))) + 1;
                if ($days > BookingService::MAX_DAYS) {
                    $validator->errors()->add('end_date', 'Maksimal lama sewa ' . BookingService::MAX_DAYS . ' hari.');
                }
            }
        });
    }

    /** Ambil pilihan dengan jumlah > 0 dari input (items / packages). */
    private function selected(string $key): array
    {
        $input = $this->input($key);

        return is_array($input)
            ? array_filter($input, fn ($qty) => (int) $qty > 0)
            : [];
    }

    public function messages(): array
    {
        return [
            'required'                   => ':attribute wajib diisi.',
            'email'                      => 'Format :attribute tidak valid.',
            'max'                        => ':attribute terlalu besar/panjang (maksimal :max).',
            'integer'                    => ':attribute harus berupa angka bulat.',
            'start_date.after_or_equal'  => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.after_or_equal'    => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'customer_phone.regex'       => 'Format nomor telepon tidak valid (contoh: 081234567890).',
        ];
    }

    public function attributes(): array
    {
        return [
            'start_date'     => 'Tanggal mulai',
            'end_date'       => 'Tanggal selesai',
            'customer_name'  => 'Nama',
            'customer_email' => 'Email',
            'customer_phone' => 'Nomor telepon',
            'notes'          => 'Catatan',
        ];
    }
}
