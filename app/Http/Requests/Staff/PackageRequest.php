<?php

namespace App\Http\Requests\Staff;

use App\Models\Item;
use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi oleh middleware role:admin,staff pada route
    }

    public function rules(): array
    {
        /** @var Package|null $package */
        $package = $this->route('package');

        return [
            'name'             => ['required', 'string', 'max:255', Rule::unique('packages', 'name')->ignore($package?->id)],
            'description'      => ['nullable', 'string', 'max:1000'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:50'],
            'is_active'        => ['nullable', 'boolean'],
            'items'            => ['nullable', 'array'],
            'items.*'          => ['nullable', 'integer', 'min:0', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $selected = $this->selectedItems();

            if (! $selected) {
                $validator->errors()->add('items', 'Pilih minimal satu alat untuk paket ini.');

                return;
            }

            // ID alat yang dikirim harus benar-benar ada (cegah manipulasi form).
            if (Item::whereIn('id', array_keys($selected))->count() !== count($selected)) {
                $validator->errors()->add('items', 'Terdapat alat yang tidak valid.');
            }
        });
    }

    /** @return array<int, int>  [item_id => jumlah] untuk jumlah > 0 */
    public function selectedItems(): array
    {
        $input = $this->input('items');

        if (! is_array($input)) {
            return [];
        }

        $selected = [];
        foreach ($input as $id => $qty) {
            if ((int) $qty > 0) {
                $selected[(int) $id] = (int) $qty;
            }
        }

        return $selected;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer'  => ':attribute harus berupa angka bulat.',
            'min'      => ':attribute minimal :min.',
            'max'      => ':attribute terlalu besar/panjang (maksimal :max).',
            'unique'   => ':attribute sudah dipakai paket lain.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'             => 'Nama paket',
            'description'      => 'Deskripsi',
            'discount_percent' => 'Diskon',
        ];
    }
}
