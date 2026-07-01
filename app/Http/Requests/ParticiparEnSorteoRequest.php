<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParticiparEnSorteoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula'         => ['required', 'string', 'max:20'],
            'nombre'         => ['required', 'string', 'max:255'],
            'telefono'       => ['required', 'string', 'max:50'],
            'direccion'      => ['nullable', 'string'],
            'lugar_compra'   => ['required', 'string', 'max:255'],
            'factura_imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.required'         => 'La cédula es obligatoria.',
            'cedula.max'              => 'La cédula no puede superar los 20 caracteres.',
            'nombre.required'         => 'El nombre es obligatorio.',
            'nombre.max'              => 'El nombre no puede superar los 255 caracteres.',
            'telefono.required'       => 'El teléfono es obligatorio.',
            'telefono.max'            => 'El teléfono no puede superar los 50 caracteres.',
            'direccion.string'        => 'La dirección debe ser texto.',
            'lugar_compra.required'   => 'El lugar de compra es obligatorio.',
            'lugar_compra.max'        => 'El lugar de compra no puede superar los 255 caracteres.',
            'factura_imagen.required' => 'La imagen de la factura es obligatoria.',
            'factura_imagen.image'    => 'El archivo debe ser una imagen válida.',
            'factura_imagen.mimes'    => 'La imagen debe ser de tipo jpg, jpeg, png o webp.',
            'factura_imagen.max'      => 'La imagen no puede superar los 5 MB.',
        ];
    }
}
