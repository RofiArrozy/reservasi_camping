<?php

namespace App\Http\Requests\Staff;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi oleh middleware role:admin,staff pada route
    }

    public function rules(): array
    {
        /** @var Article|null $article */
        $article = $this->route('article');

        return [
            'title'            => ['required', 'string', 'max:255',
                                   Rule::unique('articles', 'title')->whereNull('deleted_at')->ignore($article?->id)],
            'excerpt'          => ['nullable', 'string', 'max:500'],
            'content'          => ['required', 'string', 'min:50', 'max:20000'],
            'status'           => ['required', Rule::in([Article::STATUS_DRAFT, Article::STATUS_PUBLISHED])],
            'thumbnail'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'          => ':attribute wajib diisi.',
            'min'               => ':attribute minimal :min karakter.',
            'max'               => ':attribute terlalu panjang (maksimal :max).',
            'unique'            => ':attribute sudah dipakai artikel lain.',
            'status.in'         => 'Status tidak valid.',
            'thumbnail.image'   => 'File harus berupa gambar.',
            'thumbnail.mimes'   => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'thumbnail.max'     => 'Ukuran gambar maksimal 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'     => 'Judul',
            'excerpt'   => 'Ringkasan',
            'content'   => 'Isi artikel',
            'status'    => 'Status',
            'thumbnail' => 'Gambar',
        ];
    }
}
