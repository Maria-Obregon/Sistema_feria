<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalificacionStoreRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.criterio_id' => ['required', 'integer', 'exists:criterios,id'],
            'items.*.puntaje' => ['required', 'numeric', 'min:0'],
            'items.*.comentario' => ['nullable', 'string', 'max:1000'],
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
            'items.required' => 'El array de items es requerido',
            'items.array' => 'Los items deben ser un array',
            'items.min' => 'Debe enviar al menos un item',
            'items.*.criterio_id.required' => 'El ID del criterio es requerido',
            'items.*.criterio_id.integer' => 'El ID del criterio debe ser un número entero',
            'items.*.criterio_id.exists' => 'El criterio especificado no existe',
            'items.*.puntaje.required' => 'El puntaje es requerido',
            'items.*.puntaje.numeric' => 'El puntaje debe ser un número',
            'items.*.puntaje.min' => 'El puntaje debe ser mayor o igual a 0',
            'items.*.comentario.string' => 'El comentario debe ser texto',
            'items.*.comentario.max' => 'El comentario no puede exceder 1000 caracteres',
        ];
    }
}
