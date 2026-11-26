<?php

namespace App\Http\Requests\Staff;

use App\Models\Item;
use App\Services\InventoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi oleh middleware role:admin,staff pada route
    }

    public function rules(): array
    {
        /** @var Item|null $item  Terisi saat edit (route model binding) */
        $item = $this->route('item');

        return [
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'name'             => ['required', 'string', 'max:255',
                                   Rule::unique('items', 'name')->whereNull('deleted_at')->ignore($item?->id)],
            'description'      => ['nullable', 'string', 'max:1000'],
            'total_stock'      => ['required', 'integer', 'min:0', 'max:9999'],
            'daily_rate'       => ['required', 'integer', 'min:0', 'max:10000000'],
            'replacement_cost' => ['required', 'integer', 'min:0', 'max:100000000'],
            'is_active'        => ['nullable', 'boolean'],
            'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image'     => ['nullable', 'boolean'],
        ];
    }

    /** Stok tidak boleh turun di bawah puncak pemesanan aktif/mendatang. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $item = $this->route('item');

            if (! $item instanceof Item || $validator->errors()->has('total_stock')) {
                return;
            }

            $peak = app(InventoryService::class)->peakReservation($item);

            if ((int) $this->input('total_stock') < $peak) {
                $validator->errors()->add(
                    'total_stock',
                    "Total stok tidak boleh kurang dari {$peak} unit karena sudah dipesan pada booking aktif/mendatang."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'required'          => ':attribute wajib diisi.',
            'integer'           => ':attribute harus berupa angka bulat.',
            'min'               => ':attribute minimal :min.',
            'max'               => ':attribute terlalu besar/panjang (maksimal :max).',
            'unique'            => ':attribute sudah dipakai alat lain.',
            'exists'            => ':attribute tidak valid.',
            'image.image'       => 'File harus berupa gambar.',
            'image.mimes'       => 'Foto harus berformat JPG, PNG, atau WebP.',
            'image.max'         => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id'      => 'Kategori',
            'name'             => 'Nama alat',
            'description'      => 'Deskripsi',
            'total_stock'      => 'Total stok',
            'daily_rate'       => 'Tarif harian',
            'replacement_cost' => 'Nilai penggantian',
            'image'            => 'Foto',
        ];
    }
}
