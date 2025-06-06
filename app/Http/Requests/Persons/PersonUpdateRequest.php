<?php

namespace App\Http\Requests\Persons;

use App\Enums\Gender;
use App\Enums\PersonType;
use App\Rules\FileOrString;
use App\Models\Person;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class PersonUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $person = $this->route('person');

        return $this->user()->can('update', $person);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $person = $this->route('person');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', new Enum(PersonType::class)],
            'original_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', new Enum(Gender::class)],
            'image' => ['nullable', new FileOrString(['image/jpeg', 'image/png', 'image/webp'], 10240)],
            'description' => ['nullable', 'string'],
            'birthday' => ['nullable', 'date', 'before_or_equal:today'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('people', 'slug')->ignore($person)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_image' => ['nullable', new FileOrString(['image/jpeg', 'image/png', 'image/webp'], 10240)],
        ];
    }

    /**
     * Get the body parameters for the request.
     *
     * @return array
     */
    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'Ім\'\u044f персони.',
                'example' => 'Крістофер Нолан',
            ],
            'type' => [
                'description' => 'Тип персони (ACTOR - актор, DIRECTOR - режисер, тощо).',
                'example' => 'DIRECTOR',
            ],
            'original_name' => [
                'description' => 'Оригінальне ім\'\u044f персони (необов\'язково).',
                'example' => 'Christopher Nolan',
            ],
            'gender' => [
                'description' => 'Стать персони (необов\'язково).',
                'example' => 'MALE',
            ],
            'image' => [
                'description' => 'Зображення персони (файл або URL, необов\'язково).',
                'example' => 'https://example.com/images/nolan.jpg',
            ],
            'description' => [
                'description' => 'Опис персони (необов\'язково).',
                'example' => 'Британсько-американський кінорежисер, сценарист і продюсер...',
            ],
            'birthday' => [
                'description' => 'Дата народження персони (необов\'язково).',
                'example' => '1970-07-30',
            ],
            'birthplace' => [
                'description' => 'Місце народження персони (необов\'язково).',
                'example' => 'Лондон, Великобританія',
            ],
            'slug' => [
                'description' => 'Унікальний ідентифікатор персони для URL.',
                'example' => 'christopher-nolan',
            ],
            'meta_title' => [
                'description' => 'SEO заголовок.',
                'example' => 'Крістофер Нолан - фільми та біографія',
            ],
            'meta_description' => [
                'description' => 'SEO опис.',
                'example' => 'Дізнайтеся більше про Крістофера Нолана, його фільми та біографію.',
            ],
            'meta_image' => [
                'description' => 'SEO зображення (файл або URL).',
                'example' => 'https://example.com/images/nolan-meta.jpg',
            ],
        ];
    }

    /**
     * Get the URL parameters for the request.
     *
     * @return array
     */
    public function urlParameters()
    {
        return [
            'person' => [
                'description' => 'Slug персони, яку потрібно оновити.',
                'example' => 'christopher-nolan-abc123',
            ],
        ];
    }
}
