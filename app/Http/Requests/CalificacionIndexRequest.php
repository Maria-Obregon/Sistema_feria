<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalificacionIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'asignacion_juez_id' => ['required', 'integer', 'exists:asignacion_juez,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'asignacion_juez_id.required' => 'El ID de asignación es requerido',
            'asignacion_juez_id.integer' => 'El ID de asignación debe ser un número entero',
            'asignacion_juez_id.exists' => 'La asignación especificada no existe',
        ];
    }
}
